<?php

namespace App\Traits;

/**
 * Filtros de periodo (periodo/anio/mes/fechaDesde/fechaHasta) con resets cruzados.
 *
 * Antes duplicado en Animal/Index, Leche/Index, Engorde/Index y Queso/Index.
 * Los hooks `updated*` de Livewire se resuelven por nombre de método/propiedad,
 * por lo que basta con incluir este trait y declarar las props con queryString.
 */
trait HasPeriodoFilters
{
    public string $periodo = '';

    public string $anio = '';

    public string $mes = '';

    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public function updatedPeriodo($value): void
    {
        if ($value !== '') {
            $this->reset(['anio', 'mes', 'fechaDesde', 'fechaHasta']);
        }

        $this->resetPage();
    }

    public function updatedAnio($value): void
    {
        $this->reset(['periodo', 'fechaDesde', 'fechaHasta']);

        if ($value === '') {
            $this->mes = '';
        }

        $this->resetPage();
    }

    public function updatedMes($value): void
    {
        if ($value !== '' && $this->anio === '') {
            $this->anio = (string) now()->year;
        }

        $this->reset(['periodo', 'fechaDesde', 'fechaHasta']);
        $this->resetPage();
    }

    public function updatedFechaDesde($value): void
    {
        if ($value !== '') {
            $this->reset(['periodo', 'anio', 'mes']);
        }

        $this->resetPage();
    }

    public function updatedFechaHasta($value): void
    {
        if ($value !== '') {
            $this->reset(['periodo', 'anio', 'mes']);
        }

        $this->resetPage();
    }
}
