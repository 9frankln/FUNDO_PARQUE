@props([
    'model' => 'numeroLote',
    'year' => null,
    'number' => null,
    'tone' => 'amber',
    'id' => null,
    'label' => 'Código de lote',
    'required' => true,
    'errorField' => null,
])

@php
    $yearValue = (int) ($year ?: now()->year);
    $prefix = sprintf('INS%02d-', $yearValue % 100);
    $inputId = $id ?: 'insumo-lot-code-' . substr(md5($model), 0, 8);
    $errorKey = $errorField ?: $model;
    $numberFormatted = $number !== null && $number !== '' ? str_pad((string) (int) $number, 3, '0', STR_PAD_LEFT) : '001';
    $focusClasses = match ($tone) {
        'teal' => 'focus-within:border-teal-500 focus-within:ring-2 focus-within:ring-teal-500/20',
        'cyan' => 'focus-within:border-cyan-500 focus-within:ring-2 focus-within:ring-cyan-500/20',
        'emerald' => 'focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20',
        default => 'focus-within:border-amber-500 focus-within:ring-2 focus-within:ring-amber-500/20',
    };
    $accentColor = match ($tone) {
        'teal' => 'text-teal-600 dark:text-teal-400',
        'cyan' => 'text-cyan-600 dark:text-cyan-400',
        'emerald' => 'text-emerald-600 dark:text-emerald-400',
        default => 'text-amber-600 dark:text-amber-400',
    };
@endphp

<div
    x-data="{
        numberVal: @entangle($model).live,
        formatInput() {
            const digits = (this.numberVal || '').toString().replace(/\D+/g, '').slice(0, 3);
            if (!digits) {
                this.numberVal = '';
                return;
            }
            this.numberVal = digits.padStart(3, '0');
        }
    }"
>
    @if($label)
        <label for="{{ $inputId }}" class="mb-2 flex h-5 items-center text-xs font-bold text-zinc-700 dark:text-zinc-300">
            <span>{{ $label }}</span>
            @if($required)
                <span class="ml-1 font-bold {{ $accentColor }}">*</span>
            @endif
        </label>
    @endif

    <div
        class="flex h-11 w-full overflow-hidden rounded-xl border border-zinc-300 bg-white transition dark:border-zinc-700 dark:bg-zinc-950 {{ $focusClasses }}"
    >
        <span class="flex select-none items-center border-r border-zinc-200 bg-zinc-100 px-3 font-mono text-sm font-black text-zinc-700 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-200">
            {{ $prefix }}
        </span>

        <input
            id="{{ $inputId }}"
            type="text"
            inputmode="numeric"
            pattern="[0-9]{3}"
            maxlength="3"
            x-model="numberVal"
            x-on:blur="formatInput()"
            placeholder="{{ $numberFormatted }}"
            autocomplete="off"
            class="h-full w-full bg-transparent px-3.5 font-mono text-sm font-bold text-zinc-900 outline-none placeholder:text-zinc-400 dark:text-white dark:placeholder:text-zinc-600"
        />
    </div>

    <div class="mt-1 flex items-center justify-between text-[10px] text-zinc-500 dark:text-zinc-400">
        <span>Prefijo y año fijos. Editable: últimos 3 dígitos.</span>
        <span class="font-mono font-semibold">{{ $prefix }}<span x-text="(numberVal || '{{ $numberFormatted }}').toString().padStart(3, '0')"></span></span>
    </div>

    @error($errorKey)
        <p class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
