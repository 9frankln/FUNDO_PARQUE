<template x-teleport="body">
    <div
        x-cloak
        x-show="cameraOpen"
        x-transition.opacity.duration.150ms
        @keydown.escape.window="closeCamera()"
        class="agro-dialog-overlay"
        role="dialog"
        aria-modal="true"
        aria-label="Tomar fotografía"
    >
        <section x-ref="cameraDialog" @click.outside="closeCamera()" @keydown.tab="trapCameraFocus($event)" class="agro-dialog agro-dialog--md" tabindex="-1">
            <header class="flex items-center justify-between gap-4 border-b border-emerald-950/10 px-4 py-3 dark:border-emerald-200/10 sm:px-5">
                <div>
                    <h3 class="text-base font-extrabold text-emerald-950 dark:text-emerald-50">Tomar fotografía</h3>
                    <p class="text-xs text-emerald-900/60 dark:text-emerald-100/60">Centra imagen antes de capturar.</p>
                </div>
                <button type="button" @click="closeCamera()" class="agro-icon-button" aria-label="Cerrar cámara">&times;</button>
            </header>

            <div class="agro-dialog__scroll p-3 sm:p-5">
                <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden rounded-2xl bg-slate-950">
                    <video x-ref="cameraVideo" x-show="!cameraError" autoplay muted playsinline class="h-full w-full object-contain"></video>
                    <div x-show="cameraStarting" class="absolute inset-0 flex items-center justify-center bg-slate-950/80 text-sm font-bold text-white">
                        Iniciando cámara...
                    </div>
                    <div x-show="cameraError" class="max-w-sm space-y-3 px-5 text-center text-sm font-semibold leading-6 text-rose-200">
                        <p x-text="cameraError"></p>
                        <button type="button" @click="closeCamera(); $nextTick(() => $refs.captureInput?.click())" class="rounded-xl bg-white/10 px-4 py-2 text-xs font-bold text-white hover:bg-white/20">
                            Abrir cámara del dispositivo
                        </button>
                    </div>
                </div>
            </div>

            <footer class="flex flex-col-reverse gap-2 border-t border-emerald-950/10 p-3 dark:border-emerald-200/10 sm:flex-row sm:justify-end sm:px-5">
                <button type="button" @click="closeCamera()" class="agro-button-secondary">Cancelar</button>
                <button type="button" @click="captureCameraPhoto()" :disabled="!cameraReady || cameraStarting" class="agro-button">
                    Capturar foto
                </button>
            </footer>
        </section>
    </div>
</template>
