@props(['pdf' => false])

@php
    $logo = $pdf ? $branding->logoDataUri() : $branding->logoUrl();
    $frame = \App\Support\ImageFrame::normalize($branding->logoFrame());
    $frameStyle = 'object-position: '.$frame['x'].'% '.$frame['y'].'%; transform: scale('.$frame['zoom'].'); transform-origin: '.$frame['x'].'% '.$frame['y'].'%;';
@endphp

@if($pdf)
    @if($logo)
        <img src="{{ $logo }}" alt="Logo de {{ $branding->name() }}" {{ $attributes }}>
    @else
        <svg viewBox="0 0 24 24" role="img" aria-label="Logo de {{ $branding->name() }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
            <path d="M7 19h10" stroke-width="2.2" />
            <path d="M12 19V9" stroke-width="2.2" />
            <path d="M12 9C12 9 7 8 5 4C9 4 12 7 12 9Z" fill="currentColor" opacity="0.9" />
            <path d="M12 10C12 10 17 9 19 5C15 5 12 8 12 10Z" fill="currentColor" opacity="0.9" />
        </svg>
    @endif
@else
    <div {{ $attributes->class(['relative inline-flex items-center justify-center overflow-hidden shrink-0']) }}>
        @if($logo)
            <img data-brand-logo-image
                 src="{{ $logo }}"
                 alt="Logo de {{ $branding->name() }}"
                 class="h-full w-full object-cover transition-none pointer-events-none select-none"
                 style="{{ $frameStyle }}">
        @else
            <svg data-brand-logo-fallback viewBox="0 0 24 24" role="img" aria-label="Logo de {{ $branding->name() }}" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" class="h-full w-full p-1.5 text-emerald-500 dark:text-emerald-400 shrink-0">
                <path d="M7 19h10" stroke-width="2.2" />
                <path d="M12 19V9" stroke-width="2.2" />
                <path d="M12 9C12 9 7 8 5 4C9 4 12 7 12 9Z" fill="currentColor" opacity="0.9" />
                <path d="M12 10C12 10 17 9 19 5C15 5 12 8 12 10Z" fill="currentColor" opacity="0.9" />
            </svg>
        @endif
    </div>
@endif
