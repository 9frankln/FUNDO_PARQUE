<?php

namespace App\Livewire\Queso;

use App\Models\ProduccionQueso;
use App\Models\ProduccionQuesoPresentacion;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Form extends Component
{
    use AuthorizesPermissions, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $prodId = null;

    #[Locked]
    public $isEdit = false;

    public $fecha = '';

    public int|float|string $litrosLeche = '';

    public array $presentaciones = [];

    public $foto;

    public array $fotoEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $fotoEncuadreChanged = false;

    #[Locked]
    public $fotoRuta = null;

    public $observaciones = '';

    #[Locked]
    public bool $legacyWithoutPresentations = false;

    #[Locked]
    public int $legacyUnidades = 0;

    #[Locked]
    public string $legacyPesoTotalKg = '0.00';

    public function mount($id = null)
    {
        $this->fecha = now()->format('Y-m-d');
        $this->presentaciones = [['peso_gramos' => '1000', 'cantidad' => 1]];

        if ($id) {
            $this->prodId = $id;
            $this->isEdit = true;
            $prod = ProduccionQueso::with('presentaciones')
                ->where('fundo_id', session('fundo_id'))
                ->findOrFail($id);

            $this->fecha = $prod->fecha->format('Y-m-d');
            $this->litrosLeche = $prod->litros_leche_usados !== null ? (string) $prod->litros_leche_usados : '';
            $this->fotoRuta = $prod->foto_ruta;
            $this->fotoEncuadre = ImageFrame::normalize($prod->foto_encuadre);
            $this->observaciones = $prod->observaciones;

            if ($prod->presentaciones->isNotEmpty()) {
                $this->presentaciones = $prod->presentaciones
                    ->map(fn ($item) => [
                        'peso_gramos' => (string) $item->peso_gramos,
                        'cantidad' => $item->cantidad,
                    ])
                    ->values()
                    ->all();
            } else {
                $this->presentaciones = [];
                $this->legacyWithoutPresentations = true;
                $this->legacyUnidades = $prod->unidades;
                $this->legacyPesoTotalKg = $prod->peso_total_kg;
            }
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
        $this->fotoEncuadre = $this->prodId
            ? ImageFrame::normalize(ProduccionQueso::where('fundo_id', session('fundo_id'))->find($this->prodId)?->foto_encuadre)
            : ImageFrame::DEFAULT;
        $this->fotoEncuadreChanged = false;
        $this->resetValidation('foto');
    }

    public function addPresentacion(): void
    {
        if (count($this->presentaciones) >= count(ProduccionQuesoPresentacion::PESOS)) {
            return;
        }

        $usedWeights = array_map('intval', array_column($this->presentaciones, 'peso_gramos'));
        $nextWeight = collect(array_keys(ProduccionQuesoPresentacion::PESOS))
            ->first(fn ($weight) => ! in_array($weight, $usedWeights, true));

        $this->presentaciones[] = [
            'peso_gramos' => (string) $nextWeight,
            'cantidad' => 1,
        ];
    }

    public function removePresentacion(int $index): void
    {
        if (! array_key_exists($index, $this->presentaciones)) {
            return;
        }

        unset($this->presentaciones[$index]);
        $this->presentaciones = array_values($this->presentaciones);
        $this->resetValidation('presentaciones');
    }

    #[Computed]
    public function totalUnidades(): int
    {
        if ($this->legacyWithoutPresentations && $this->presentaciones === []) {
            return $this->legacyUnidades;
        }

        return $this->presentationTotals()['unidades'];
    }

    #[Computed]
    public function pesoTotalKg(): string
    {
        if ($this->legacyWithoutPresentations && $this->presentaciones === []) {
            return $this->legacyPesoTotalKg;
        }

        return number_format($this->presentationTotals()['gramos'] / 1000, 2, '.', '');
    }

    public function save()
    {
        $fundoId = session('fundo_id');
        $production = $this->prodId
            ? ProduccionQueso::where('fundo_id', $fundoId)->findOrFail($this->prodId)
            : null;

        $this->authorizePermission('queso', $production ? 'actualizar' : 'crear');
        $hasStoredBreakdown = $production?->presentaciones()->exists() ?? false;
        $requiresBreakdown = ! $production || $hasStoredBreakdown || $this->presentaciones !== [];

        $rules = [
            'fecha' => [
                'required',
                'date',
                Rule::unique('producciones_queso', 'fecha')
                    ->where(fn ($query) => $query
                        ->where('fundo_id', $fundoId)
                        ->whereNull('deleted_at'))
                    ->ignore($production?->id),
            ],
            'litrosLeche' => ['nullable', 'numeric', 'min:0.01', 'max:999999.99'],
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:max_width=6000,max_height=6000',
            ...ImageFrame::rules('fotoEncuadre'),
            'observaciones' => 'nullable|string|max:5000',
        ];

        if ($requiresBreakdown) {
            $rules += [
                'presentaciones' => ['required', 'array', 'min:1', 'max:5'],
                'presentaciones.*.peso_gramos' => [
                    'required',
                    'integer',
                    'distinct',
                    Rule::in(array_keys(ProduccionQuesoPresentacion::PESOS)),
                ],
                'presentaciones.*.cantidad' => ['required', 'integer', 'min:1', 'max:100000'],
            ];
        }

        $this->validate($rules, [
            'litrosLeche.numeric' => 'Ingresa un volumen de leche válido.',
            'litrosLeche.min' => 'El volumen de leche debe ser mayor a 0.',
            'presentaciones.required' => 'Agrega al menos una presentación de queso.',
            'presentaciones.min' => 'Agrega al menos una presentación de queso.',
            'presentaciones.*.peso_gramos.distinct' => 'No repitas la misma presentación.',
            'presentaciones.*.peso_gramos.in' => 'Selecciona un peso válido.',
            'presentaciones.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
        ]);

        $totals = $requiresBreakdown
            ? $this->presentationTotals()
            : ['unidades' => $production->unidades, 'gramos' => (int) round($production->peso_total_kg * 1000)];
        $previousPhoto = $production?->foto_ruta;
        $newPhoto = null;
        $fotoEncuadre = ImageFrame::normalize($this->fotoEncuadre);

        try {
            if ($this->foto) {
                $newPhoto = $this->storeOptimizedPhoto();
            }

            DB::transaction(function () use (&$production, $fotoEncuadre, $fundoId, $totals, $requiresBreakdown, $newPhoto) {
                $photoPath = $newPhoto ?: $production?->foto_ruta;
                $data = [
                    'fundo_id' => $fundoId,
                    'fecha' => $this->fecha,
                    'unidades' => $totals['unidades'],
                    'peso_total_kg' => $totals['gramos'] / 1000,
                    'litros_leche_usados' => $this->litrosLeche !== '' ? (float) $this->litrosLeche : null,
                    ...($photoPath === null
                        ? ['foto_encuadre' => null]
                        : (($newPhoto || $this->fotoEncuadreChanged) ? ['foto_encuadre' => $fotoEncuadre] : [])),
                    'observaciones' => $this->observaciones ?: null,
                ];

                if ($newPhoto) {
                    $data['foto_ruta'] = $newPhoto;
                }

                if ($production) {
                    $production->update($data);
                } else {
                    $production = ProduccionQueso::create($data);
                }

                if ($requiresBreakdown) {
                    $production->presentaciones()->delete();
                    $production->presentaciones()->createMany(array_map(fn ($item) => [
                        'peso_gramos' => (int) $item['peso_gramos'],
                        'cantidad' => (int) $item['cantidad'],
                    ], $this->presentaciones));
                }
            });
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto && $previousPhoto && $previousPhoto !== $newPhoto) {
            Storage::disk('public')->delete($previousPhoto);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => $this->isEdit ? '¡Actualizado!' : '¡Registrado!',
            'text' => $this->isEdit ? 'Registro de producción de queso actualizado.' : 'Registro de producción de queso creado.',
        ]);
        $this->publishRecentRecord('queso.producciones', $production);

        return $this->redirectRoute('queso.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.queso.form', [
            'pesoOptions' => ProduccionQuesoPresentacion::PESOS,
        ])
            ->layout('layouts.app');
    }

    private function presentationTotals(): array
    {
        $unidades = 0;
        $gramos = 0;

        foreach ($this->presentaciones as $item) {
            $peso = (int) ($item['peso_gramos'] ?? 0);
            $cantidad = max(0, (int) ($item['cantidad'] ?? 0));

            if (! array_key_exists($peso, ProduccionQuesoPresentacion::PESOS)) {
                continue;
            }

            $unidades += $cantidad;
            $gramos += $peso * $cantidad;
        }

        return compact('unidades', 'gramos');
    }

    private function storeOptimizedPhoto(): string
    {
        return ImageOptimizer::store($this->foto, 'queso');
    }
}
