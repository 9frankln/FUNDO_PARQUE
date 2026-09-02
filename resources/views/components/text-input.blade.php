@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 focus:border-emerald-500 dark:focus:border-emerald-500 focus:ring-emerald-500 dark:focus:ring-emerald-500 rounded-xl shadow-sm']) }}>
