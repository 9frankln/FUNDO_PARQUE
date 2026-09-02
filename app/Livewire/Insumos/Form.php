<?php

namespace App\Livewire\Insumos;

use App\Models\Insumo;
use App\Models\InsumoLote;
use App\Services\InsumoPurchaseService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\InsumoLotCodeAllocator;
use App\Support\NumberFormatter;
use App\Traits\AuthorizesPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Form extends Component
{
    use AuthorizesPermissions, WithFileUploads;

    #[Locked]
    public ?int $insumoId = null;

    #[Locked]
    public bool $isEdit = false;

    public string $nombre = '';

    public string $tipo = 'material_descartable';

    public string $presentacion = '';

    public string $marcaLaboratorio = '';

    public string $unidadStock = 'unidad';

    public int|float|string $stockMinimo = 0;

    public string $condicionAlmacenamiento = 'ambiente';

    public string $ubicacionPredeterminada = '';

    public string $observaciones = '';

    public bool $activo = true;

    public $foto;

    #[Locked]
    public ?string $fotoActual = null;

    public array $fotoEncuadre = ImageFrame::DEFAULT;

    #[Locked]
    public bool $fotoEncuadreChanged = false;

    public bool $eliminarFoto = false;

    public bool $agregarExistencia = true;

    public string $loteId = '';

    public array $lotesEditables = [];

    public string $tipoIngreso = 'compra';

    public string $numeroLote = '';

    #[Locked]
    public int $codigoLoteAnio;

    #[Locked]
    public ?int $codigoLoteSugerido = null;

    public string $fechaIngreso = '';

    public string $fechaVencimiento = '';

    public int|float|string $cantidadInicial = '';

    public int|float|string $costoTotal = '';

    public string $proveedor = '';

    public string $comprobante = '';

    public string $ubicacion = '';

    public function mount($id = null): void
    {
        $this->fechaIngreso = now()->toDateString();
        $this->codigoLoteAnio = now()->year;
        $this->codigoLoteSugerido = app(InsumoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->codigoLoteAnio
        );
        $this->numeroLote = str_pad((string) $this->codigoLoteSugerido, 3, '0', STR_PAD_LEFT);

        if ($id) {
            $this->isEdit = true;
            $fundoId = (int) session('fundo_id');
            $item = Insumo::query()
                ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->findOrFail($id);

            $this->insumoId = $item->id;
            $this->nombre = $item->nombre;
            $this->tipo = $item->tipo ?? 'material_descartable';
            $this->presentacion = $item->presentacion ?? '';
            $this->marcaLaboratorio = $item->marca_laboratorio ?? '';
            $this->unidadStock = $item->unidad_stock ?? 'unidad';
            $this->stockMinimo = NumberFormatter::format($item->stock_minimo ?? 0);
            $this->condicionAlmacenamiento = $item->condicion_almacenamiento ?? 'ambiente';
            $this->ubicacionPredeterminada = $item->ubicacion_predeterminada ?? '';
            $this->observaciones = $item->observaciones ?? '';
            $this->activo = (bool) $item->activo;
            $this->fotoActual = $item->foto_ruta;
            $this->fotoEncuadre = ImageFrame::normalize($item->foto_encuadre);

            if (! $this->fotoActual) {
                $linkedMov = InsumoLote::query()
                    ->where('insumo_id', $item->id)
                    ->whereNotNull('movimiento_id')
                    ->with('movimientoFinanciero')
                    ->get()
                    ->pluck('movimientoFinanciero')
                    ->filter(fn ($m) => $m && $m->comprobanteEsImagen())
                    ->first();

                if ($linkedMov && Storage::disk('local')->exists($linkedMov->comprobante_ruta)) {
                    $ext = pathinfo($linkedMov->comprobante_ruta, PATHINFO_EXTENSION) ?: 'webp';
                    $publicPath = "insumos/{$fundoId}/insumo_{$item->id}_".Str::random(8).".{$ext}";
                    Storage::disk('public')->put($publicPath, Storage::disk('local')->get($linkedMov->comprobante_ruta));
                    $item->update([
                        'foto_ruta' => $publicPath,
                        'foto_encuadre' => ImageFrame::normalize($linkedMov->comprobante_encuadre),
                    ]);
                    $this->fotoActual = $publicPath;
                    $this->fotoEncuadre = ImageFrame::normalize($item->foto_encuadre);
                }
            }

            $this->loadEditableLots($item);
        }
    }

    public function updatedNumeroLote(mixed $value): void
    {
        $this->numeroLote = InsumoLotCodeAllocator::normalizeNumber($value);
    }

    public function updatedLoteId(mixed $value): void
    {
        if (! $value) {
            return;
        }

        $lot = InsumoLote::query()
            ->where('fundo_id', session('fundo_id'))
            ->where('insumo_id', $this->insumoId)
            ->findOrFail((int) $value);

        $this->loadLot($lot);
        $this->resetValidation();
    }

    private function loadEditableLots(Insumo $item): void
    {
        $lots = $item->lotes()
            ->where('fundo_id', session('fundo_id'))
            ->with('movimientoFinanciero:id,monto')
            ->latest('fecha_ingreso')
            ->latest('id')
            ->get();

        $this->lotesEditables = $lots->mapWithKeys(fn (InsumoLote $lot) => [
            (string) $lot->id => sprintf(
                '%s · %s %s · S/ %.2f',
                $lot->numero_lote,
                rtrim(rtrim(number_format((float) $lot->cantidad_inicial, 3, '.', ''), '0'), '.'),
                $item->unidad_stock,
                (float) $lot->costo_total
            ),
        ])->all();

        $latestLot = $lots->first();
        $this->agregarExistencia = $latestLot !== null;

        if ($latestLot) {
            $this->loteId = (string) $latestLot->id;
            $this->loadLot($latestLot);
        }
    }

    private function loadLot(InsumoLote $lot): void
    {
        $code = InsumoLotCodeAllocator::parse($lot->numero_lote);
        $this->codigoLoteAnio = (int) ($code['year'] ?? $lot->fecha_ingreso->year);
        $this->codigoLoteSugerido = $code['number'] ?? null;
        $this->numeroLote = str_pad((string) ($code['number'] ?? 1), 3, '0', STR_PAD_LEFT);
        $this->fechaIngreso = $lot->fecha_ingreso->format('Y-m-d');
        $this->fechaVencimiento = $lot->fecha_vencimiento ? $lot->fecha_vencimiento->format('Y-m-d') : '';
        $this->cantidadInicial = NumberFormatter::format($lot->cantidad_inicial);
        $this->costoTotal = $lot->costo_total ?? '';
        $this->proveedor = $lot->proveedor ?? '';
        $this->comprobante = $lot->comprobante ?? '';
        $this->ubicacion = $lot->ubicacion ?? '';
        $this->tipoIngreso = $lot->movimiento_id || (float) $lot->costo_total > 0 ? 'compra' : 'saldo_inicial';
    }

    public function updatedFoto(): void
    {
        if ($this->foto) {
            $this->fotoEncuadre = ImageFrame::DEFAULT;
            $this->fotoEncuadreChanged = true;
        }
    }

    public function updatedFotoEncuadre(): void
    {
        $this->fotoEncuadreChanged = true;
    }

    public function cancelPhotoChange(): void
    {
        $this->reset('foto');
        $this->fotoEncuadre = $this->insumoId
            ? ImageFrame::normalize(Insumo::where('fundo_id', session('fundo_id'))->find($this->insumoId)?->foto_encuadre)
            : ImageFrame::DEFAULT;
        $this->fotoEncuadreChanged = false;
        $this->resetValidation('foto');
    }

    public function removePhoto(): void
    {
        $this->eliminarFoto = true;
        $this->fotoActual = null;
        $this->reset('foto');
        $this->fotoEncuadre = ImageFrame::DEFAULT;
        $this->fotoEncuadreChanged = true;
    }

    public function save(InsumoPurchaseService $purchases)
    {
        $this->authorizePermission('medicamentos', $this->isEdit ? 'actualizar' : 'crear');
        $fundoId = (int) session('fundo_id');

        $rules = [
            'nombre' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(array_keys(Insumo::TYPES))],
            'presentacion' => ['nullable', 'string', 'max:255'],
            'marcaLaboratorio' => ['nullable', 'string', 'max:255'],
            'unidadStock' => ['required', Rule::in(array_keys(Insumo::UNITS))],
            'stockMinimo' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'condicionAlmacenamiento' => ['required', Rule::in(array_keys(Insumo::STORAGE_CONDITIONS))],
            'ubicacionPredeterminada' => ['nullable', 'string', 'max:255'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'file', 'mimes:jpeg,png,webp,jpg', 'max:25600'],
        ];

        if ($this->agregarExistencia) {
            $rules = [
                ...$rules,
                'tipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
                'numeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
                'fechaIngreso' => ['required', 'date', 'before_or_equal:today'],
                'fechaVencimiento' => ['nullable', 'date', 'after_or_equal:fechaIngreso'],
                'cantidadInicial' => ['required', 'numeric', 'gt:0', 'max:999999999'],
                'costoTotal' => [$this->tipoIngreso === 'compra' ? 'required' : 'nullable', 'numeric', $this->tipoIngreso === 'compra' ? 'min:0.01' : 'min:0', 'max:999999999'],
                'proveedor' => ['nullable', 'string', 'max:255'],
                'comprobante' => ['nullable', 'string', 'max:100'],
                'ubicacion' => ['nullable', 'string', 'max:255'],
            ];
        }

        $this->validate($rules, [
            'nombre.required' => 'El nombre del insumo es obligatorio.',
            'tipo.required' => 'Selecciona el tipo de insumo.',
            'unidadStock.required' => 'Selecciona la unidad de medida.',
            'stockMinimo.required' => 'Indica el stock mínimo de alerta.',
            'cantidadInicial.required' => 'Indica la cantidad inicial.',
            'cantidadInicial.gt' => 'La cantidad debe ser mayor que cero.',
            'costoTotal.required' => 'Indica el costo total de la compra.',
            'numeroLote.required' => 'Indica los tres dígitos del código de insumo.',
            'numeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'numeroLote.not_in' => 'La numeración debe iniciar en 001.',
        ]);

        $newPhotoPath = null;
        if ($this->foto) {
            $newPhotoPath = ImageOptimizer::store($this->foto, "insumos/{$fundoId}", 'foto', 1600, 2 * 1024 * 1024, 'public');
        }

        $targetLot = $this->isEdit && $this->loteId
            ? InsumoLote::query()->where('fundo_id', $fundoId)->where('insumo_id', $this->insumoId)->find($this->loteId)
            : null;

        $lotData = $this->agregarExistencia ? [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->codigoLoteAnio,
            'codigo_numero' => (int) $this->numeroLote,
            'codigo_automatico' => ! $targetLot && (int) $this->numeroLote === $this->codigoLoteSugerido,
            'codigo_error_field' => 'numeroLote',
            'fecha_ingreso' => $this->fechaIngreso,
            'fecha_vencimiento' => $this->fechaVencimiento ?: null,
            'cantidad_inicial' => $this->cantidadInicial,
            'costo_total' => $this->tipoIngreso === 'compra' ? $this->costoTotal : null,
            'proveedor' => trim($this->proveedor) ?: null,
            'comprobante' => trim($this->comprobante) ?: null,
            'ubicacion' => trim($this->ubicacion) ?: null,
        ] : null;

        try {
            DB::transaction(function () use ($fundoId, $newPhotoPath, $targetLot, $lotData, $purchases) {
                $item = $this->isEdit
                    ? Insumo::query()->where(fn ($q) => $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))->findOrFail($this->insumoId)
                    : new Insumo(['fundo_id' => $fundoId]);

                $item->fill([
                    'nombre' => trim($this->nombre),
                    'tipo' => $this->tipo,
                    'presentacion' => trim($this->presentacion) ?: null,
                    'marca_laboratorio' => trim($this->marcaLaboratorio) ?: null,
                    'unidad_stock' => $this->unidadStock,
                    'stock_minimo' => $this->stockMinimo,
                    'condicion_almacenamiento' => $this->condicionAlmacenamiento,
                    'ubicacion_predeterminada' => trim($this->ubicacionPredeterminada) ?: null,
                    'observaciones' => trim($this->observaciones) ?: null,
                    'activo' => $this->activo,
                ]);

                if ($newPhotoPath) {
                    if ($item->foto_ruta && Storage::disk('public')->exists($item->foto_ruta)) {
                        Storage::disk('public')->delete($item->foto_ruta);
                    }
                    $item->foto_ruta = $newPhotoPath;
                    $item->foto_encuadre = $this->fotoEncuadre;
                } elseif ($this->eliminarFoto) {
                    if ($item->foto_ruta && Storage::disk('public')->exists($item->foto_ruta)) {
                        Storage::disk('public')->delete($item->foto_ruta);
                    }
                    $item->foto_ruta = null;
                    $item->foto_encuadre = ImageFrame::DEFAULT;
                } elseif ($this->fotoEncuadreChanged) {
                    $item->foto_encuadre = $this->fotoEncuadre;
                }

                $item->save();
                $this->insumoId = $item->id;

                if ($this->agregarExistencia && $lotData) {
                    if ($targetLot) {
                        $purchases->updateLot($targetLot, $lotData);
                    } else {
                        $purchases->createLot($item, $lotData, $this->tipoIngreso, auth()->id());
                    }
                }

                $linkedMovs = InsumoLote::query()
                    ->where('insumo_id', $item->id)
                    ->whereNotNull('movimiento_id')
                    ->with('movimientoFinanciero')
                    ->get()
                    ->pluck('movimientoFinanciero')
                    ->filter();

                foreach ($linkedMovs as $mov) {
                    if ($newPhotoPath && Storage::disk('public')->exists($newPhotoPath)) {
                        $localPath = 'comprobantes/insumo_'.$item->id.'_'.Str::random(8).'.webp';
                        Storage::disk('local')->put($localPath, Storage::disk('public')->get($newPhotoPath));
                        if ($mov->comprobante_ruta && Storage::disk('local')->exists($mov->comprobante_ruta)) {
                            Storage::disk('local')->delete($mov->comprobante_ruta);
                        }
                        $mov->update([
                            'comprobante_ruta' => $localPath,
                            'comprobante_encuadre' => $this->fotoEncuadre,
                        ]);
                    } elseif ($this->eliminarFoto) {
                        if ($mov->comprobante_ruta && Storage::disk('local')->exists($mov->comprobante_ruta)) {
                            Storage::disk('local')->delete($mov->comprobante_ruta);
                        }
                        $mov->update([
                            'comprobante_ruta' => null,
                            'comprobante_encuadre' => null,
                        ]);
                    } elseif ($this->fotoEncuadreChanged) {
                        $mov->update(['comprobante_encuadre' => $this->fotoEncuadre]);
                    }
                }
            });
        } catch (Throwable $e) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }
            throw $e;
        }

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => $this->isEdit ? 'Insumo actualizado' : 'Insumo registrado',
            'text' => 'Catálogo y botiquín actualizados.',
        ]);

        return $this->redirectRoute('insumos.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.insumos.form', [
            'tipos' => Insumo::TYPES,
            'unidades' => Insumo::UNITS,
            'condiciones' => Insumo::STORAGE_CONDITIONS,
        ])->layout('layouts.app');
    }
}
