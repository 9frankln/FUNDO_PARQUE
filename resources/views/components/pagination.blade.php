@php
    $pageName = $paginator->getPageName();
@endphp

@if($paginator->hasPages())
    <nav class="agro-pagination" role="navigation" aria-label="Navegación de páginas">
        <div class="flex items-center justify-between gap-3 sm:hidden">
            <button type="button" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled"
                    @disabled($paginator->onFirstPage())
                    class="agro-pagination__mobile-button">
                Anterior
            </button>
            <span class="agro-pagination__mobile-status">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            <button type="button" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled"
                    @disabled(! $paginator->hasMorePages())
                    class="agro-pagination__mobile-button">
                Siguiente
            </button>
        </div>

        <div class="hidden items-center justify-between gap-6 sm:flex">
            <p class="agro-pagination__summary">
                Mostrando <strong>{{ $paginator->firstItem() }}</strong> a <strong>{{ $paginator->lastItem() }}</strong>
                de <strong>{{ $paginator->total() }}</strong> resultados
            </p>

            <div class="inline-flex items-center gap-1" aria-label="Páginas disponibles">
                <button type="button" wire:click="previousPage('{{ $pageName }}')" wire:loading.attr="disabled"
                        @disabled($paginator->onFirstPage())
                        class="agro-pagination__button" aria-label="Página anterior">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6" /></svg>
                </button>

                @foreach($elements as $element)
                    @if(is_string($element))
                        <span class="agro-pagination__ellipsis">{{ $element }}</span>
                    @endif

                    @if(is_array($element))
                        @foreach($element as $page => $url)
                            @if($page == $paginator->currentPage())
                                <span class="agro-pagination__button agro-pagination__button--active" aria-current="page">{{ $page }}</span>
                            @else
                                <button type="button" wire:key="paginator-{{ $pageName }}-{{ $page }}"
                                        wire:click="gotoPage({{ $page }}, '{{ $pageName }}')"
                                        class="agro-pagination__button" aria-label="Ir a la página {{ $page }}">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                <button type="button" wire:click="nextPage('{{ $pageName }}')" wire:loading.attr="disabled"
                        @disabled(! $paginator->hasMorePages())
                        class="agro-pagination__button" aria-label="Página siguiente">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6" /></svg>
                </button>
            </div>
        </div>
    </nav>
@endif
