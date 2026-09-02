<?php

namespace App\Traits;

/**
 * Provides reusable sorting logic for Livewire index components.
 *
 * Usage:
 *   1. Use this trait in your component.
 *   2. Override $sortBy and $sortDir defaults if needed.
 *   3. Call toggleSort('column_name') from wire:click.
 *   4. Apply sorting: $query->orderBy($this->sortBy, $this->sortDir)
 */
trait HasSorting
{
    public string $sortBy = 'created_at';

    public string $sortDir = 'desc';

    public function toggleSort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Apply the current sort to a query builder.
     */
    protected function applySorting($query)
    {
        return $query->orderBy($this->sortBy, $this->sortDir);
    }
}
