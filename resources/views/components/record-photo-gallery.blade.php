@props(['photos'])

<div x-data="{
        photo: null,
        init() {
            this.$watch('photo', value => document.body.classList.toggle('overflow-hidden', Boolean(value)));
        },
        destroy() {
            document.body.classList.remove('overflow-hidden');
        },
    }" class="space-y-2">
    @if($photos->isNotEmpty())
        <div class="grid grid-cols-3 gap-2">
            @foreach($photos as $index => $photo)
                @php
                    $photoUrl = route('record-photo.show', $photo);
                    $frame = \App\Support\ImageFrame::normalize($photo->encuadre);
                @endphp
                <button type="button" @click="photo = @js($photoUrl)"
                        class="group relative aspect-[4/3] overflow-hidden rounded-lg border border-emerald-950/10 bg-emerald-50 dark:border-emerald-200/10 dark:bg-emerald-950/50">
                    <img src="{{ $photoUrl }}" alt="Foto {{ $index + 1 }} del registro" loading="lazy" class="h-full w-full object-cover transition" style="object-position: {{ $frame['x'] }}% {{ $frame['y'] }}%; transform: scale({{ $frame['zoom'] }}); transform-origin: {{ $frame['x'] }}% {{ $frame['y'] }}%;">
                    <span class="absolute bottom-1 right-1 rounded bg-zinc-950/75 px-1.5 py-0.5 text-[9px] font-bold text-white">{{ $index + 1 }}/{{ $photos->count() }}</span>
                </button>
            @endforeach
        </div>

        <template x-teleport="body">
            <div x-cloak x-show="photo" x-transition.opacity @keydown.escape.window="photo = null" @click.self="photo = null"
                 class="agro-dialog-overlay !z-[120] !bg-zinc-950/90" role="dialog" aria-modal="true" aria-label="Foto ampliada">
                <button type="button" @click="photo = null" class="absolute right-3 top-3 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-2xl text-white hover:bg-white/20 sm:right-4 sm:top-4" aria-label="Cerrar">&times;</button>
                <img :src="photo" alt="Foto ampliada del registro" class="max-h-[calc(100dvh-2rem)] max-w-[calc(100vw-2rem)] rounded-xl object-contain shadow-2xl">
            </div>
        </template>
    @else
        <div class="rounded-lg border border-dashed border-emerald-950/15 bg-emerald-50/50 px-3 py-2 text-center text-[10px] text-emerald-900/50 dark:border-emerald-200/15 dark:bg-emerald-950/25 dark:text-emerald-100/50">
            Registro sin fotos.
        </div>
    @endif
</div>

