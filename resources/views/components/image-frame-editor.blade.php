@props([
    'id',
    'src',
    'xModel',
    'yModel',
    'zoomModel',
    'initiallyOpen' => false,
    'saveAction' => null,
    'closeAction' => null,
    'applyLabel' => 'Aplicar encuadre',
    'title' => null,
    'description' => null,
    'mode' => 'simple',
    'minZoom' => null,
    'maxZoom' => null,
])

@php
    $isInitiallyOpen = filter_var($initiallyOpen, FILTER_VALIDATE_BOOLEAN);
    $isSimple = $mode === 'simple';
    $minZoom = $minZoom ?? ($isSimple ? 1 : 0.3);
    $maxZoom = $maxZoom ?? ($isSimple ? 4 : 2.5);
    $title = $title ?? ($isSimple ? 'Ajustar imagen' : 'Ajustar encuadre del logo');
    $description = $description ?? ($isSimple
        ? 'Arrastra la imagen para moverla y ajusta el zoom al tamaño deseado.'
        : 'Arrastra la imagen y ajusta el zoom para seleccionar la porción exacta que se mostrará.');
    $dialogId = $id.'-dialog';
    $titleId = $id.'-title';
    $descriptionId = $id.'-description';
@endphp

<div
    x-data="imageFrameEditor({
        initiallyOpen: @js($isInitiallyOpen),
        simple: @js($isSimple),
        screen: 'desktop',
        minZoom: @js($minZoom),
        maxZoom: @js($maxZoom),
        focusX: $wire.entangle(@js($xModel)),
        focusY: $wire.entangle(@js($yModel)),
        zoom: $wire.entangle(@js($zoomModel)),
        saveAction: @js($saveAction),
        closeAction: @js($closeAction),
    })"
    {{ $attributes->class(['image-frame-editor']) }}
>
    @unless($isInitiallyOpen)
        <button
            x-ref="trigger"
            type="button"
            x-on:click.stop.prevent="open($event)"
            x-bind:aria-expanded="visible"
            aria-controls="{{ $dialogId }}"
            aria-haspopup="dialog"
            class="image-frame-editor__trigger"
        >
            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 8V5a1 1 0 0 1 1-1h3m8 0h3a1 1 0 0 1 1 1v3m0 8v3a1 1 0 0 1-1 1h-3m-8 0H5a1 1 0 0 1-1-1v-3M8 12h8m-4-4v8" />
            </svg>
            Ajustar
        </button>
    @endunless

    <template x-teleport="body">
        <div
            x-cloak
            x-show="visible"
            x-on:click.self="cancel()"
            x-on:keydown.escape.window="cancel()"
            x-transition.opacity.duration.150ms
            class="agro-dialog-overlay image-frame-editor__overlay"
        >
            <section
                id="{{ $dialogId }}"
                x-ref="dialog"
                x-show="visible"
                x-on:click.stop
                x-on:keydown="onKeydown($event)"
                x-on:keydown.tab="trapFocus($event)"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-3 scale-[.98]"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-3 scale-[.98]"
                role="dialog"
                aria-modal="true"
                aria-labelledby="{{ $titleId }}"
                aria-describedby="{{ $descriptionId }}"
                tabindex="-1"
                class="agro-dialog image-frame-editor__dialog max-w-4xl"
            >
                <header class="image-frame-editor__header">
                    <div class="image-frame-editor__heading">
                        <p class="agro-kicker">Edición no destructiva</p>
                        <h2 id="{{ $titleId }}" class="image-frame-editor__title">{{ $title }}</h2>
                        <p id="{{ $descriptionId }}" class="image-frame-editor__description">{{ $description }}</p>
                    </div>
                    <button type="button" x-on:click="cancel()" class="image-frame-editor__close" aria-label="Cerrar editor">&times;</button>
                </header>

                <div class="image-frame-editor__body">
                    <div class="image-frame-editor__workspace {{ $isSimple ? 'image-frame-editor__workspace--simple' : '' }}">
                        <section class="image-frame-editor__preview-panel" aria-label="Vista previa del encuadre">
                            <div class="image-frame-editor__preview-toolbar">
                                <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300">{{ $isSimple ? 'Encuadre de la imagen' : 'Encuadre del Logo (1:1)' }}</span>
                                @if($isSimple)
                                    <div class="image-frame-editor__modes" role="group" aria-label="Vista previa por pantalla">
                                        <button type="button" class="image-frame-editor__mode" :class="screen === 'desktop' && 'image-frame-editor__mode--active'" x-on:click="switchScreen('desktop')">Escritorio / Tablet</button>
                                        <button type="button" class="image-frame-editor__mode" :class="screen === 'mobile' && 'image-frame-editor__mode--active'" x-on:click="switchScreen('mobile')">Móvil</button>
                                    </div>
                                @else
                                    <span class="image-frame-editor__preview-label">Formato simétrico</span>
                                @endif
                            </div>

                            <div class="image-frame-editor__stage flex items-center justify-center p-3">
                                <div
                                    x-ref="frame"
                                    x-on:pointerdown="startDrag($event)"
                                    x-on:pointermove="drag($event)"
                                    x-on:pointerup="endDrag($event)"
                                    x-on:pointercancel="endDrag($event)"
                                    x-on:lostpointercapture="endDrag($event)"
                                    x-on:wheel.prevent="onWheel($event)"
                                    x-bind:style="{ cursor: dragging ? 'grabbing' : 'grab' }"
                                    class="image-frame-editor__frame {{ $isSimple ? 'image-frame-editor__frame--simple border-emerald-400/50' : 'image-frame-editor__frame--square border-sky-400/40' }} relative overflow-hidden rounded-3xl border-2 bg-zinc-950 shadow-2xl select-none"
                                    :class="simple ? (screen === 'mobile' ? 'image-frame-editor__frame--mobile' : 'image-frame-editor__frame--horizontal') : ''"
                                >
                                    @if($isSimple)
                                        <!-- Fondo: misma imagen difuminada para que nunca se vea negro al alejar -->
                                        <img src="{{ $src }}" alt="" aria-hidden="true" draggable="false"
                                             class="pointer-events-none absolute inset-0 h-full w-full scale-110 object-cover opacity-30 blur-md select-none">
                                    @endif
                                    <!-- Imagen con encuadre directo real -->
                                    <img
                                        src="{{ $src }}"
                                        alt="Vista previa del encuadre"
                                        draggable="false"
                                        x-bind:style="{
                                            objectPosition: `${focusX}% ${focusY}%`,
                                            transform: `scale(${zoom})`,
                                            transformOrigin: `${focusX}% ${focusY}%`,
                                        }"
                                        class="h-full w-full object-cover select-none pointer-events-none transition-none"
                                    >

                                    @unless($isSimple)
                                        <!-- Guía circular celeste de encuadre (Login / Perfil) -->
                                        <div class="pointer-events-none absolute inset-2 rounded-full border-2 border-sky-400/80 shadow-[0_0_15px_rgba(56,189,248,0.35)]"></div>

                                        <!-- Rejilla tenue de tercios -->
                                        <div class="image-frame-editor__crop-grid pointer-events-none absolute inset-0 opacity-15"></div>

                                        <!-- Esquinas blancas cropper -->
                                        <span class="image-frame-editor__crop-handle handle-top-left"></span>
                                        <span class="image-frame-editor__crop-handle handle-top-right"></span>
                                        <span class="image-frame-editor__crop-handle handle-bottom-left"></span>
                                        <span class="image-frame-editor__crop-handle handle-bottom-right"></span>
                                    @endunless

                                    <span class="image-frame-editor__drag-hint">Arrastra para mover la imagen</span>
                                </div>
                            </div>
                            @if($isSimple)
                                <div class="image-frame-editor__zoom-bar">
                                    <button type="button" class="image-frame-editor__zoom-btn" title="Alejar" aria-label="Alejar imagen" x-on:click="zoom = Math.max(minZoom, +((Number(zoom) - 0.1).toFixed(2)))" x-bind:disabled="Number(zoom) <= minZoom">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2.2"><path d="M5 12h14"/></svg>
                                    </button>
                                    <input type="range" :min="minZoom" :max="maxZoom" step="0.02" x-model.number="zoom" class="image-frame-editor__range image-frame-editor__zoom-bar-range" aria-label="Zoom">
                                    <button type="button" class="image-frame-editor__zoom-btn" title="Acercar" aria-label="Acercar imagen" x-on:click="zoom = Math.min(maxZoom, +((Number(zoom) + 0.1).toFixed(2)))" x-bind:disabled="Number(zoom) >= maxZoom">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                                    </button>
                                    <button type="button" x-on:click="reset()" class="image-frame-editor__reset" title="Restablecer encuadre">Restablecer</button>
                                </div>
                            @else
                                <p class="image-frame-editor__preview-help">El círculo celeste indica la zona del login; la caja cuadrada indica el uso en la barra superior.</p>
                            @endif
                        </section>

                        <aside class="image-frame-editor__inspector" aria-label="Controles de encuadre">
                            <div class="image-frame-editor__inspector-header">
                                <div>
                                    <h3 class="image-frame-editor__inspector-title">{{ $isSimple ? 'Encuadre' : 'Posición y escala' }}</h3>
                                    <p class="image-frame-editor__inspector-copy">{{ $isSimple ? 'Arrastra la imagen para moverla.' : 'Ajuste fino del área visible.' }}</p>
                                </div>
                                @unless($isSimple)
                                    <div class="flex items-center gap-1.5">
                                        <button type="button" x-on:click="focusX = 50; focusY = 50;" class="image-frame-editor__reset" title="Centrar foco">Centrar</button>
                                        <button type="button" x-on:click="reset()" class="image-frame-editor__reset">Restablecer</button>
                                    </div>
                                @endunless
                            </div>

                            @unless($isSimple)
                                <!-- Muestra Mini-Preview en vivo del resultado circular y cuadrado -->
                                <div class="image-frame-editor__result-preview my-3 rounded-2xl border border-zinc-200/80 bg-zinc-50/90 p-3 dark:border-zinc-800/80 dark:bg-zinc-900/80">
                                    <span class="block text-[11px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">Vista previa final:</span>
                                    <div class="flex items-center gap-4 justify-around">
                                        <div class="text-center">
                                            <div class="relative h-12 w-12 overflow-hidden rounded-full border-2 border-emerald-500/50 bg-zinc-950 shadow-md">
                                                <img src="{{ $src }}"
                                                     alt="Vista previa circular"
                                                     x-bind:style="{
                                                         objectPosition: `${focusX}% ${focusY}%`,
                                                         transform: `scale(${zoom})`,
                                                         transformOrigin: `${focusX}% ${focusY}%`,
                                                     }"
                                                     class="h-full w-full object-cover">
                                            </div>
                                            <span class="mt-1 block text-[10px] font-semibold text-zinc-500">Login</span>
                                        </div>
                                        <div class="text-center">
                                            <div class="relative h-12 w-12 overflow-hidden rounded-xl border-2 border-emerald-500/50 bg-zinc-950 shadow-md">
                                                <img src="{{ $src }}"
                                                     alt="Vista previa cuadrada"
                                                     x-bind:style="{
                                                         objectPosition: `${focusX}% ${focusY}%`,
                                                         transform: `scale(${zoom})`,
                                                         transformOrigin: `${focusX}% ${focusY}%`,
                                                     }"
                                                     class="h-full w-full object-cover">
                                            </div>
                                            <span class="mt-1 block text-[10px] font-semibold text-zinc-500">Navbar</span>
                                        </div>
                                    </div>
                                </div>
                            @endunless

                            @unless($isSimple)
                            <div class="image-frame-editor__controls">
                                <label class="image-frame-editor__control">
                                    <span class="image-frame-editor__control-label">
                                        <span>Horizontal</span>
                                        <span class="image-frame-editor__control-value" x-text="`${Math.round(Number(focusX) || 0)}%`"></span>
                                    </span>
                                    <input type="range" min="0" max="100" step="1" x-model.number="focusX" class="image-frame-editor__range">
                                </label>
                                <label class="image-frame-editor__control">
                                    <span class="image-frame-editor__control-label">
                                        <span>Vertical</span>
                                        <span class="image-frame-editor__control-value" x-text="`${Math.round(Number(focusY) || 0)}%`"></span>
                                    </span>
                                    <input type="range" min="0" max="100" step="1" x-model.number="focusY" class="image-frame-editor__range">
                                </label>
                                <label class="image-frame-editor__control">
                                    <span class="image-frame-editor__control-label">
                                        <span>Zoom</span>
                                        <span class="image-frame-editor__control-value" x-text="`${Number(zoom || 1).toFixed(2)}×`"></span>
                                    </span>
                                    <div class="image-frame-editor__zoom-row">
                                        <button
                                            type="button"
                                            title="Alejar (zoom out)"
                                            aria-label="Alejar imagen"
                                            x-on:click="zoom = Math.max(minZoom, +((Number(zoom) - 0.1).toFixed(2)))"
                                            x-bind:disabled="Number(zoom) <= minZoom"
                                            class="image-frame-editor__zoom-btn"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2.2"><path d="M5 12h14"/></svg>
                                        </button>
                                        <input
                                            type="range"
                                            :min="minZoom"
                                            :max="maxZoom"
                                            step="0.02"
                                            x-model.number="zoom"
                                            class="image-frame-editor__range"
                                            style="flex: 1;"
                                        >
                                        <button
                                            type="button"
                                            title="Acercar (zoom in)"
                                            aria-label="Acercar imagen"
                                            x-on:click="zoom = Math.min(maxZoom, +((Number(zoom) + 0.1).toFixed(2)))"
                                            x-bind:disabled="Number(zoom) >= maxZoom"
                                            class="image-frame-editor__zoom-btn"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2.2"><path d="M12 5v14M5 12h14"/></svg>
                                        </button>
                                    </div>
                                </label>
                            </div>
                            @else
                                <p class="image-frame-editor__inspector-help">Zoom: usa la barra inferior, la rueda del mouse o Ctrl + / Ctrl − para acercar y alejar.</p>
                            @endunless

                            <div class="image-frame-editor__slot">{{ $slot }}</div>
                        </aside>
                    </div>
                </div>

                <footer class="image-frame-editor__footer">
                    <button type="button" x-on:click="cancel()" class="agro-button-secondary">Cancelar</button>
                    <button
                        type="button"
                        x-on:click="apply()"
                        @if($saveAction)
                            wire:loading.attr="disabled"
                            wire:target="{{ $saveAction }}"
                        @endif
                        class="agro-button"
                    >{{ $applyLabel }}</button>
                </footer>
            </section>
        </div>
    </template>
</div>
