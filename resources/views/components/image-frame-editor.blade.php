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
    'title' => 'Ajustar encuadre',
    'description' => 'Arrastra la fotografía y ajusta el zoom. El punto focal se adapta a formatos horizontales y móviles.',
])

@php
    $isInitiallyOpen = filter_var($initiallyOpen, FILTER_VALIDATE_BOOLEAN);
    $dialogId = $id.'-dialog';
    $titleId = $id.'-title';
    $descriptionId = $id.'-description';
@endphp

<div
    x-data="imageFrameEditor({
        initiallyOpen: @js($isInitiallyOpen),
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
                class="agro-dialog image-frame-editor__dialog"
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
                    <div class="image-frame-editor__workspace">
                        <section class="image-frame-editor__preview-panel" aria-label="Vista previa del encuadre">
                            <div class="image-frame-editor__preview-toolbar">
                                <div class="image-frame-editor__modes" role="group" aria-label="Formato de vista previa">
                                    <button
                                        type="button"
                                        x-on:click="previewMode = 'horizontal'"
                                        x-bind:class="previewMode === 'horizontal' && 'image-frame-editor__mode--active'"
                                        x-bind:aria-pressed="previewMode === 'horizontal'"
                                        class="image-frame-editor__mode"
                                    >Horizontal</button>
                                    <button
                                        type="button"
                                        x-on:click="previewMode = 'mobile'"
                                        x-bind:class="previewMode === 'mobile' && 'image-frame-editor__mode--active'"
                                        x-bind:aria-pressed="previewMode === 'mobile'"
                                        class="image-frame-editor__mode"
                                    >Móvil</button>
                                </div>
                                <span class="image-frame-editor__preview-label" x-text="previewMode === 'mobile' ? 'Vista 4:5' : 'Vista 16:10'"></span>
                            </div>

                            <div class="image-frame-editor__stage">
                                <div
                                    x-ref="frame"
                                    x-on:pointerdown="startDrag($event)"
                                    x-on:pointermove="drag($event)"
                                    x-on:pointerup="endDrag($event)"
                                    x-on:pointercancel="endDrag($event)"
                                    x-on:lostpointercapture="endDrag($event)"
                                    x-bind:class="previewMode === 'mobile' ? 'image-frame-editor__frame--mobile' : 'image-frame-editor__frame--horizontal'"
                                    x-bind:style="{ cursor: dragging ? 'grabbing' : 'grab' }"
                                    class="image-frame-editor__frame"
                                >
                                    <img
                                        src="{{ $src }}"
                                        alt="Vista previa de la imagen"
                                        draggable="false"
                                        x-bind:style="{
                                            objectPosition: `${focusX}% ${focusY}%`,
                                            transform: `scale(${zoom})`,
                                            transformOrigin: `${focusX}% ${focusY}%`,
                                        }"
                                        class="image-frame-editor__image"
                                    >
                                    <span
                                        class="image-frame-editor__focus"
                                        x-bind:style="{ left: `${focusX}%`, top: `${focusY}%` }"
                                        aria-hidden="true"
                                    ></span>
                                    <span class="image-frame-editor__drag-hint">Arrastra para mover</span>
                                </div>
                            </div>
                            <p class="image-frame-editor__preview-help">Comprueba ambos formatos: el encuadre se conserva al cambiar de vista.</p>
                        </section>

                        <aside class="image-frame-editor__inspector" aria-label="Controles de encuadre">
                            <div class="image-frame-editor__inspector-header">
                                <div>
                                    <h3 class="image-frame-editor__inspector-title">Posición y escala</h3>
                                    <p class="image-frame-editor__inspector-copy">Ajuste fino del punto focal.</p>
                                </div>
                                <button type="button" x-on:click="reset()" class="image-frame-editor__reset">Restablecer</button>
                            </div>

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
                                    <input type="range" min="1" max="2.5" step="0.05" x-model.number="zoom" class="image-frame-editor__range">
                                </label>
                            </div>

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
