import Swal from 'sweetalert2';
window.Swal = Swal;

// Polyfill for SVG templates in Alpine.js
if (typeof SVGElement !== 'undefined' && !('content' in SVGElement.prototype)) {
    Object.defineProperty(SVGElement.prototype, 'content', {
        get() {
            if (this.tagName?.toLowerCase() === 'template') {
                if (!this._content) {
                    this._content = document.createDocumentFragment();
                    while (this.firstChild) {
                        this._content.appendChild(this.firstChild);
                    }
                }
                return this._content;
            }
            return undefined;
        },
        configurable: true,
    });
}

const MAX_IMAGE_DIMENSION = 1600;
const TARGET_IMAGE_BYTES = 1.5 * 1024 * 1024;
const MAX_SOURCE_IMAGE_BYTES = 40 * 1024 * 1024;
const MAX_SOURCE_IMAGE_PIXELS = 40_000_000;
const MAX_ATTACHMENT_BYTES = 25 * 1024 * 1024;
const IMAGE_FRAME_MIN_ZOOM = 0.3;
const IMAGE_FRAME_MAX_ZOOM = 2.5;

const finiteNumber = (value, fallback) => {
    const number = Number(value);
    return Number.isFinite(number) ? number : fallback;
};

const trackedUploadActivity = () => ({
    uploadActivityBusy: false,

    setUploadActivity(busy) {
        const next = Boolean(busy);
        if (this.uploadActivityBusy === next) return;

        this.uploadActivityBusy = next;
        const store = Alpine.store('imageUploads');
        store.active = Math.max(0, store.active + (next ? 1 : -1));
    },
});

let imageFrameEditorBodyLocks = 0;

const lockImageFrameEditorBody = () => {
    imageFrameEditorBodyLocks += 1;
    document.body.classList.add('image-frame-editor-open');
};

const unlockImageFrameEditorBody = () => {
    imageFrameEditorBodyLocks = Math.max(0, imageFrameEditorBodyLocks - 1);
    if (imageFrameEditorBodyLocks === 0) {
        document.body.classList.remove('image-frame-editor-open');
    }
};

const canvasBlob = (canvas, type, quality) => new Promise((resolve, reject) => {
    canvas.toBlob(
        (blob) => blob ? resolve(blob) : reject(new Error('No se pudo procesar la imagen.')),
        type,
        quality,
    );
});

const loadImageSource = async (file) => {
    if ('createImageBitmap' in window) {
        let image;
        try {
            image = await createImageBitmap(file, { imageOrientation: 'from-image' });
        } catch {
            image = await createImageBitmap(file);
        }

        return { image, release: () => image.close() };
    }

    const url = URL.createObjectURL(file);
    const image = new Image();

    await new Promise((resolve, reject) => {
        image.onload = resolve;
        image.onerror = () => reject(new Error('No se pudo leer la imagen.'));
        image.src = url;
    });

    return { image, release: () => URL.revokeObjectURL(url) };
};

const optimizeImage = async (file, maxDimension = MAX_IMAGE_DIMENSION, targetBytes = TARGET_IMAGE_BYTES) => {
    const supportedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!supportedTypes.includes(file.type)) {
        throw new Error('Usa una imagen JPG, PNG o WebP.');
    }
    if (file.size > MAX_SOURCE_IMAGE_BYTES) {
        throw new Error('La imagen original no puede superar 40 MB.');
    }

    const { image, release } = await loadImageSource(file);
    const sourceWidth = image.width || image.naturalWidth;
    const sourceHeight = image.height || image.naturalHeight;
    if (!sourceWidth || !sourceHeight || sourceWidth * sourceHeight > MAX_SOURCE_IMAGE_PIXELS) {
        release();
        throw new Error('Imagen demasiado grande. Máximo 40 megapíxeles.');
    }

    const scale = Math.min(1, maxDimension / Math.max(sourceWidth, sourceHeight));
    let width = Math.max(1, Math.round(sourceWidth * scale));
    let height = Math.max(1, Math.round(sourceHeight * scale));
    let blob;

    try {
        for (let attempt = 0; attempt < 8; attempt += 1) {
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d', { alpha: true });
            if (!context) throw new Error('Navegador sin soporte para procesar imágenes.');
            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';
            context.drawImage(image, 0, 0, width, height);

            blob = await canvasBlob(canvas, 'image/webp', Math.max(0.64, 0.88 - attempt * 0.04));
            canvas.width = 1;
            canvas.height = 1;

            if (blob.size <= targetBytes) break;

            const reduction = Math.max(0.65, Math.min(0.88, Math.sqrt(targetBytes / blob.size) * 0.96));
            width = Math.max(1, Math.round(width * reduction));
            height = Math.max(1, Math.round(height * reduction));
        }
    } finally {
        release();
    }

    if (!blob || blob.size > targetBytes) {
        throw new Error('Imagen demasiado compleja. Prueba con otra fotografía.');
    }

    const name = file.name.replace(/\.[^.]+$/, '') || 'foto';
    const extension = blob.type === 'image/webp' ? 'webp' : (blob.type === 'image/png' ? 'png' : 'jpg');

    return new File([blob], `${name}.${extension}`, {
        type: blob.type,
        lastModified: Date.now(),
    });
};

const stopMediaStream = (stream) => {
    stream?.getTracks().forEach((track) => track.stop());
};

const cameraControls = (captureHandler) => ({
    cameraOpen: false,
    cameraStarting: false,
    cameraReady: false,
    cameraError: '',
    cameraStream: null,
    cameraReturnFocus: null,

    async openCamera(event = null) {
        if (this.busy) return;

        if (!navigator.mediaDevices?.getUserMedia) {
            this.$refs.captureInput?.click();
            return;
        }

        this.cameraReturnFocus = event?.currentTarget instanceof HTMLElement
            ? event.currentTarget
            : (document.activeElement instanceof HTMLElement ? document.activeElement : null);
        this.cameraOpen = true;
        this.cameraStarting = true;
        this.cameraReady = false;
        this.cameraError = '';
        document.body.classList.add('overflow-hidden');

        try {
            await this.$nextTick();
            this.$refs.cameraDialog?.focus({ preventScroll: true });
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });

            if (!this.cameraOpen) {
                stopMediaStream(stream);
                return;
            }

            this.cameraStream = stream;
            const video = this.$refs.cameraVideo;
            video.srcObject = stream;
            await video.play();
            this.cameraReady = true;
        } catch (error) {
            stopMediaStream(this.cameraStream);
            this.cameraStream = null;
            this.cameraError = error?.name === 'NotAllowedError'
                ? 'Permiso de cámara denegado. Habilítalo o usa “Elegir imagen”.'
                : 'No se pudo iniciar cámara. Usa “Elegir imagen”.';
        } finally {
            this.cameraStarting = false;
        }
    },

    stopCamera() {
        stopMediaStream(this.cameraStream);
        this.cameraStream = null;
        this.cameraReady = false;
        if (this.$refs.cameraVideo) this.$refs.cameraVideo.srcObject = null;
    },

    closeCamera() {
        const returnFocus = this.cameraReturnFocus;
        this.cameraOpen = false;
        this.stopCamera();
        document.body.classList.remove('overflow-hidden');
        this.cameraReturnFocus = null;

        queueMicrotask(() => requestAnimationFrame(() => {
            if (returnFocus instanceof HTMLElement && returnFocus.isConnected) {
                returnFocus.focus({ preventScroll: true });
            }
        }));
    },

    trapCameraFocus(event) {
        if (!this.cameraOpen) return;

        const dialog = this.$refs.cameraDialog;
        const elements = [...(dialog?.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), [tabindex]:not([tabindex="-1"])') || [])]
            .filter((element) => element.getClientRects().length > 0);
        if (elements.length === 0) {
            event.preventDefault();
            dialog?.focus();
            return;
        }

        const first = elements[0];
        const last = elements[elements.length - 1];
        if (!elements.includes(document.activeElement)) {
            event.preventDefault();
            (event.shiftKey ? last : first).focus();
        } else if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    },

    async captureCameraPhoto() {
        const video = this.$refs.cameraVideo;
        if (!this.cameraReady || !video?.videoWidth || !video?.videoHeight) return;

        try {
            const scale = Math.min(1, 1920 / Math.max(video.videoWidth, video.videoHeight));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(video.videoWidth * scale));
            canvas.height = Math.max(1, Math.round(video.videoHeight * scale));
            const context = canvas.getContext('2d');
            if (!context) throw new Error('Canvas no disponible.');

            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const blob = await canvasBlob(canvas, 'image/jpeg', 0.92);
            canvas.width = 1;
            canvas.height = 1;
            const file = new File([blob], `foto-${Date.now()}.jpg`, { type: 'image/jpeg', lastModified: Date.now() });

            this.closeCamera();
            await this[captureHandler](file);
        } catch {
            this.cameraError = 'No se pudo capturar foto. Usa cámara del dispositivo.';
            this.stopCamera();
        }
    },
});

const alertTypes = ['success', 'error', 'warning', 'info', 'question'];

const eventData = (event) => Array.isArray(event) ? event[0] : event;
const alertType = (icon) => alertTypes.includes(icon) ? icon : 'info';

const applyBranding = (event) => {
    const data = eventData(event?.detail ?? event) || {};

    Object.entries(data.palette || {}).forEach(([shade, rgb]) => {
        document.documentElement.style.setProperty(`--brand-${shade}`, rgb);
    });
    document.querySelectorAll('[data-brand-name]').forEach((element) => {
        element.textContent = data.name || '';
    });
    document.querySelectorAll('[data-brand-tagline]').forEach((element) => {
        element.textContent = data.tagline || '';
    });
    document.querySelectorAll('[data-brand-logo-image]').forEach((element) => {
        const frame = data.logoFrame || { x: 50, y: 50, zoom: 1 };
        element.src = data.logoUrl || '';
        element.alt = `Logo de ${data.name || ''}`;
        const focusX = finiteNumber(frame.x, 50);
        const focusY = finiteNumber(frame.y, 50);
        element.style.objectPosition = `${focusX}% ${focusY}%`;
        element.style.transform = `scale(${finiteNumber(frame.zoom, 1)})`;
        element.style.transformOrigin = `${focusX}% ${focusY}%`;
        element.classList.toggle('hidden', !data.logoUrl);
    });
    document.querySelectorAll('[data-brand-logo-fallback]').forEach((element) => {
        element.classList.toggle('hidden', Boolean(data.logoUrl));
        element.setAttribute('aria-label', `Logo de ${data.name || ''}`);
    });
    if (data.name) document.title = data.name;
};
const alertClasses = (icon, toast = false) => ({
    popup: `agro-alert agro-alert--${alertType(icon)}${toast ? ' agro-toast' : ''}`,
    title: 'agro-alert__title',
    htmlContainer: 'agro-alert__text',
    confirmButton: 'agro-alert__confirm',
    cancelButton: 'agro-alert__cancel',
    actions: 'agro-alert__actions',
    closeButton: 'agro-alert__close',
    timerProgressBar: 'agro-alert__progress',
});

const showToast = (data) => {
    const icon = alertType(data.icon || 'success');

    return Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title: data.title || '',
        text: data.text || '',
        showConfirmButton: false,
        showCloseButton: true,
        timer: data.timer || 4000,
        timerProgressBar: true,
        customClass: alertClasses(icon, true),
    });
};

const showModal = (data, confirmation = false) => {
    const icon = alertType(data.icon || (confirmation ? 'warning' : 'info'));

    return Swal.fire({
        icon,
        title: data.title || (confirmation ? 'Confirmar acción' : 'Notificación'),
        text: data.text || '',
        confirmButtonText: data.confirmButtonText || (confirmation ? 'Confirmar' : 'Entendido'),
        cancelButtonText: data.cancelButtonText || 'Cancelar',
        showCancelButton: confirmation,
        showCloseButton: !confirmation,
        buttonsStyling: false,
        allowOutsideClick: !confirmation,
        focusCancel: confirmation,
        customClass: alertClasses(icon),
    });
};

window.confirmDelete = (title, text = '¡Esta acción no se podrá revertir!') => {
    return Swal.fire({
        icon: 'warning',
        title: title || '¿Está seguro de eliminar?',
        text: text,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        focusCancel: true,
        customClass: alertClasses('warning'),
    });
};

const showFlashAlert = () => {
    const element = document.getElementById('swal-flash');
    if (!element) return;

    const data = JSON.parse(element.textContent || '{}');
    element.remove();
    showToast(data);
};

let _idleTimeoutMs = 0;
let _idleTimer = null;
let _idleListenersAttached = false;

const _resetIdleTimer = () => {
    if (_idleTimer) {
        clearTimeout(_idleTimer);
        _idleTimer = null;
    }
    if (!_idleTimeoutMs || _idleTimeoutMs <= 0 || _idleTimeoutMs > 2147483647) return;

    _idleTimer = setTimeout(() => {
        const form = document.getElementById('idle-logout-form');
        if (!form) return;

        // Protección de flujo activo: No cerrar sesión automáticamente si hay modales o visores abiertos
        const hasActiveModal = document.querySelector('[role="dialog"], [aria-modal="true"], .agro-dialog-overlay, .agro-dialog-overlay--pdf, iframe');
        if (hasActiveModal) {
            _resetIdleTimer();
            return;
        }
        form.submit();
    }, _idleTimeoutMs);
};

const initializeIdleLogout = () => {
    const form = document.getElementById('idle-logout-form');
    if (!form) {
        if (_idleTimer) {
            clearTimeout(_idleTimer);
            _idleTimer = null;
        }
        _idleTimeoutMs = 0;
        return;
    }

    _idleTimeoutMs = Number(form.dataset.timeout) || 0;
    _resetIdleTimer();

    if (!_idleListenersAttached) {
        _idleListenersAttached = true;
        ['mousemove', 'mousedown', 'pointermove', 'click', 'keydown', 'touchstart', 'scroll', 'wheel'].forEach((event) => {
            window.addEventListener(event, _resetIdleTimer, { passive: true });
        });

        window.addEventListener('focus', _resetIdleTimer, { passive: true });
        window.addEventListener('blur', _resetIdleTimer, { passive: true });
        window.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') _resetIdleTimer();
        }, { passive: true });
    }
};

const registerAlpineComponents = () => {
    Alpine.store('imageUploads', {
        active: 0,

        get busy() {
            return this.active > 0;
        },
    });

    Alpine.data('directUploadProgress', () => ({
        ...trackedUploadActivity(),
        busy: false,
        progress: 0,
        error: '',
        previewUrls: [],
        uploadListeners: [],

        captureFiles(event) {
            if (this.busy) {
                event.target.value = '';
                return;
            }

            this.releasePreviews();
            const files = Array.from(event.target?.files || []);
            this.previewUrls = files
                .filter((file) => file.type.startsWith('image/'))
                .map((file) => URL.createObjectURL(file));
            this.busy = files.length > 0;
            this.progress = 0;
            this.error = '';
            this.setUploadActivity(this.busy);
        },

        releasePreviews() {
            this.previewUrls.forEach((url) => URL.revokeObjectURL(url));
            this.previewUrls = [];
        },

        init() {
            const listen = (name, handler) => {
                this.$el.addEventListener(name, handler);
                this.uploadListeners.push([name, handler]);
            };

            listen('livewire-upload-start', () => {
                this.busy = true;
                this.progress = 0;
                this.error = '';
            });
            listen('livewire-upload-progress', (event) => {
                this.progress = Math.min(100, Math.max(0, Number(event.detail?.progress) || 0));
            });
            listen('livewire-upload-finish', () => {
                this.busy = false;
                this.progress = 100;
                this.error = '';
                this.releasePreviews();
                this.setUploadActivity(false);
            });
            listen('livewire-upload-error', (event) => {
                this.busy = false;
                this.progress = 0;
                this.error = event.detail?.message || 'No se pudo subir el archivo. Intenta nuevamente.';
                this.releasePreviews();
                this.setUploadActivity(false);
            });
            listen('livewire-upload-cancel', () => {
                this.busy = false;
                this.progress = 0;
                this.error = 'La carga fue cancelada.';
                this.releasePreviews();
                this.setUploadActivity(false);
            });
        },

        destroy() {
            this.uploadListeners.forEach(([name, handler]) => this.$el.removeEventListener(name, handler));
            this.uploadListeners = [];
            this.releasePreviews();
            this.setUploadActivity(false);
        },
    }));

    Alpine.data('imageFrameEditor', (config = {}) => ({
        initiallyOpen: Boolean(config.initiallyOpen),
        visible: Boolean(config.initiallyOpen),
        focusX: config.focusX ?? 50,
        focusY: config.focusY ?? 50,
        zoom: config.zoom ?? IMAGE_FRAME_MIN_ZOOM,
        minZoom: config.minZoom ?? IMAGE_FRAME_MIN_ZOOM,
        maxZoom: config.maxZoom ?? IMAGE_FRAME_MAX_ZOOM,
        simple: Boolean(config.simple),
        screen: config.screen || 'desktop',
        frames: {},
        previewMode: config.previewMode || 'square',
        snapshot: null,
        returnFocus: null,
        dragging: false,
        pointerId: null,
        dragTarget: null,
        startClientX: 0,
        startClientY: 0,
        startFocusX: 50,
        startFocusY: 50,
        frameWidth: 1,
        frameHeight: 1,
        bodyLocked: false,
        saveAction: config.saveAction || null,
        closeAction: config.closeAction || null,

        init() {
            this.$watch('visible', (visible) => this.syncVisibility(visible));

            if (this.visible) {
                if (this.simple && Number(this.zoom) < this.minZoom) this.zoom = this.minZoom;
                this.returnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : null;
                this.captureSnapshot();
                this.syncVisibility(true);
            }
        },

        destroy() {
            const returnFocus = this.visible ? this.returnFocus : null;
            this.endDrag();
            this.unlockBody();
            if (returnFocus) this.restoreFocus(returnFocus);
        },

        number(value, fallback) {
            const number = Number(value);
            return Number.isFinite(number) ? number : fallback;
        },

        clamp(value, min, max) {
            return Math.min(max, Math.max(min, this.number(value, min)));
        },

        captureSnapshot() {
            this.snapshot = {
                focusX: this.clamp(this.number(this.focusX, 50), 0, 100),
                focusY: this.clamp(this.number(this.focusY, 50), 0, 100),
                zoom: this.clamp(this.number(this.zoom, this.minZoom), this.minZoom, this.maxZoom),
            };
        },

        restoreSnapshot() {
            if (!this.snapshot) return;

            this.focusX = this.snapshot.focusX;
            this.focusY = this.snapshot.focusY;
            this.zoom = this.snapshot.zoom;
        },

        syncVisibility(visible) {
            if (visible) {
                this.lockBody();
                this.$nextTick(() => this.focusDialog());
                return;
            }

            this.endDrag();
            this.unlockBody();
        },

        lockBody() {
            if (this.bodyLocked) return;
            this.bodyLocked = true;
            lockImageFrameEditorBody();
        },

        unlockBody() {
            if (!this.bodyLocked) return;
            this.bodyLocked = false;
            unlockImageFrameEditorBody();
        },

        open(event) {
            if (this.visible) return;

            if (this.simple && Number(this.zoom) < this.minZoom) this.zoom = this.minZoom;
            this.returnFocus = event?.currentTarget instanceof HTMLElement
                ? event.currentTarget
                : (document.activeElement instanceof HTMLElement ? document.activeElement : null);
            this.captureSnapshot();
            this.visible = true;
        },

        cancel() {
            if (!this.visible) return;

            const returnFocus = this.returnFocus;
            this.restoreSnapshot();
            this.visible = false;
            this.invokeAction(this.closeAction);
            this.restoreFocus(returnFocus);
        },

        apply() {
            if (!this.visible) return null;

            const returnFocus = this.returnFocus;
            if (this.saveAction) {
                const request = this.invokeAction(this.saveAction);
                if (!this.initiallyOpen) {
                    this.visible = false;
                    this.restoreFocus(returnFocus);
                }
                return request;
            }

            this.syncHostPreview();
            this.visible = false;
            this.restoreFocus(returnFocus);
            return null;
        },

        invokeAction(action) {
            if (!action || !/^[A-Za-z_$][\w$]*$/.test(action)) return null;

            const method = this.$wire?.[action];
            return typeof method === 'function' ? method() : null;
        },

        syncHostPreview() {
            const host = this.$el.parentElement;
            if (!host) return;

            const focusX = this.clamp(this.number(this.focusX, 50), 0, 100);
            const focusY = this.clamp(this.number(this.focusY, 50), 0, 100);
            const zoom = this.clamp(this.number(this.zoom, IMAGE_FRAME_MIN_ZOOM), IMAGE_FRAME_MIN_ZOOM, IMAGE_FRAME_MAX_ZOOM);
            host.querySelectorAll('img').forEach((image) => {
                if (image.closest('.image-frame-editor')) return;

                image.style.objectPosition = `${focusX}% ${focusY}%`;
                image.style.transform = `scale(${zoom})`;
                image.style.transformOrigin = `${focusX}% ${focusY}%`;
            });
            this.$dispatch('image-frame-applied', { focusX, focusY, zoom });
        },

        focusDialog() {
            const dialog = this.$refs.dialog;
            if (!dialog) return;

            try {
                dialog.focus({ preventScroll: true });
            } catch {
                dialog.focus();
            }
        },

        restoreFocus(element) {
            queueMicrotask(() => requestAnimationFrame(() => {
                if (!(element instanceof HTMLElement) || !element.isConnected) return;

                try {
                    element.focus({ preventScroll: true });
                } catch {
                    element.focus();
                }
            }));
        },

        focusableElements() {
            const selector = 'a[href], button, input:not([type="hidden"]), textarea, select, details, [tabindex]:not([tabindex="-1"])';
            return [...(this.$refs.dialog?.querySelectorAll(selector) || [])]
                .filter((element) => !element.disabled && element.getClientRects().length > 0);
        },

        trapFocus(event) {
            if (!this.visible) return;

            const elements = this.focusableElements();
            if (elements.length === 0) {
                event.preventDefault();
                this.focusDialog();
                return;
            }

            const first = elements[0];
            const last = elements[elements.length - 1];
            if (!elements.includes(document.activeElement)) {
                event.preventDefault();
                (event.shiftKey ? last : first).focus();
            } else if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        },

        reset() {
            this.focusX = 50;
            this.focusY = 50;
            this.zoom = this.minZoom;
        },

        switchScreen(next) {
            if (next === this.screen) return;

            // Guardar la posición actual de la pantalla saliente.
            this.frames[this.screen] = {
                x: this.number(this.focusX, 50),
                y: this.number(this.focusY, 50),
                zoom: this.number(this.zoom, this.minZoom),
            };
            this.screen = next;

            // Restaurar la posición independiente de la pantalla entrante.
            const saved = this.frames[next];
            if (saved) {
                this.focusX = this.clamp(saved.x, 0, 100);
                this.focusY = this.clamp(saved.y, 0, 100);
                this.zoom = this.clamp(saved.zoom, this.minZoom, this.maxZoom);
            } else {
                this.focusX = 50;
                this.focusY = 50;
                this.zoom = this.minZoom;
            }
        },

        onWheel(event) {
            const delta = event.deltaY < 0 ? 0.1 : -0.1;
            this.zoom = this.clamp(Number(this.zoom) + delta, this.minZoom, this.maxZoom);
        },

        onKeydown(event) {
            if (!event.ctrlKey && !event.metaKey) return;
            if (event.key === '=' || event.key === '+') {
                event.preventDefault();
                this.zoom = this.clamp(Number(this.zoom) + 0.1, this.minZoom, this.maxZoom);
            } else if (event.key === '-') {
                event.preventDefault();
                this.zoom = this.clamp(Number(this.zoom) - 0.1, this.minZoom, this.maxZoom);
            } else if (event.key === '0') {
                event.preventDefault();
                this.reset();
            }
        },

        startDrag(event) {
            if (event.button !== 0 || event.isPrimary === false) return;

            const frame = this.$refs.frame;
            if (!frame) return;

            const rect = frame.getBoundingClientRect();
            event.preventDefault();
            this.dragging = true;
            this.pointerId = event.pointerId;
            this.dragTarget = event.currentTarget;
            this.startClientX = event.clientX;
            this.startClientY = event.clientY;
            this.startFocusX = this.clamp(this.number(this.focusX, 50), 0, 100);
            this.startFocusY = this.clamp(this.number(this.focusY, 50), 0, 100);
            this.frameWidth = Math.max(1, rect.width);
            this.frameHeight = Math.max(1, rect.height);

            try {
                this.dragTarget.setPointerCapture(this.pointerId);
            } catch {
                this.endDrag();
            }
        },

        drag(event) {
            if (!this.dragging || event.pointerId !== this.pointerId) return;

            const sensitivity = 100 / this.clamp(this.zoom, this.minZoom, this.maxZoom);
            const nextX = this.startFocusX - ((event.clientX - this.startClientX) / this.frameWidth) * sensitivity;
            const nextY = this.startFocusY - ((event.clientY - this.startClientY) / this.frameHeight) * sensitivity;
            this.focusX = Math.round(this.clamp(nextX, 0, 100) * 10) / 10;
            this.focusY = Math.round(this.clamp(nextY, 0, 100) * 10) / 10;
        },

        endDrag(event = null) {
            if (!this.dragging || (event && event.pointerId !== this.pointerId)) return;

            const target = this.dragTarget;
            const pointerId = this.pointerId;
            this.dragging = false;
            this.pointerId = null;
            this.dragTarget = null;

            if (!target || pointerId === null) return;

            try {
                if (target.hasPointerCapture(pointerId)) target.releasePointerCapture(pointerId);
            } catch {
                // The browser can release capture before pointercancel reaches Alpine.
            }
        },
    }));

    Alpine.data('optimizedImageUpload', (
        property = 'foto',
        maxDimension = MAX_IMAGE_DIMENSION,
        targetBytes = TARGET_IMAGE_BYTES,
    ) => ({
        ...trackedUploadActivity(),
        ...cameraControls('processPhoto'),
        property,
        maxDimension,
        targetBytes,
        processing: false,
        uploading: false,
        progress: 0,
        clientError: '',
        previewUrl: null,

        get busy() {
            return this.processing || this.uploading;
        },

        releasePreview() {
            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        },

        async selectPhoto(event) {
            const file = event.target.files[0];
            event.target.value = '';
            await this.processPhoto(file);
        },

        async processPhoto(file) {
            if (!file || this.busy) return;

            this.clientError = '';
            this.progress = 0;
            this.releasePreview();
            this.previewUrl = URL.createObjectURL(file);
            this.processing = true;
            this.setUploadActivity(true);
            this.$dispatch('profile-image-upload-state', { busy: true });

            try {
                const optimized = await optimizeImage(file, this.maxDimension, this.targetBytes);
                this.processing = false;
                this.uploading = true;

                this.$wire.upload(
                    this.property,
                    optimized,
                    () => {
                        this.uploading = false;
                        this.progress = 100;
                        this.releasePreview();
                        this.setUploadActivity(false);
                        this.$dispatch('profile-image-upload-state', { busy: false });
                    },
                    () => {
                        this.uploading = false;
                        this.releasePreview();
                        this.clientError = 'No se pudo subir la imagen. Intenta nuevamente.';
                        this.setUploadActivity(false);
                        this.$dispatch('profile-image-upload-state', { busy: false });
                    },
                    (uploadEvent) => {
                        this.progress = uploadEvent.detail.progress;
                    },
                );
            } catch (error) {
                this.processing = false;
                this.releasePreview();
                this.clientError = error.message || 'No se pudo procesar la imagen.';
                this.setUploadActivity(false);
                this.$dispatch('profile-image-upload-state', { busy: false });
            }
        },

        destroy() {
            this.closeCamera();
            this.releasePreview();
            this.setUploadActivity(false);
        },
    }));

    Alpine.data('optimizedMultiImageUpload', (property = 'fotos', max = 3, initialCount = 0) => ({
        ...trackedUploadActivity(),
        ...cameraControls('addCapturedPhoto'),
        property,
        max,
        count: initialCount,
        processing: false,
        uploading: false,
        progress: 0,
        clientError: '',
        previewUrls: [],
        profileImageBusy: false,

        get busy() {
            return this.processing || this.uploading;
        },

        releasePreviews() {
            this.previewUrls.forEach((url) => URL.revokeObjectURL(url));
            this.previewUrls = [];
        },

        removeOne() {
            this.count = Math.max(0, this.count - 1);
            this.clientError = '';
        },

        async selectPhotos(event) {
            const files = Array.from(event.target.files || []);
            event.target.value = '';
            await this.processPhotos(files);
        },

        async addCapturedPhoto(file) {
            await this.processPhotos([file]);
        },

        async processPhotos(files) {
            if (files.length === 0 || this.busy) return;

            const available = this.max - this.count;
            if (available <= 0 || files.length > available) {
                this.clientError = `Puedes agregar ${Math.max(available, 0)} imagen(es) más. Máximo ${this.max}.`;
                return;
            }

            this.clientError = '';
            this.progress = 0;
            this.processing = true;
            this.setUploadActivity(true);
            this.releasePreviews();

            try {
                const optimizedFiles = [];
                for (const file of files) {
                    const optimized = await optimizeImage(file, 1280, 900 * 1024);
                    optimizedFiles.push(optimized);
                    this.previewUrls.push(URL.createObjectURL(optimized));
                }

                this.processing = false;
                this.uploading = true;
                this.$wire.uploadMultiple(
                    this.property,
                    optimizedFiles,
                    () => {
                        this.uploading = false;
                        this.progress = 100;
                        this.count = Math.min(this.max, this.count + optimizedFiles.length);
                        this.releasePreviews();
                        this.setUploadActivity(false);
                    },
                    () => {
                        this.uploading = false;
                        this.clientError = 'No se pudieron subir las imágenes. Intenta nuevamente.';
                        this.releasePreviews();
                        this.setUploadActivity(false);
                    },
                    (uploadEvent) => {
                        this.progress = uploadEvent.detail.progress;
                    },
                    () => {
                        this.uploading = false;
                        this.releasePreviews();
                        this.setUploadActivity(false);
                    },
                    true,
                );
            } catch (error) {
                this.processing = false;
                this.releasePreviews();
                this.clientError = error.message || 'No se pudieron procesar las imágenes.';
                this.setUploadActivity(false);
            }
        },

        destroy() {
            this.closeCamera();
            this.releasePreviews();
            this.setUploadActivity(false);
        },
    }));

    Alpine.data('optimizedAttachmentUpload', (property = 'comprobante') => ({
        ...trackedUploadActivity(),
        ...cameraControls('processAttachment'),
        property,
        processing: false,
        uploading: false,
        progress: 0,
        clientError: '',
        previewUrl: null,

        get busy() {
            return this.processing || this.uploading;
        },

        releasePreview() {
            if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
            this.previewUrl = null;
        },

        async selectAttachment(event) {
            const file = event.target.files[0];
            event.target.value = '';
            await this.processAttachment(file);
        },

        async processAttachment(file) {
            if (!file || this.busy) return;

            this.clientError = '';
            this.progress = 0;
            this.releasePreview();
            if (file.type.startsWith('image/')) {
                this.previewUrl = URL.createObjectURL(file);
            }
            this.processing = true;
            this.setUploadActivity(true);

            try {
                let upload = file;
                if (file.type.startsWith('image/')) {
                    upload = await optimizeImage(file, 1400, 900 * 1024);
                } else if (file.type !== 'application/pdf') {
                    throw new Error('Usa PDF o imagen JPG, PNG o WebP.');
                } else if (file.size > MAX_ATTACHMENT_BYTES) {
                    throw new Error('PDF no puede superar 25 MB.');
                }

                this.processing = false;
                this.uploading = true;
                this.$wire.upload(
                    this.property,
                    upload,
                    () => {
                        this.uploading = false;
                        this.progress = 100;
                        this.releasePreview();
                        this.setUploadActivity(false);
                    },
                    () => {
                        this.uploading = false;
                        this.clientError = 'No se pudo subir archivo. Intenta nuevamente.';
                        this.releasePreview();
                        this.setUploadActivity(false);
                    },
                    (uploadEvent) => {
                        this.progress = uploadEvent.detail.progress;
                    },
                );
            } catch (error) {
                this.processing = false;
                this.releasePreview();
                this.clientError = error.message || 'No se pudo procesar archivo.';
                this.setUploadActivity(false);
            }
        },

        destroy() {
            this.closeCamera();
            this.releasePreview();
            this.setUploadActivity(false);
        },
    }));

    Alpine.data('financeDashboard', (payload = {}) => ({
        range: 12,
        selectedPeriod: '',
        activePoint: null,
        categoryType: 'egreso',
        monthly: Array.isArray(payload.monthly) ? payload.monthly : [],

        get visibleMonths() {
            return this.monthly.slice(-Math.min(this.range, this.monthly.length));
        },

        get selectedMonth() {
            return this.visibleMonths.find((month) => month.period === this.selectedPeriod) || null;
        },

        get analysisMonths() {
            return this.selectedMonth ? [this.selectedMonth] : this.visibleMonths;
        },

        get totalIncome() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.income || 0), 0);
        },

        get totalExpenses() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.expenses || 0), 0);
        },

        get totalAssignments() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.assignments || 0), 0);
        },

        get totalBalance() {
            return this.totalIncome - this.totalExpenses;
        },

        get activeMonth() {
            return this.activePoint === null ? this.selectedMonth : this.visibleMonths[this.activePoint];
        },

        get cashMaximum() {
            return Math.max(...this.visibleMonths.flatMap((month) => [Number(month.income || 0), Number(month.expenses || 0)]), 1);
        },

        pointX(index) {
            const count = this.visibleMonths.length;
            return count <= 1 ? 500 : 30 + (index / (count - 1)) * 940;
        },

        pointY(month, field) {
            return 205 - (Number(month?.[field] || 0) / this.cashMaximum) * 165;
        },

        trendPoints(field) {
            return this.visibleMonths.map((month, index) => `${this.pointX(index)},${this.pointY(month, field)}`).join(' ');
        },

        showLabel(index) {
            const step = this.range <= 6 ? 1 : 2;
            return index % step === 0 || index === this.visibleMonths.length - 1;
        },

        selectPeriod(period) {
            this.selectedPeriod = period;
            this.activePoint = period
                ? this.visibleMonths.findIndex((month) => month.period === period)
                : null;
        },

        aggregate(field) {
            const totals = {};
            this.analysisMonths.forEach((month) => {
                Object.entries(month[field] || {}).forEach(([label, amount]) => {
                    totals[label] = (totals[label] || 0) + Number(amount || 0);
                });
            });
            return Object.entries(totals)
                .map(([label, amount]) => ({ label: label.replaceAll('_', ' '), amount }))
                .sort((left, right) => right.amount - left.amount)
                .slice(0, 5);
        },

        get categoryRows() {
            const field = this.categoryType === 'egreso' ? 'expenseCategories' : 'incomeCategories';
            return this.withWidths(this.aggregate(field));
        },

        get purposeRows() {
            return this.withWidths(this.aggregate('purposes'));
        },

        withWidths(rows) {
            const maximum = Math.max(...rows.map((row) => row.amount), 1);
            return rows.map((row) => ({
                ...row,
                width: `${Math.max((row.amount / maximum) * 100, row.amount > 0 ? 4 : 0)}%`,
            }));
        },

        formatMoney(value) {
            return `S/. ${new Intl.NumberFormat('es-PE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(Number(value || 0))}`;
        },

        setRange(months) {
            this.range = months;
            if (!this.visibleMonths.some((month) => month.period === this.selectedPeriod)) {
                this.selectedPeriod = '';
            }
            this.activePoint = null;
        },
    }));

    Alpine.data('cheeseDashboard', (payload = {}) => ({
        range: 12,
        metric: 'weight',
        annualMetric: 'weight',
        selectedPeriod: '',
        activePoint: null,
        monthly: Array.isArray(payload.monthly) ? payload.monthly : [],
        annual: Array.isArray(payload.annual) ? payload.annual : [],
        presentationLabels: payload.presentationLabels || {},
        presentationColors: ['#10b981', '#0ea5e9', '#8b5cf6', '#f59e0b', '#f43f5e', '#14b8a6'],

        get visibleMonths() {
            return this.monthly.slice(-Math.min(this.range, this.monthly.length));
        },

        get selectedMonth() {
            return this.visibleMonths.find((month) => month.period === this.selectedPeriod) || null;
        },

        get analysisMonths() {
            return this.selectedMonth ? [this.selectedMonth] : this.visibleMonths;
        },

        get activeMonths() {
            return this.analysisMonths.filter((month) => Number(month.records) > 0);
        },

        get totalWeight() {
            return this.analysisMonths.reduce((total, month) => total + Number(month.weight || 0), 0);
        },

        get totalUnits() {
            return this.analysisMonths.reduce((total, month) => total + Number(month.units || 0), 0);
        },

        get totalRecords() {
            return this.analysisMonths.reduce((total, month) => total + Number(month.records || 0), 0);
        },

        get totalDays() {
            return this.analysisMonths.reduce((total, month) => total + Number(month.days || 0), 0);
        },

        get averageDailyWeight() {
            return this.totalDays > 0 ? this.totalWeight / this.totalDays : 0;
        },

        get bestMonth() {
            return this.analysisMonths.reduce((best, month) => (
                !best || Number(month.weight) > Number(best.weight) ? month : best
            ), null);
        },

        get currentMonth() {
            return this.selectedMonth || this.monthly.at(-1) || null;
        },

        get previousMonth() {
            const currentIndex = this.monthly.findIndex((month) => month.period === this.currentMonth?.period);
            return currentIndex > 0 ? this.monthly[currentIndex - 1] : null;
        },

        get monthlyChange() {
            const current = Number(this.currentMonth?.weight || 0);
            const previous = Number(this.previousMonth?.weight || 0);
            if (previous === 0) return current === 0 ? 0 : null;

            return ((current - previous) / previous) * 100;
        },

        value(item, metric = this.metric) {
            return Number(item?.[metric] || 0);
        },

        format(value, decimals = 0) {
            return new Intl.NumberFormat('es-PE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(Number(value || 0));
        },

        metricText(value, metric = this.metric) {
            return metric === 'weight'
                ? `${this.format(value, 2)} kg`
                : `${this.format(value)} moldes`;
        },

        changeText() {
            if (this.monthlyChange === null) return 'Sin base anterior';
            if (this.monthlyChange === 0) return 'Sin variación';

            return `${this.monthlyChange > 0 ? '+' : ''}${this.format(this.monthlyChange, 1)}% vs. mes anterior`;
        },

        setRange(months) {
            this.range = months;
            if (!this.visibleMonths.some((month) => month.period === this.selectedPeriod)) {
                this.selectedPeriod = '';
            }
            this.activePoint = null;
        },

        selectPeriod(period) {
            this.selectedPeriod = period;
            this.activePoint = period
                ? this.visibleMonths.findIndex((month) => month.period === period)
                : null;
        },

        pointX(index) {
            const count = this.visibleMonths.length;
            return count <= 1 ? 500 : 25 + (index / (count - 1)) * 950;
        },

        pointY(item) {
            const maximum = Math.max(...this.visibleMonths.map((month) => this.value(month)), 1);
            return 210 - (this.value(item) / maximum) * 175;
        },

        get trendPoints() {
            return this.visibleMonths
                .map((month, index) => `${this.pointX(index)},${this.pointY(month)}`)
                .join(' ');
        },

        get areaPoints() {
            if (this.visibleMonths.length === 0) return '';

            return `25,210 ${this.trendPoints} 975,210`;
        },

        showMonthLabel(index) {
            const step = this.range <= 6 ? 1 : (this.range <= 12 ? 2 : 4);
            return index % step === 0 || index === this.visibleMonths.length - 1;
        },

        get activeMonth() {
            return this.activePoint === null ? this.selectedMonth : this.visibleMonths[this.activePoint];
        },

        get presentationData() {
            const totals = {};
            this.analysisMonths.forEach((month) => {
                Object.entries(month.presentations || {}).forEach(([weight, quantity]) => {
                    totals[weight] = (totals[weight] || 0) + Number(quantity || 0);
                });
            });
            const total = Object.values(totals).reduce((sum, quantity) => sum + quantity, 0);

            return Object.entries(totals)
                .filter(([, quantity]) => quantity > 0)
                .sort(([, left], [, right]) => right - left)
                .map(([weight, quantity], index) => ({
                    weight,
                    label: this.presentationLabels[weight] || `${weight} gramos`,
                    quantity,
                    percentage: total > 0 ? (quantity / total) * 100 : 0,
                    color: this.presentationColors[index % this.presentationColors.length],
                }));
        },

        get presentationTotal() {
            return this.presentationData.reduce((total, item) => total + item.quantity, 0);
        },

        get donutBackground() {
            if (this.presentationData.length === 0) return 'conic-gradient(#cbd5e1 0 100%)';
            let offset = 0;
            const segments = this.presentationData.map((item) => {
                const start = offset;
                offset += item.percentage;
                return `${item.color} ${start}% ${offset}%`;
            });

            return `conic-gradient(${segments.join(', ')})`;
        },

        get annualRows() {
            return this.annual.slice(-6).reverse();
        },

        annualValue(row) {
            return Number(row?.[this.annualMetric] || 0);
        },

        annualWidth(row) {
            const maximum = Math.max(...this.annualRows.map((item) => this.annualValue(item)), 1);
            return `${Math.max((this.annualValue(row) / maximum) * 100, this.annualValue(row) > 0 ? 4 : 0)}%`;
        },

        get weekdayRows() {
            const labels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
            const totals = labels.map((label, index) => ({ label, weight: 0, units: 0, days: 0, key: index + 1 }));
            this.analysisMonths.forEach((month) => {
                totals.forEach((day) => {
                    const values = month.weekdays?.[day.key] || {};
                    day.weight += Number(values.weight || 0);
                    day.units += Number(values.units || 0);
                    day.days += Number(values.days || 0);
                });
            });
            const maximum = Math.max(...totals.map((day) => day.weight), 1);

            return totals.map((day) => ({
                ...day,
                height: `${Math.max((day.weight / maximum) * 100, day.weight > 0 ? 5 : 0)}%`,
            }));
        },
    }));

    Alpine.data('animalDashboard', (payload = {}) => ({
        range: 12,
        selectedPeriod: '',
        activePoint: null,
        monthly: Array.isArray(payload.monthly) ? payload.monthly : [],
        especies: Array.isArray(payload.especies) ? payload.especies : [],
        estados: Array.isArray(payload.estados) ? payload.estados : [],

        get visibleMonths() {
            return this.monthly.slice(-Math.min(this.range, this.monthly.length));
        },

        get selectedMonth() {
            return this.visibleMonths.find((month) => month.period === this.selectedPeriod) || null;
        },

        get activeMonth() {
            return this.activePoint === null ? this.selectedMonth : this.visibleMonths[this.activePoint];
        },

        get analysisMonths() {
            return this.selectedMonth ? [this.selectedMonth] : this.visibleMonths;
        },

        get periodCount() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.count || 0), 0);
        },

        get periodHembras() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.hembras || 0), 0);
        },

        get periodMachos() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.machos || 0), 0);
        },

        get maxMonthlyCount() {
            return Math.max(...this.visibleMonths.map((month) => Number(month.count || 0)), 1);
        },

        pointX(index) {
            const count = this.visibleMonths.length;
            return count <= 1 ? 500 : 30 + (index / (count - 1)) * 940;
        },

        pointY(count) {
            return 205 - (Number(count || 0) / this.maxMonthlyCount) * 165;
        },

        get trendPoints() {
            return this.visibleMonths
                .map((month, index) => `${this.pointX(index)},${this.pointY(month.count)}`)
                .join(' ');
        },

        get areaPoints() {
            if (this.visibleMonths.length === 0) return '';
            return `30,210 ${this.trendPoints} 970,210`;
        },

        showMonthLabel(index) {
            const step = this.range <= 6 ? 1 : 2;
            return index % step === 0 || index === this.visibleMonths.length - 1;
        },

        selectPeriod(period) {
            this.selectedPeriod = period;
            this.activePoint = period
                ? this.visibleMonths.findIndex((month) => month.period === period)
                : null;
        },

        setRange(months) {
            this.range = months;
            if (!this.visibleMonths.some((month) => month.period === this.selectedPeriod)) {
                this.selectedPeriod = '';
            }
            this.activePoint = null;
        },
    }));

    Alpine.data('engordeDashboard', (payload = {}) => ({
        range: 12,
        dropdownOpen: false,
        selectedPeriod: '',
        activePoint: null,
        monthly: Array.isArray(payload.monthly) ? payload.monthly : [],

        get visibleMonths() {
            return this.monthly.slice(-Math.min(this.range, this.monthly.length));
        },

        get selectedMonth() {
            return this.visibleMonths.find((month) => month.period === this.selectedPeriod) || null;
        },

        get activeMonth() {
            return this.activePoint === null ? this.selectedMonth : this.visibleMonths[this.activePoint];
        },

        get analysisMonths() {
            return this.selectedMonth ? [this.selectedMonth] : this.visibleMonths;
        },

        get periodCount() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.count || 0), 0);
        },

        get periodHembras() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.hembras || 0), 0);
        },

        get periodMachos() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.machos || 0), 0);
        },

        get maxMonthlyCount() {
            return Math.max(...this.visibleMonths.map((month) => Number(month.count || 0)), 1);
        },

        pointX(index) {
            const count = this.visibleMonths.length;
            return count <= 1 ? 500 : 30 + (index / (count - 1)) * 940;
        },

        pointY(count) {
            return 205 - (Number(count || 0) / this.maxMonthlyCount) * 165;
        },

        get trendPoints() {
            return this.visibleMonths
                .map((month, index) => `${this.pointX(index)},${this.pointY(month.count)}`)
                .join(' ');
        },

        get areaPoints() {
            if (this.visibleMonths.length === 0) return '';
            return `30,210 ${this.trendPoints} 970,210`;
        },

        showMonthLabel(index) {
            const step = this.range <= 6 ? 1 : 2;
            return index % step === 0 || index === this.visibleMonths.length - 1;
        },

        selectPeriod(period) {
            this.selectedPeriod = period;
            this.activePoint = period
                ? this.visibleMonths.findIndex((month) => month.period === period)
                : null;
        },

        setRange(months) {
            this.range = months;
            if (!this.visibleMonths.some((month) => month.period === this.selectedPeriod)) {
                this.selectedPeriod = '';
            }
            this.activePoint = null;
        },
    }));

    Alpine.data('globalDashboard', (payload = {}) => ({
        performanceMetric: payload.allowedModules?.leche ? 'milk' : 'cheese',
        performanceRange: 6,
        performanceHover: null,
        performanceSelectedPeriod: '',
        milkMonthly: Array.isArray(payload.milkMonthly) ? payload.milkMonthly : [],
        cheeseMonthly: Array.isArray(payload.cheeseMonthly) ? payload.cheeseMonthly : [],

        get performanceSource() {
            return this.performanceMetric === 'milk' ? this.milkMonthly : this.cheeseMonthly;
        },

        get performanceVisible() {
            return this.performanceSource.slice(-Math.min(this.performanceRange, this.performanceSource.length));
        },

        get performanceSelectedMonth() {
            return this.performanceVisible.find((month) => month.period === this.performanceSelectedPeriod) || null;
        },

        get performanceActiveMonth() {
            return this.performanceHover === null
                ? this.performanceSelectedMonth
                : this.performanceVisible[this.performanceHover];
        },

        get performanceMax() {
            return Math.max(...this.performanceVisible.map((month) => Number(month.total || 0)), 1);
        },

        performancePointX(index) {
            const count = this.performanceVisible.length;
            return count <= 1 ? 500 : 30 + (index / (count - 1)) * 940;
        },

        performancePointY(value) {
            return 205 - (Number(value || 0) / this.performanceMax) * 165;
        },

        get performanceTrendPoints() {
            return this.performanceVisible
                .map((month, index) => `${this.performancePointX(index)},${this.performancePointY(month.total)}`)
                .join(' ');
        },

        get performanceAreaPoints() {
            if (this.performanceVisible.length === 0) return '';
            return `30,205 ${this.performanceTrendPoints} 970,205`;
        },

        get performanceSummary() {
            const active = this.performanceActiveMonth;
            const rows = active ? [active] : this.performanceVisible;
            const total = rows.reduce((sum, row) => sum + Number(row.total || 0), 0);
            const secondaryKey = this.performanceMetric === 'milk' ? 'records' : 'units';
            const best = this.performanceVisible.reduce(
                (winner, row) => Number(row.total || 0) > Number(winner?.total || 0) ? row : winner,
                null
            );

            return {
                total,
                average: rows.length ? total / rows.length : 0,
                secondary: rows.reduce((sum, row) => sum + Number(row[secondaryKey] || 0), 0),
                bestLabel: best?.label || 'Sin datos',
            };
        },

        setPerformanceMetric(metric) {
            this.performanceMetric = metric;
            this.performanceHover = null;
            this.performanceSelectedPeriod = '';
        },

        setPerformanceRange(months) {
            this.performanceRange = months;
            this.performanceHover = null;
            if (!this.performanceVisible.some((month) => month.period === this.performanceSelectedPeriod)) {
                this.performanceSelectedPeriod = '';
            }
        },

        selectPerformanceMonth(period) {
            this.performanceSelectedPeriod = this.performanceSelectedPeriod === period ? '' : period;
            this.performanceHover = null;
        },

        financeRange: 6,
        financeHover: null,
        financeSelectedPeriod: '',
        financeMonthly: Array.isArray(payload.financeMonthly) ? payload.financeMonthly : [],

        get financeVisible() {
            return this.financeMonthly.slice(-Math.min(this.financeRange, this.financeMonthly.length));
        },

        get financeSelectedMonth() {
            return this.financeVisible.find((month) => month.period === this.financeSelectedPeriod) || null;
        },

        get financeActiveMonth() {
            return this.financeHover === null
                ? this.financeSelectedMonth
                : this.financeVisible[this.financeHover];
        },

        get financeMax() {
            return Math.max(
                ...this.financeVisible.map((month) => Math.max(Number(month.income || 0), Number(month.expense || 0))),
                1
            );
        },

        financeBarPercent(value) {
            const percentage = (Number(value || 0) / this.financeMax) * 100;
            return Math.max(percentage, 2);
        },

        get financeSummary() {
            const active = this.financeActiveMonth;
            const rows = active ? [active] : this.financeVisible;
            const income = rows.reduce((sum, row) => sum + Number(row.income || 0), 0);
            const expense = rows.reduce((sum, row) => sum + Number(row.expense || 0), 0);

            return { income, expense, balance: income - expense };
        },

        setFinanceRange(months) {
            this.financeRange = months;
            this.financeHover = null;
            if (!this.financeVisible.some((month) => month.period === this.financeSelectedPeriod)) {
                this.financeSelectedPeriod = '';
            }
        },

        selectFinanceMonth(period) {
            this.financeSelectedPeriod = this.financeSelectedPeriod === period ? '' : period;
            this.financeHover = null;
        },

        species: Array.isArray(payload.species) ? payload.species : [],
        inventorySelected: null,
        inventoryColors: ['#0ea5e9', '#22c55e', '#a3e635', '#f59e0b', '#f43f5e', '#8b5cf6', '#14b8a6'],

        get inventoryTotal() {
            return this.species.reduce((sum, row) => sum + Number(row.count || 0), 0);
        },

        get inventoryActive() {
            return this.inventorySelected === null ? null : this.species[this.inventorySelected];
        },

        get inventoryGradient() {
            if (this.inventoryTotal <= 0) return '#e4e4e7';

            let current = 0;
            const segments = this.species.map((row, index) => {
                const start = current;
                current += (Number(row.count || 0) / this.inventoryTotal) * 100;
                return `${this.inventoryColors[index % this.inventoryColors.length]} ${start}% ${current}%`;
            });

            return `conic-gradient(${segments.join(', ')})`;
        },

        formatNumber(value, decimals = 0) {
            return new Intl.NumberFormat('es-PE', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals,
            }).format(Number(value || 0));
        },
    }));

    Alpine.data('lecheDashboard', (payload = {}) => ({
        range: 12,
        selectedPeriod: '',
        activePoint: null,
        monthly: Array.isArray(payload.monthly) ? payload.monthly : [],

        get visibleMonths() {
            return this.monthly.slice(-Math.min(this.range, this.monthly.length));
        },

        get selectedMonth() {
            return this.visibleMonths.find((month) => month.period === this.selectedPeriod) || null;
        },

        get activeMonth() {
            return this.activePoint === null ? this.selectedMonth : this.visibleMonths[this.activePoint];
        },

        get analysisMonths() {
            return this.selectedMonth ? [this.selectedMonth] : this.visibleMonths;
        },

        get periodCount() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.count || 0), 0);
        },

        get maxMonthlyCount() {
            return Math.max(...this.visibleMonths.map((month) => Number(month.count || 0)), 1);
        },

        pointX(index) {
            const count = this.visibleMonths.length;
            return count <= 1 ? 500 : 30 + (index / (count - 1)) * 940;
        },

        pointY(count) {
            return 205 - (Number(count || 0) / this.maxMonthlyCount) * 165;
        },

        get trendPoints() {
            return this.visibleMonths
                .map((month, index) => `${this.pointX(index)},${this.pointY(month.count)}`)
                .join(' ');
        },

        get areaPoints() {
            if (this.visibleMonths.length === 0) return '';
            return `30,210 ${this.trendPoints} 970,210`;
        },

        showMonthLabel(index) {
            const step = this.range <= 6 ? 1 : 2;
            return index % step === 0 || index === this.visibleMonths.length - 1;
        },

        selectPeriod(period) {
            this.selectedPeriod = period;
            this.activePoint = period
                ? this.visibleMonths.findIndex((month) => month.period === period)
                : null;
        },

        setRange(months) {
            this.range = months;
            if (!this.visibleMonths.some((month) => month.period === this.selectedPeriod)) {
                this.selectedPeriod = '';
            }
            this.activePoint = null;
        },
    }));

    Alpine.data('monitoreoDashboard', (payload = {}) => ({
        range: 12,
        selectedPeriod: '',
        activePoint: null,
        monthly: Array.isArray(payload.monthly) ? payload.monthly : [],

        get visibleMonths() {
            return this.monthly.slice(-Math.min(this.range, this.monthly.length));
        },

        get selectedMonth() {
            return this.visibleMonths.find((month) => month.period === this.selectedPeriod) || null;
        },

        get activeMonth() {
            return this.activePoint === null ? this.selectedMonth : this.visibleMonths[this.activePoint];
        },

        get analysisMonths() {
            return this.selectedMonth ? [this.selectedMonth] : this.visibleMonths;
        },

        get periodCount() {
            return this.analysisMonths.reduce((sum, month) => sum + Number(month.count || 0), 0);
        },

        get maxMonthlyCount() {
            return Math.max(...this.visibleMonths.map((month) => Number(month.count || 0)), 1);
        },

        pointX(index) {
            const count = this.visibleMonths.length;
            return count <= 1 ? 500 : 30 + (index / (count - 1)) * 940;
        },

        pointY(count) {
            return 205 - (Number(count || 0) / this.maxMonthlyCount) * 165;
        },

        get trendPoints() {
            return this.visibleMonths
                .map((month, index) => `${this.pointX(index)},${this.pointY(month.count)}`)
                .join(' ');
        },

        get areaPoints() {
            if (this.visibleMonths.length === 0) return '';
            return `30,210 ${this.trendPoints} 970,210`;
        },

        showMonthLabel(index) {
            const step = this.range <= 6 ? 1 : 2;
            return index % step === 0 || index === this.visibleMonths.length - 1;
        },

        selectPeriod(period) {
            this.selectedPeriod = period;
            this.activePoint = period
                ? this.visibleMonths.findIndex((month) => month.period === period)
                : null;
        },

        setRange(months) {
            this.range = months;
            if (!this.visibleMonths.some((month) => month.period === this.selectedPeriod)) {
                this.selectedPeriod = '';
            }
            this.activePoint = null;
        },
    }));
};

if (typeof Alpine !== 'undefined') {
    registerAlpineComponents();
}

document.addEventListener('alpine:init', registerAlpineComponents);

document.addEventListener('livewire:initialized', () => {
    Livewire.on('swal:toast', (event) => {
        showToast(eventData(event));
    });

    Livewire.on('swal:modal', (event) => {
        showModal(eventData(event));
    });

    Livewire.on('swal:confirm', (event) => {
        const data = eventData(event);

        showModal(data, true).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch(data.event, data.id ? { id: data.id } : {});
            } else if (data.cancelEvent) {
                Livewire.dispatch(data.cancelEvent, data.id ? { id: data.id } : {});
            }
        });
    });

    Livewire.hook('request', ({ fail }) => {
        fail(({ status, preventDefault }) => {
            if (status === 419) {
                preventDefault();
                console.warn('Livewire session refreshed gracefully.');
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', showFlashAlert);
document.addEventListener('livewire:navigated', showFlashAlert);
document.addEventListener('DOMContentLoaded', initializeIdleLogout);
document.addEventListener('livewire:navigated', initializeIdleLogout);
window.addEventListener('branding-updated', applyBranding);

// Restaura el scroll del body cuando no hay modales REALMENTE visibles en pantalla
const syncBodyScrollLock = () => {
    const overlays = Array.from(document.querySelectorAll('.agro-dialog-overlay, .agro-dialog-overlay--pdf, .agro-dialog-overlay--full, .image-frame-editor-modal, [role="dialog"][aria-modal="true"]'));
    const isAnyModalVisible = overlays.some((el) => {
        if (el.hasAttribute('hidden') || el.style.display === 'none' || el.classList.contains('hidden') || el.hasAttribute('x-cloak')) {
            return false;
        }
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') {
            return false;
        }
        return el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0;
    });

    document.body.classList.toggle('overflow-hidden', isAnyModalVisible);
};
const observer = new MutationObserver(syncBodyScrollLock);
observer.observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class', 'hidden'] });
document.addEventListener('DOMContentLoaded', syncBodyScrollLock);
document.addEventListener('livewire:navigated', syncBodyScrollLock);
