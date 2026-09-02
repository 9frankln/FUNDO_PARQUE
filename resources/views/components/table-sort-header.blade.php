@props([
    'field',
    'sortBy' => '',
    'sortDir' => 'asc',
    'align' => 'left',
])

@php
    $isActive = $sortBy === $field;
    $alignClass = match($align) {
        'right' => 'justify-end',
        'center' => 'justify-center',
        default => 'justify-start',
    };
@endphp

<button
    type="button"
    {{ $attributes->merge(['class' => 'group inline-flex items-center gap-1.5 transition select-none hover:text-zinc-900 dark:hover:text-white ' . $alignClass]) }}
>
    <span>{{ $slot }}</span>
    <span class="inline-flex items-center">
        @if($isActive)
            @if($sortDir === 'asc')
                <svg class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
                </svg>
            @else
                <svg class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            @endif
        @else
            <svg class="h-3 w-3 opacity-0 text-zinc-400 transition-opacity duration-150 group-hover:opacity-60" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
            </svg>
        @endif
    </span>
</button>
