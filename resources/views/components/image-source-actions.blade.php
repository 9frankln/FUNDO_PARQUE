@props([
    'inputId',
    'handler' => 'selectPhoto',
    'multiple' => false,
    'accept' => 'image/jpeg,image/png,image/webp',
    'galleryLabel' => null,
])

<div {{ $attributes->class(['grid grid-cols-1 gap-2 min-[360px]:grid-cols-2']) }}>
    <button type="button" @click="openCamera($event)" :disabled="busy" class="agro-image-action">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.38a2.25 2.25 0 0 0 1.59-.66l.62-.62A2.25 2.25 0 0 1 10.43 4h3.14a2.25 2.25 0 0 1 1.59.66l.62.62a2.25 2.25 0 0 0 1.59.66h1.38A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.38a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
        </svg>
        Tomar foto
    </button>
    <input
        x-ref="captureInput"
        type="file"
        accept="image/jpeg,image/png,image/webp"
        capture="environment"
        class="sr-only"
        @change="{{ $handler }}($event)"
        :disabled="busy"
    >

    <button type="button" @click="$refs.galleryInput?.click()" :disabled="busy" class="agro-image-action">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5M3.75 15v3.75A2.25 2.25 0 0 0 6 21h12a2.25 2.25 0 0 0 2.25-2.25V15" />
        </svg>
        {{ $galleryLabel ?? ($multiple ? 'Elegir fotos' : 'Elegir imagen') }}
    </button>
    <input
        id="{{ $inputId }}"
        x-ref="galleryInput"
        type="file"
        accept="{{ $accept }}"
        @if($multiple) multiple @endif
        tabindex="-1"
        class="sr-only"
        @change="{{ $handler }}($event)"
        :disabled="busy"
    >
</div>

<x-camera-capture />
