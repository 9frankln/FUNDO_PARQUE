@props([
    'model',
    'year',
    'number' => '',
    'errorField' => null,
    'tone' => 'amber',
    'id' => 'medication-lot-code',
    'label' => 'Código de lote',
    'required' => true,
])

@php
    $errorField ??= $model;
    $prefix = \App\Support\MedicamentoLotCodeAllocator::prefix((int) $year);
    $digits = preg_replace('/\D/', '', (string) $number);
    $preview = $digits !== '' ? $prefix.str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT) : $prefix.'001';
    $palette = match ($tone) {
        'cyan' => ['focus-within:border-cyan-500/60 focus-within:ring-cyan-500/15', 'text-cyan-700 dark:text-cyan-300', 'text-cyan-600 dark:text-cyan-400'],
        'emerald' => ['focus-within:border-emerald-500/60 focus-within:ring-emerald-500/15', 'text-emerald-700 dark:text-emerald-300', 'text-emerald-600 dark:text-emerald-400'],
        default => ['focus-within:border-amber-500/60 focus-within:ring-amber-500/15', 'text-amber-700 dark:text-amber-300', 'text-amber-600 dark:text-amber-400'],
    };
@endphp

<div>
    @if($label)
        <label class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300" for="{{ $id }}">
            <span>{{ $label }}</span>
            @if($required)
                <span class="ml-1 font-bold {{ $palette[2] }}">*</span>
            @endif
        </label>
    @endif
    <div class="grid grid-cols-[minmax(0,1fr)_6rem] overflow-hidden rounded-xl border border-zinc-300 bg-white transition focus-within:ring-2 dark:border-zinc-700 dark:bg-zinc-950 {{ $palette[0] }}">
        <div class="flex h-11 items-center border-r border-zinc-300 px-4 text-sm font-black tracking-wider dark:border-zinc-700 {{ $palette[1] }}">
            {{ $prefix }}
        </div>
        <input id="{{ $id }}" type="text" inputmode="numeric" pattern="[0-9]{3}" maxlength="3"
               wire:model.blur="{{ $model }}"
               x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '').slice(0, 3)"
               class="h-11 border-0 bg-transparent px-3 text-center font-mono text-sm font-black tracking-widest text-zinc-900 outline-none focus:ring-0 dark:text-zinc-100"
               placeholder="001">
    </div>
    <div class="mt-1.5 flex flex-wrap items-center justify-between gap-2 text-[11px]">
        <span class="text-zinc-500">Prefijo y año fijos. Editable: últimos 3 dígitos.</span>
        <span class="font-bold {{ $palette[1] }}">{{ $preview }}</span>
    </div>
    @if($errors->has($errorField))
        <p class="mt-1 text-xs font-semibold text-rose-600 dark:text-rose-400">{{ $errors->first($errorField) }}</p>
    @endif
</div>
