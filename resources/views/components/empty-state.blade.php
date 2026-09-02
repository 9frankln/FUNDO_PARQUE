@props(['icon' => 'clipboard', 'title' => 'Sin registros', 'description' => '', 'action' => null, 'actionText' => null, 'actionRoute' => null])

<div class="flex flex-col items-center justify-center py-12 px-6 text-center">
    {{-- Icon --}}
    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
        @if($icon === 'clipboard')
        <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 3h1a2.25 2.25 0 012.25 2.25m-13.5 0A48.394 48.394 0 0112 2.25c2.243 0 4.435.164 6.561.477M3.75 21.75V6.108c0-1.135.845-2.098 1.976-2.192a48.424 48.424 0 011.123-.08" />
        </svg>
        @elseif($icon === 'beaker')
        <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
        </svg>
        @elseif($icon === 'chart')
        <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
        </svg>
        @elseif($icon === 'search')
        <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        @elseif($icon === 'syringe')
        <svg class="h-8 w-8 text-zinc-400 dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        @endif
    </div>
    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $title }}</h3>
    @if($description)
        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
    @if($actionRoute)
        <a href="{{ $actionRoute }}" wire:navigate class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-zinc-300 bg-white px-4 py-2 text-xs font-semibold text-zinc-700 shadow-sm transition hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700">
            {{ $actionText ?? 'Crear nuevo' }}
        </a>
    @endif
    {{ $slot }}
</div>
