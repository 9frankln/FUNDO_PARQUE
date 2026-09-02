<?php

namespace App\Livewire\Engorde;

use App\Models\LoteEngorde;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\LoteCodeAllocator;
use App\Traits\AuthorizesPermissions;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class LoteForm extends Component
{
    use AuthorizesPermissions, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $loteId = null;

    #[Locked]
    public $isEdit = false;

    #[Locked]
    public $codigo = '';

    public $nombre = '';

    public $fechaInicio = '';

    public $fechaFin = '';

    public $estado = 'activo';

    public $observaciones = '';

    public $foto = null;

    public array $fotoEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $fotoEncuadreChanged = false;

    #[Locked]
    public $existingFoto = null;

    public bool $removeFoto = false;

    public bool $photoConfirmed = false;

    protected $listeners = [
        'confirmarCambioFotoLote' => 'confirmPhotoChange',
        'cancelarCambioFotoLote' => 'cancelPhotoChange',
        'confirmarEliminacionFotoLote' => 'confirmPhotoRemoval',
    ];

    public function mount($id = null): void
    {
        $this->fechaInicio = now()->format('Y-m-d');

        if ($id) {
            $this->loteId = $id;
            $this->isEdit = true;
            $lote = LoteEngorde::where('fundo_id', session('fundo_id'))->findOrFail($id);

            $this->codigo = $lote->codigo;
            $this->nombre = $lote->nombre;
            $this->fechaInicio = $lote->fecha_inicio->format('Y-m-d');
            $this->fechaFin = $lote->fecha_fin?->format('Y-m-d') ?: '';
            $this->estado = $lote->estado;
            $this->observaciones = $lote->observaciones;
            $this->existingFoto = $lote->foto_ruta;
            $this->fotoEncuadre = ImageFrame::normalize($lote->foto_encuadre);
        } else {
            $year = now()->year;
            $number = app(LoteCodeAllocator::class)->preview((int) session('fundo_id'), $year);
            $this->codigo = LoteCodeAllocator::format($year, $number);
        }
    }

    public function updatedFoto(): void
    {
        $this->photoConfirmed = false;
        $this->removeFoto = false;

        if (! $this->foto) {
            return;
        }

        $this->fotoEncuadre = ImageFrame::DEFAULT;
        $this->validateOnly('foto', $this->photoRules(), $this->photoMessages());
        $this->photoConfirmed = true;
    }

    public function updatedFotoEncuadre(): void
    {
        $this->fotoEncuadreChanged = true;
    }

    public function confirmPhotoChange(): void
    {
        if (! $this->foto) {
            return;
        }

        $this->photoConfirmed = true;
        $this->removeFoto = false;
        $this->resetValidation('foto');
    }

    public function cancelPhotoChange(): void
    {
        $this->reset('foto');
        $this->photoConfirmed = false;
        $this->fotoEncuadre = $this->loteId
            ? ImageFrame::normalize(LoteEngorde::where('fundo_id', session('fundo_id'))->find($this->loteId)?->foto_encuadre)
            : ImageFrame::DEFAULT;
        $this->fotoEncuadreChanged = false;
        $this->resetValidation('foto');
    }

    public function requestPhotoRemoval(): void
    {
        if (! $this->existingFoto) {
            return;
        }

        $this->dispatch('swal:confirm', [
            'title' => '¿Preparar eliminación?',
            'text' => 'La foto seguirá protegida hasta guardar. Podrás deshacer esta acción.',
            'icon' => 'warning',
            'confirmButtonText' => 'Preparar eliminación',
            'cancelButtonText' => 'Mantener foto',
            'event' => 'confirmarEliminacionFotoLote',
        ]);
    }

    public function confirmPhotoRemoval(): void
    {
        $this->reset('foto');
        $this->photoConfirmed = false;
        $this->removeFoto = true;
        $this->resetValidation('foto');
    }

    public function cancelPhotoRemoval(): void
    {
        $this->removeFoto = false;
    }

    public function save()
    {
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $lote = $this->loteId
            ? LoteEngorde::where('fundo_id', $fundoId)->findOrFail($this->loteId)
            : new LoteEngorde;
        $wasEdit = $lote->exists;

        $this->authorizePermission('engorde', $lote->exists ? 'actualizar' : 'crear');
        $this->validate([
            'nombre' => 'nullable|string|max:255',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'nullable|date|after_or_equal:fechaInicio',
            'estado' => 'required|in:activo,cerrado',
            'observaciones' => 'nullable|string|max:5000',
            'foto' => $this->photoRules()['foto'],
            'removeFoto' => 'boolean',
            'photoConfirmed' => 'boolean',
            ...ImageFrame::rules('fotoEncuadre'),
        ], $this->photoMessages());

        if ($this->foto && ! $this->photoConfirmed) {
            throw ValidationException::withMessages([
                'foto' => 'Confirma la nueva imagen antes de guardar.',
            ]);
        }

        $newPhoto = null;
        $previousPhoto = $lote->foto_ruta;
        $fotoEncuadre = ImageFrame::normalize($this->fotoEncuadre);
        $allocator = app(LoteCodeAllocator::class);

        try {
            if ($this->foto) {
                $newPhoto = ImageOptimizer::store($this->foto, 'fotos/engorde/lotes');
            }

            DB::transaction(function () use ($allocator, $fotoEncuadre, $fundoId, $lote, $newPhoto, &$previousPhoto): void {
                $target = $lote->exists
                    ? LoteEngorde::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($lote->id)
                    : $lote;
                $previousPhoto = $target->foto_ruta;
                $code = $allocator->allocate($target, $fundoId, now()->year);
                $photoPath = $newPhoto ?: ($this->removeFoto ? null : $target->foto_ruta);

                $target->fill([
                    'fundo_id' => $fundoId,
                    ...$code,
                    'nombre' => $this->nombre ?: null,
                    'foto_ruta' => $photoPath,
                    ...($photoPath === null
                        ? ['foto_encuadre' => null]
                        : (($newPhoto || $this->fotoEncuadreChanged) ? ['foto_encuadre' => $fotoEncuadre] : [])),
                    'fecha_inicio' => $this->fechaInicio,
                    'fecha_fin' => $this->fechaFin ?: null,
                    'estado' => $this->estado,
                    'observaciones' => $this->observaciones ?: null,
                ])->save();
            }, attempts: 5);
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($previousPhoto && ($newPhoto || $this->removeFoto) && $previousPhoto !== $newPhoto) {
            Storage::disk('public')->delete($previousPhoto);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => $wasEdit ? '¡Actualizado!' : '¡Registrado!',
            'text' => $wasEdit ? 'Lote actualizado correctamente.' : 'Lote creado correctamente.',
        ]);
        $this->publishRecentRecord('engorde.lotes', $lote);

        return $this->redirectRoute('engorde.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.engorde.lote-form')
            ->layout('layouts.app');
    }

    private function photoRules(): array
    {
        return [
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
        ];
    }

    private function photoMessages(): array
    {
        return [
            'foto.image' => 'Selecciona una imagen válida.',
            'foto.mimes' => 'Usa una imagen JPG, PNG o WebP.',
            'foto.max' => 'La imagen optimizada no puede superar 2 MB.',
            'foto.dimensions' => 'La imagen supera las dimensiones permitidas.',
        ];
    }
}
