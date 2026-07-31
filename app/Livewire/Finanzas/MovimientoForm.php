<?php

namespace App\Livewire\Finanzas;

use App\Models\Animal;
use App\Models\CategoriaFinanciera;
use App\Models\Movimiento;
use App\Services\AnimalInventoryService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class MovimientoForm extends Component
{
    use AuthorizesPermissions, PublishesRecentRecord, WithFileUploads;

    #[Locked]
    public $movId = null;

    #[Locked]
    public $isEdit = false;

    public $tipo = 'egreso';

    public $categoriaId = '';

    public $monto = '';

    public $moneda = 'PEN';

    public $fecha = '';

    public $descripcion = '';

    public $comprobante;

    public array $comprobanteEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $comprobanteEncuadreChanged = false;

    public $comprobanteRuta = null;

    public array $categorias = [];

    public $selectedCategoriaNombre = '';

    public $dineroProviene = '';

    public array $animalesIds = [];

    public $comprador = '';

    public $cantidadLitros = '';

    public $cantidadQuesos = '';

    public array $animalesDisponibles = [];

    #[Url(as: 'animal', except: null)]
    public ?int $animalVentaId = null;

    public function mount($id = null)
    {
        $requestedSaleDate = request()->query('fecha_venta');
        $this->fecha = is_string($requestedSaleDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedSaleDate)
            ? $requestedSaleDate
            : now()->format('Y-m-d');
        $this->cargarCategorias();
        $this->animalesDisponibles = Animal::with(['especie', 'raza'])
            ->where('fundo_id', session('fundo_id'))
            ->where('activo', true)
            ->get()
            ->map(fn ($a) => [
                'id' => (string) $a->id,
                'code' => $a->arete,
                'name' => $a->nombre,
                'type' => $a->especie?->nombre ?? 'Otros',
                'species' => $a->especie?->nombre,
                'breed' => $a->raza?->nombre,
                'sex' => $a->genero === 'macho' ? 'Macho' : 'Hembra',
            ])
            ->toArray();

        if (! $id) {
            $requestedAnimalId = (int) ($this->animalVentaId ?: request()->query('animal'));
            $animalToSell = $requestedAnimalId
                ? Animal::query()
                    ->where('fundo_id', session('fundo_id'))
                    ->where('activo', true)
                    ->find($requestedAnimalId)
                : null;

            if ($animalToSell) {
                $this->animalVentaId = (int) $animalToSell->id;
                $this->tipo = 'ingreso';
                $this->cargarCategorias();
                $saleCategory = collect($this->categorias)
                    ->first(fn (array $category) => stripos($category['nombre'], 'venta de animal') !== false);

                if ($saleCategory) {
                    $this->categoriaId = (string) $saleCategory['id'];
                    $this->selectedCategoriaNombre = mb_strtolower($saleCategory['nombre']);
                    $this->animalesIds = [(string) $animalToSell->id];
                }
            }
        }

        if ($id) {
            $mov = Movimiento::where('fundo_id', session('fundo_id'))->findOrFail($id);
            $this->movId = $mov->getKey();
            $this->isEdit = true;

            $this->tipo = $mov->tipo;
            $this->categoriaId = $mov->categoria_id;
            $this->monto = $mov->monto;
            $this->moneda = $mov->moneda;
            $this->fecha = $mov->fecha->format('Y-m-d');
            $this->descripcion = $mov->descripcion;
            $this->comprobanteRuta = $mov->comprobante_ruta;
            $this->comprobanteEncuadre = $mov->comprobanteEsImagen()
                ? ImageFrame::normalize($mov->comprobante_encuadre)
                : ImageFrame::DEFAULT;

            $this->cargarCategorias();
        }
    }

    public function updatedComprobante(): void
    {
        if ($this->comprobante && str_starts_with((string) $this->comprobante->getMimeType(), 'image/')) {
            $this->comprobanteEncuadre = ImageFrame::DEFAULT;
        }
    }

    public function updatedComprobanteEncuadre(): void
    {
        $this->comprobanteEncuadreChanged = true;
    }

    public function cancelAttachmentChange(): void
    {
        $this->reset('comprobante');
        $movimiento = $this->movId
            ? Movimiento::where('fundo_id', session('fundo_id'))->find($this->movId)
            : null;
        $this->comprobanteEncuadre = $movimiento?->comprobanteEsImagen()
            ? ImageFrame::normalize($movimiento->comprobante_encuadre)
            : ImageFrame::DEFAULT;
        $this->comprobanteEncuadreChanged = false;
        $this->resetValidation('comprobante');
    }

    public function updatedTipo($value): void
    {
        if (! in_array($value, ['ingreso', 'egreso'], true)) {
            $this->tipo = 'egreso';
        }

        $this->categoriaId = '';
        $this->selectedCategoriaNombre = '';
        $this->cargarCategorias();
    }

    public function updatedCategoriaId($value): void
    {
        if ($value) {
            $cat = collect($this->categorias)->firstWhere('id', (int) $value);
            $this->selectedCategoriaNombre = $cat ? mb_strtolower($cat['nombre']) : '';
        } else {
            $this->selectedCategoriaNombre = '';
        }

        // Reset dynamic fields
        $this->dineroProviene = '';
        $this->animalesIds = [];
        $this->comprador = '';
        $this->cantidadLitros = '';
        $this->cantidadQuesos = '';
    }

    public function cargarCategorias(): void
    {
        $this->categorias = CategoriaFinanciera::query()
            ->where('tipo', $this->tipo)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre'])
            ->map(fn (CategoriaFinanciera $category) => [
                'id' => (int) $category->id,
                'nombre' => $category->nombre,
            ])
            ->all();
    }

    public function save(AnimalInventoryService $inventory)
    {
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $existingMovimiento = $this->movId
            ? Movimiento::where('fundo_id', $fundoId)->findOrFail($this->movId)
            : null;
        $wasEdit = $existingMovimiento !== null;

        $this->authorizePermission('finanzas', $wasEdit ? 'actualizar' : 'crear');
        $selectedCategory = CategoriaFinanciera::query()
            ->where('tipo', $this->tipo)
            ->where('activo', true)
            ->find((int) $this->categoriaId);
        $selectedCategoryName = mb_strtolower((string) $selectedCategory?->nombre);

        $rules = [
            'tipo' => 'required|in:ingreso,egreso',
            'categoriaId' => [
                'required',
                Rule::exists('categorias_financieras', 'id')->where(fn ($query) => $query
                    ->where('tipo', $this->tipo)
                    ->where('activo', true)
                    ->where(fn ($scope) => $scope->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))),
            ],
            'monto' => 'required|numeric|min:0.01',
            'moneda' => 'required|string|max:3',
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'comprobante' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:25600',
            ...ImageFrame::rules('comprobanteEncuadre'),
        ];

        // Los datos de origen y venta solo se capturan al crear; al editar se conserva el vínculo original.
        if ($this->tipo === 'ingreso' && ! $wasEdit) {
            if (str_contains($selectedCategoryName, 'préstamo') || str_contains($selectedCategoryName, 'subsidio')) {
                $rules['dineroProviene'] = ['required', 'string', 'max:150'];
            } elseif (stripos($selectedCategoryName, 'venta de animal') !== false) {
                $rules['animalesIds'] = ['required', 'array', 'min:1'];
                $rules['animalesIds.*'] = [
                    'distinct',
                    Rule::exists('animales', 'id')->where(fn ($query) => $query
                        ->where('fundo_id', $fundoId)
                        ->where('activo', true)
                        ->whereNull('deleted_at')),
                ];
                $rules['comprador'] = ['nullable', 'string', 'max:150'];
            } elseif (str_contains($selectedCategoryName, 'venta de leche')) {
                $rules['cantidadLitros'] = 'required|numeric|min:0.01';
                $rules['comprador'] = 'nullable|string|max:255';
            } elseif (str_contains($selectedCategoryName, 'venta de queso')) {
                $rules['cantidadQuesos'] = 'required|integer|min:1';
                $rules['comprador'] = 'nullable|string|max:255';
            }
        }

        $this->validate($rules, [
            'categoriaId.required' => 'Selecciona una categoría.',
            'categoriaId.exists' => 'La categoría no corresponde al tipo de movimiento.',
            'comprobante.mimes' => 'Usa un PDF o una imagen JPG, PNG o WebP.',
            'comprobante.max' => 'El archivo original no puede superar 25 MB.',
        ]);

        $category = $selectedCategory ?? CategoriaFinanciera::query()->findOrFail((int) $this->categoriaId);
        $categoryName = mb_strtolower($category->nombre);
        $isAnimalSale = $this->tipo === 'ingreso'
            && stripos($categoryName, 'venta de animal') !== false;
        $this->selectedCategoriaNombre = $categoryName;
        $finalDescription = trim($this->descripcion);
        $saleAnimalCodes = [];

        if ($this->tipo === 'ingreso' && ! $wasEdit) {
            $prefix = '';
            if (str_contains($this->selectedCategoriaNombre, 'préstamo')) {
                $prefix = "[Préstamo de: {$this->dineroProviene}]";
            } elseif (str_contains($this->selectedCategoriaNombre, 'subsidio')) {
                $prefix = "[Subsidio de: {$this->dineroProviene}]";
            } elseif (stripos($this->selectedCategoriaNombre, 'venta de animal') !== false && ! empty($this->animalesIds)) {
                $saleAnimalCodes = Animal::query()
                    ->where('fundo_id', $fundoId)
                    ->where('activo', true)
                    ->whereIn('id', $this->animalesIds)
                    ->pluck('arete')
                    ->all();
                $animalesStr = implode(', ', $saleAnimalCodes);
                $prefix = "[Venta Animales: {$animalesStr}]";
                if ($this->comprador) {
                    $prefix .= " [A: {$this->comprador}]";
                }
            } elseif (str_contains($this->selectedCategoriaNombre, 'venta de leche')) {
                $prefix = "[{$this->cantidadLitros} Ltrs]";
                if ($this->comprador) {
                    $prefix .= " [A: {$this->comprador}]";
                }
            } elseif (str_contains($this->selectedCategoriaNombre, 'venta de queso')) {
                $prefix = "[{$this->cantidadQuesos} Quesos]";
                if ($this->comprador) {
                    $prefix .= " [A: {$this->comprador}]";
                }
            }

            if ($prefix) {
                $finalDescription = $finalDescription ? "{$prefix} - {$finalDescription}" : $prefix;
            }
        }

        $data = [
            'fundo_id' => $fundoId,
            'tipo' => $this->tipo,
            'categoria_id' => (int) $this->categoriaId,
            'monto' => $this->monto,
            'moneda' => $this->moneda,
            'fecha' => $this->fecha,
            'descripcion' => $finalDescription ?: null,
        ];

        $previousReceipt = $existingMovimiento?->comprobante_ruta;
        $newReceipt = null;
        $newReceiptIsImage = $this->comprobante
            && str_starts_with((string) $this->comprobante->getMimeType(), 'image/');
        $receiptIsImage = $this->comprobante
            ? $newReceiptIsImage
            : ($existingMovimiento?->comprobanteEsImagen() ?? false);
        if (! $receiptIsImage) {
            $data['comprobante_encuadre'] = null;
        } elseif ($this->comprobante || $this->comprobanteEncuadreChanged) {
            $data['comprobante_encuadre'] = ImageFrame::normalize($this->comprobanteEncuadre);
        }

        try {
            if ($this->comprobante) {
                $newReceipt = $newReceiptIsImage
                    ? ImageOptimizer::store(
                        $this->comprobante,
                        'comprobantes',
                        'comprobante',
                        1400,
                        900 * 1024,
                        'local'
                    )
                    : $this->comprobante->store('comprobantes', 'local');
            }

            if ($newReceipt) {
                $data['comprobante_ruta'] = $newReceipt;
            }

            $movimiento = DB::transaction(function () use (
                $existingMovimiento,
                $data,
                $isAnimalSale,
                $wasEdit,
                $inventory
            ): Movimiento {
                $movimiento = Movimiento::updateOrCreate(
                    ['id' => $existingMovimiento?->getKey()],
                    $data
                );

                if ($isAnimalSale && ! $wasEdit) {
                    $inventory->linkSale($movimiento, $this->animalesIds, trim($this->comprador) ?: null);
                }

                return $movimiento;
            });
        } catch (\Throwable $exception) {
            if ($newReceipt) {
                Storage::disk('local')->delete($newReceipt);
            }

            throw $exception;
        }

        if ($newReceipt && $previousReceipt && $previousReceipt !== $newReceipt) {
            Storage::disk('local')->delete($previousReceipt);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => $wasEdit ? 'Movimiento actualizado' : 'Movimiento registrado',
            'text' => $wasEdit
                ? 'El movimiento y su comprobante se actualizaron correctamente.'
                : 'El movimiento fue registrado correctamente.',
        ]);
        session()->flash('success', $wasEdit ? 'Movimiento actualizado correctamente.' : 'Movimiento registrado correctamente.');
        $this->publishRecentRecord('finanzas.movimientos', $movimiento);

        return redirect()->route('finanzas.index', ['tab' => 'movimientos']);
    }

    public function render()
    {
        return view('livewire.finanzas.movimiento-form')
            ->layout('layouts.app');
    }
}
