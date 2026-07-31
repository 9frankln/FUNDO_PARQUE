@php
    $fundos = auth()->user()->fundos()->where('activo', true)->get();
    $activeFundo = auth()->user()->fundoActivo();
@endphp

@if($activeFundo)
<div x-data="{ open: false }" class="relative w-full">
    <button @click="open = !open" 
            class="flex items-center justify-between w-full px-4 py-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800/50 hover:bg-zinc-200 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-700/50 rounded-xl transition duration-300 group outline-none">
        <div class="flex items-center gap-2">
            <span class="text-emerald-500 dark:text-emerald-400">🏡</span>
            <span class="truncate max-w-[150px] text-zinc-800 dark:text-zinc-100">{{ $activeFundo->nombre }}</span>
        </div>
        <svg class="w-4 h-4 text-zinc-500 group-hover:text-zinc-700 dark:group-hover:text-zinc-300 transition transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>
    <div x-show="open" @click.away="open = false" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 w-full mt-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xl max-h-60 overflow-y-auto" style="display: none;">
        <div class="p-1">
            @foreach($fundos as $f)
                @if($f->id !== $activeFundo->id)
                    <form method="POST" action="{{ route('select-fundo', $f->id) }}">
                        @csrf
                        <button type="submit"
                           class="flex w-full items-center gap-2 px-3 py-2.5 text-sm text-zinc-600 dark:text-zinc-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-500/10 rounded-lg transition duration-200">
                        <span>🏡</span>
                        <span class="truncate">{{ $f->nombre }}</span>
                        </button>
                    </form>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif
