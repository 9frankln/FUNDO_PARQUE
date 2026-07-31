@props(['title' => 'Fotos del registro', 'existingPhotos' => [], 'newPhotos' => [], 'newFrames' => []])

<section class="space-y-3 rounded-2xl border border-zinc-800/80 bg-zinc-900 p-4 sm:p-5">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-850 pb-2.5">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-sky-700 dark:text-sky-400">{{ $title }}</h3>
            <p class="mt-0.5 text-[10px] text-zinc-500">Una por vez o varias juntas · máximo 3 · WebP optimizado</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex h-9 items-center rounded-lg border border-zinc-300 bg-zinc-50 px-2.5 text-[10px] font-bold text-zinc-600 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-300">
                <span x-text="count"></span>&nbsp;de&nbsp;<span x-text="max"></span>
            </span>
        </div>
    </div>

    <div x-cloak x-show="count < max">
        <x-image-source-actions input-id="record-photo-input" handler="selectPhotos" multiple gallery-label="Elegir fotos" />
    </div>

    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
        @foreach($existingPhotos as $photo)
            @php
                $frame = \App\Support\ImageFrame::normalize($photo['frame'] ?? null);
            @endphp
            <div wire:key="existing-photo-{{ $photo['id'] }}" class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-zinc-800 bg-zinc-950">
                <img src="{{ $photo['url'] }}" alt="Foto existente del registro" loading="lazy" class="h-full w-full object-cover"
                     style="object-position: {{ $frame['x'] }}% {{ $frame['y'] }}%; transform: scale({{ $frame['zoom'] }}); transform-origin: {{ $frame['x'] }}% {{ $frame['y'] }}%;">
                <x-image-frame-editor
                    id="existing-record-photo-frame-{{ $photo['id'] }}"
                    :src="$photo['url']"
                    x-model="existingPhotoFrames.{{ $photo['id'] }}.x"
                    y-model="existingPhotoFrames.{{ $photo['id'] }}.y"
                    zoom-model="existingPhotoFrames.{{ $photo['id'] }}.zoom"
                />
                <button type="button" wire:click="removeExistingPhoto({{ $photo['id'] }})" x-on:click="removeOne()"
                        class="absolute right-1.5 top-1.5 flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-950/85 text-lg font-bold text-white opacity-100 transition hover:bg-rose-700 sm:opacity-0 sm:group-hover:opacity-100"
                        aria-label="Quitar foto">&times;</button>
            </div>
        @endforeach

        @foreach($newPhotos as $index => $photo)
            @if(!$errors->has('fotos.'.$index))
                @php
                    $photoUrl = $photo->temporaryUrl();
                    $frame = \App\Support\ImageFrame::normalize($newFrames[$index] ?? null);
                @endphp
                <div wire:key="new-photo-{{ $index }}-{{ $photo->getFilename() }}" class="group relative aspect-[4/3] overflow-hidden rounded-xl border border-sky-300 bg-sky-50 dark:border-sky-500/35 dark:bg-sky-950/30">
                    <img src="{{ $photoUrl }}" alt="Foto nueva del registro" class="h-full w-full object-cover"
                         style="object-position: {{ $frame['x'] }}% {{ $frame['y'] }}%; transform: scale({{ $frame['zoom'] }}); transform-origin: {{ $frame['x'] }}% {{ $frame['y'] }}%;">
                    <x-image-frame-editor
                        id="new-record-photo-frame-{{ $index }}"
                        :src="$photoUrl"
                        x-model="fotoEncuadres.{{ $index }}.x"
                        y-model="fotoEncuadres.{{ $index }}.y"
                        zoom-model="fotoEncuadres.{{ $index }}.zoom"
                    />
                    <span class="absolute bottom-1.5 left-1.5 rounded-md bg-sky-950/80 px-1.5 py-0.5 text-[9px] font-bold text-white">Nueva</span>
                    <button type="button" wire:click="removeNewPhoto({{ $index }})" x-on:click="removeOne()"
                            class="absolute right-1.5 top-1.5 flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-950/85 text-lg font-bold text-white transition hover:bg-rose-700"
                            aria-label="Descartar foto">&times;</button>
                </div>
            @endif
        @endforeach

        <template x-for="url in previewUrls" :key="url">
            <div class="relative aspect-[4/3] overflow-hidden rounded-xl border border-sky-300 bg-sky-50 dark:border-sky-500/35 dark:bg-sky-950/30">
                <img :src="url" alt="Foto optimizada" class="h-full w-full object-contain">
            </div>
        </template>

        @if(count($existingPhotos) === 0 && count($newPhotos) === 0)
            <div x-show="previewUrls.length === 0" class="col-span-full flex min-h-16 items-center justify-center rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-4 text-center text-[11px] text-zinc-500 dark:border-zinc-800 dark:bg-zinc-950/50">
                Sin fotos. Usa “Agregar fotos” si necesitas evidencia visual.
            </div>
        @endif
    </div>

    <div x-cloak x-show="busy" class="rounded-lg bg-zinc-950 p-2.5" role="status" aria-live="polite">
        <div class="flex items-center justify-between text-[10px] font-semibold text-sky-300">
            <span x-text="processing ? 'Comprimiendo imágenes...' : 'Subiendo imágenes...'"></span>
            <span x-show="uploading" x-text="`${progress}%`"></span>
        </div>
        <span class="mt-1.5 block h-1 overflow-hidden rounded-full bg-zinc-700">
            <span class="block h-full bg-sky-400 transition-all" :style="`width: ${processing ? 18 : progress}%`"></span>
        </span>
    </div>

    <span x-cloak x-show="clientError" x-text="clientError" class="block text-xs text-red-500" role="alert"></span>
    @error('fotos') <span class="block text-xs text-red-500">{{ $message }}</span> @enderror
    @error('fotos.*') <span class="block text-xs text-red-500">{{ $message }}</span> @enderror
</section>
