<?php

namespace App\Livewire\Leche;

use App\Models\Ordeno;
use App\Models\OrdenoFotoDiaria;
use App\Support\ImageFrame;
use Livewire\Component;

class Show extends Component
{
    public $ordenoId;

    public $ordeno;

    public $fotoRuta;

    public array $fotoEncuadre = ImageFrame::DEFAULT;

    public function mount($id)
    {
        $this->ordenoId = $id;
        $this->ordeno = Ordeno::with(['detalles.animal.raza'])->findOrFail($id);
        $dailyPhoto = OrdenoFotoDiaria::where('fundo_id', session('fundo_id'))
            ->whereDate('fecha', $this->ordeno->fecha)
            ->first(['foto_ruta', 'foto_encuadre']);
        $this->fotoRuta = $dailyPhoto?->foto_ruta;
        $this->fotoEncuadre = ImageFrame::normalize($dailyPhoto?->foto_encuadre);
    }

    public function render()
    {
        return view('livewire.leche.show')
            ->layout('layouts.app');
    }
}
