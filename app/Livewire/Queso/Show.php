<?php

namespace App\Livewire\Queso;

use App\Models\ProduccionQueso;
use App\Traits\AuthorizesPermissions;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesPermissions;

    #[Locked]
    public int $prodId;

    public function mount($id): void
    {
        $this->prodId = (int) $id;
    }

    public function render()
    {
        $produccion = ProduccionQueso::with('presentaciones')
            ->where('fundo_id', session('fundo_id'))
            ->findOrFail($this->prodId);

        return view('livewire.queso.show', compact('produccion'))
            ->layout('layouts.app');
    }
}
