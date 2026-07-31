<nav class="flex flex-1 justify-end">
    @auth
        <a
            href="{{ url('/dashboard') }}"
            class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-700/15 transition hover:-translate-y-0.5 hover:bg-emerald-500"
        >
            Ir al panel
        </a>
    @else
        <button
            type="button"
            @click="$dispatch('open-login')"
            class="rounded-xl border border-emerald-600/20 bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-700/15 transition hover:-translate-y-0.5 hover:bg-emerald-500"
        >
            Iniciar sesión
        </button>
    @endauth
</nav>
