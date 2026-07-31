<?php

namespace App\Livewire\Finanzas;

use App\Models\AsignacionFamiliar;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class AsignacionForm extends Component
{
    use AuthorizesPermissions, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $asigId = null;

    #[Locked]
    public $isEdit = false;

    public $beneficiario = '';

    public $monto = '';

    public $moneda = 'PEN';

    public $fecha = '';

    public $proposito = 'estudio';

    public $descripcion = '';

    public $foto;

    public array $fotoEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $fotoEncuadreChanged = false;

    #[Locked]
    public $fotoRuta = null;

    public function mount($id = null)
    {
        $this->fecha = now()->format('Y-m-d');

        if ($id) {
            $asig = AsignacionFamiliar::where('fundo_id', session('fundo_id'))->findOrFail($id);
            $this->asigId = $asig->getKey();
            $this->isEdit = true;

            $this->beneficiario = $asig->beneficiario;
            $this->monto = $asig->monto;
            $this->moneda = $asig->moneda;
            $this->fecha = $asig->fecha->format('Y-m-d');
            $this->proposito = $asig->proposito;
            $this->descripcion = $asig->descripcion;
            $this->fotoRuta = $asig->foto_ruta;
            $this->fotoEncuadre = ImageFrame::normalize($asig->foto_encuadre);
        }
    }

    public function updatedFoto(): void
    {
        if ($this->foto) {
            $this->fotoEncuadre = ImageFrame::DEFAULT;
        }
    }

    public function updatedFotoEncuadre(): void
    {
        $this->fotoEncuadreChanged = true;
    }

    public function cancelPhotoChange(): void
    {
        $this->reset('foto');
        $this->fotoEncuadre = $this->asigId
            ? ImageFrame::normalize(AsignacionFamiliar::where('fundo_id', session('fundo_id'))->find($this->asigId)?->foto_encuadre)
            : ImageFrame::DEFAULT;
        $this->fotoEncuadreChanged = false;
        $this->resetValidation('foto');
    }

    public function save()
    {
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $existingAsignacion = $this->asigId
            ? AsignacionFamiliar::where('fundo_id', $fundoId)->findOrFail($this->asigId)
            : null;
        $wasEdit = $existingAsignacion !== null;

        $this->authorizePermission('finanzas', $wasEdit ? 'actualizar' : 'crear');

        $rules = [
            'beneficiario' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0.01',
            'moneda' => 'required|string|max:3',
            'fecha' => 'required|date',
            'proposito' => 'required|in:estudio,salud,alimentacion,vivienda,transporte,ropa,gastos_personales,emergencia,otros',
            'descripcion' => 'nullable|string|max:255',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:25600|dimensions:max_width=10000,max_height=10000',
            ...ImageFrame::rules('fotoEncuadre'),
        ];

        $this->validate($rules, [
            'foto.image' => 'Selecciona una imagen válida.',
            'foto.mimes' => 'Usa una imagen JPG, PNG o WebP.',
            'foto.max' => 'La imagen original no puede superar 25 MB.',
            'foto.dimensions' => 'La imagen no puede superar 10,000 píxeles por lado.',
        ]);

        $previousPhoto = $existingAsignacion?->foto_ruta;
        $newPhoto = null;
        $fotoEncuadre = ImageFrame::normalize($this->fotoEncuadre);
        $data = [
            'fundo_id' => $fundoId,
            'beneficiario' => $this->beneficiario,
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'fecha' => $this->fecha,
            'proposito' => $this->proposito,
            'descripcion' => $this->descripcion ?: null,
        ];
        if ($this->foto || $this->fotoEncuadreChanged) {
            $data['foto_encuadre'] = ($this->foto || $previousPhoto) ? $fotoEncuadre : null;
        } elseif (! $previousPhoto) {
            $data['foto_encuadre'] = null;
        }

        try {
            if ($this->foto) {
                $newPhoto = ImageOptimizer::store(
                    $this->foto,
                    'finanzas/asignaciones',
                    'foto',
                    1080,
                    600 * 1024,
                    'local'
                );
                $data['foto_ruta'] = $newPhoto;
            }

            $asignacion = AsignacionFamiliar::updateOrCreate(
                ['id' => $existingAsignacion?->getKey()],
                $data
            );
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('local')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto && $previousPhoto && $previousPhoto !== $newPhoto) {
            Storage::disk('local')->delete($previousPhoto);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => $wasEdit ? 'Asignación actualizada' : 'Asignación registrada',
            'text' => $wasEdit
                ? 'La asignación familiar y su foto se actualizaron correctamente.'
                : 'La asignación familiar fue registrada correctamente.',
        ]);
        session()->flash('success', $wasEdit ? 'Asignación familiar actualizada correctamente.' : 'Asignación familiar registrada correctamente.');
        $this->publishRecentRecord('finanzas.asignaciones', $asignacion);

        return redirect()->route('finanzas.index', ['tab' => 'asignaciones']);
    }

    public function render()
    {
        return view('livewire.finanzas.asignacion-form')
            ->layout('layouts.app');
    }
}
