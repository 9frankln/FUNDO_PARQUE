import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: [
        'border-emerald-200', 'bg-emerald-50/70', 'dark:border-emerald-400/20', 'dark:bg-emerald-400/[.06]',
        'border-amber-200', 'bg-amber-50/70', 'dark:border-amber-400/20', 'dark:bg-amber-400/[.06]',
        'border-sky-200', 'bg-sky-50/70', 'dark:border-sky-400/20', 'dark:bg-sky-400/[.06]',
        'border-yellow-200', 'bg-yellow-50/70', 'dark:border-yellow-400/20', 'dark:bg-yellow-400/[.06]',
        'border-teal-200', 'bg-teal-50/70', 'dark:border-teal-400/20', 'dark:bg-teal-400/[.06]',
        'border-rose-200', 'bg-rose-50/70', 'dark:border-rose-400/20', 'dark:bg-rose-400/[.06]',
        'border-violet-200', 'bg-violet-50/70', 'dark:border-violet-400/20', 'dark:bg-violet-400/[.06]',
        'border-cyan-200', 'bg-cyan-50/70', 'dark:border-cyan-400/20', 'dark:bg-cyan-400/[.06]',
        'border-indigo-200', 'bg-indigo-50/70', 'dark:border-indigo-400/20', 'dark:bg-indigo-400/[.06]',
        'border-pink-200', 'bg-pink-50/70', 'dark:border-pink-400/20', 'dark:bg-pink-400/[.06]',
        'bg-emerald-100', 'text-emerald-700', 'dark:bg-emerald-400/15', 'dark:text-emerald-300',
        'bg-sky-100', 'text-sky-700', 'dark:bg-sky-400/15', 'dark:text-sky-300',
        'bg-violet-100', 'text-violet-700', 'dark:bg-violet-400/15', 'dark:text-violet-300',
        'bg-amber-100', 'text-amber-700', 'dark:bg-amber-400/15', 'dark:text-amber-300',
    ],

    theme: {
        extend: {
            spacing: {
                '4.5': '1.125rem',
            },
            colors: {
                emerald: Object.fromEntries(
                    [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950]
                        .map((shade) => [shade, `rgb(var(--brand-${shade}) / <alpha-value>)`]),
                ),
            },
            fontFamily: {
                sans: ['Outfit', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
