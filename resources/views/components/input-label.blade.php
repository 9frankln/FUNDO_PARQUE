@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-zinc-800 dark:text-zinc-200']) }}>
    {{ $value ?? $slot }}
</label>
