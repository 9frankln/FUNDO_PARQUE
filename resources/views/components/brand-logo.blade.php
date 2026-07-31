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
        <svg viewBox="0 0 64 64" role="img" aria-label="Logo de {{ $branding->name() }}" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
            <path d="M32 57V27" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
            <path d="M32 35C18 35 10 27 9 15c13 0 22 6 23 20Zm0 9c14 0 22-8 23-20-13 0-22 6-23 20Z" fill="currentColor" opacity=".85"/>
            <path d="M17 57h30" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
        </svg>
    @endif
@else
    <img data-brand-logo-image src="{{ $logo ?? '' }}" alt="Logo de {{ $branding->name() }}" {{ $attributes->class(['hidden' => ! $logo])->merge(['style' => $frameStyle]) }}>
    <svg data-brand-logo-fallback viewBox="0 0 64 64" role="img" aria-label="Logo de {{ $branding->name() }}" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class(['hidden' => (bool) $logo]) }}>
        <path d="M32 57V27" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
        <path d="M32 35C18 35 10 27 9 15c13 0 22 6 23 20Zm0 9c14 0 22-8 23-20-13 0-22 6-23 20Z" fill="currentColor" opacity=".85"/>
        <path d="M17 57h30" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="5"/>
    </svg>
@endif
