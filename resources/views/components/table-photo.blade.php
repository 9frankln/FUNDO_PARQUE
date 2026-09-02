@props([
    'path' => null,
    'url' => null,
    'alt' => 'Fotografía del registro',
    'size' => 'md',
    'frame' => null,
])

@php
    $boxClass = $size === 'sm' ? 'h-9 w-9 rounded-lg' : 'h-12 w-12 rounded-xl';
    $iconClass = $size === 'sm' ? 'h-4 w-4' : 'h-5 w-5';
    $source = $url ?: ($path ? '/storage/'.ltrim($path, '/') : null);
    $pixels = $size === 'sm' ? 36 : 48;
    $imageFrame = \App\Support\ImageFrame::normalize($frame);
@endphp

@if($source)
    <a href="{{ $source }}" target="_blank" rel="noopener noreferrer"
       title="Abrir imagen completa"
       class="group inline-flex overflow-hidden rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-zinc-900">
         <img src="{{ $source }}"
              alt="{{ $alt }}"
              width="{{ $pixels }}"
              height="{{ $pixels }}"
              loading="lazy"
              decoding="async"
              onerror="this.onerror=null; this.style.display='none'; this.parentElement.insertAdjacentHTML('afterend', '<span title=\'Sin foto\' class=\'{{ $boxClass }} inline-flex items-center justify-center border border-dashed border-zinc-300 bg-zinc-50 text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-600\'><svg class=\'{{ $iconClass }}\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.7\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.379a2.25 2.25 0 0 0 1.59-.659l.622-.622A2.25 2.25 0 0 1 10.432 4h3.136a2.25 2.25 0 0 1 1.591.659l.622.622A2.25 2.25 0 0 0 17.371 6h1.379A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15.75 12.375a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z\'/><path stroke-linecap=\'round\' d=\'m5 19 14-14\'/></svg></span>'); this.parentElement.remove();"
              class="{{ $boxClass }} border border-emerald-900/10 bg-emerald-50 object-cover shadow-sm transition group-hover:border-emerald-500/40 dark:border-emerald-300/15 dark:bg-emerald-950/30"
              style="object-position: {{ $imageFrame['x'] }}% {{ $imageFrame['y'] }}%; transform: scale({{ $imageFrame['zoom'] }}); transform-origin: {{ $imageFrame['x'] }}% {{ $imageFrame['y'] }}%;">
    </a>
@else
    <span title="Sin foto" class="{{ $boxClass }} inline-flex items-center justify-center border border-dashed border-zinc-300 bg-zinc-50 text-zinc-400 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-600">
        <svg class="{{ $iconClass }}" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V8.25A2.25 2.25 0 0 1 5.25 6h1.379a2.25 2.25 0 0 0 1.59-.659l.622-.622A2.25 2.25 0 0 1 10.432 4h3.136a2.25 2.25 0 0 1 1.591.659l.622.622A2.25 2.25 0 0 0 17.371 6h1.379A2.25 2.25 0 0 1 21 8.25v8.25a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 16.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 12.375a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            <path stroke-linecap="round" d="m5 19 14-14" />
        </svg>
        <span class="sr-only">Sin foto</span>
    </span>
@endif

