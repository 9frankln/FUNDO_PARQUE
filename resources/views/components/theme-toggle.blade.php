@props([
    'btnClass' => 'agro-navbar__control hidden h-10 w-10 items-center justify-center rounded-xl transition sm:flex',
    'action'   => 'darkMode = !darkMode',
    'iconSize' => 'h-5 w-5',
])
{{--
    Componente unificado de toggle de tema claro/oscuro.
    Fuente de verdad única para los íconos — editar aquí afecta TODA la app.

    Props:
      - btnClass  : clases del <button> wrapper (diferente en navbar vs landing)
      - action    : expresión Alpine para el @click
      - iconSize  : tamaño del SVG (h-5 w-5 por defecto)
--}}
<button
    type="button"
    @click="{{ $action }}"
    aria-label="Alternar tema claro / oscuro"
    class="{{ $btnClass }}"
>
    {{-- MODO CLARO → muestra luna con estrella (click cambia a NOCHE) --}}
    <svg x-show="!darkMode"
         class="{{ $iconSize }}"
         fill="none"
         stroke="currentColor"
         viewBox="0 0 24 24"
         stroke-linecap="round"
         stroke-linejoin="round"
         aria-hidden="true">
        <path stroke-width="1.8" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
        <path stroke-width="1.6" d="M19.5 4.5 20 6l1.5.5L20 7l-.5 1.5L19 7l-1.5-.5L19 6l.5-1.5Z"/>
    </svg>

    {{-- MODO NOCHE → muestra sol (click cambia a CLARO) --}}
    <svg x-cloak
         x-show="darkMode"
         class="{{ $iconSize }}"
         fill="none"
         stroke="currentColor"
         viewBox="0 0 24 24"
         stroke-linecap="round"
         stroke-linejoin="round"
         aria-hidden="true">
        <path stroke-width="1.8"
              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.4-6.4-.7.7M6.3 17.7l-.7.7m0-12.8.7.7m11.4 11.4.7.7M16 12a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z"/>
    </svg>
</button>
