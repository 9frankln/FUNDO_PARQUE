<?php

namespace App\Livewire\Medicamentos;

use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Services\MedicamentoPurchaseService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\MedicamentoLotCodeAllocator;
use App\Support\NumberFormatter;
use App\Traits\AuthorizesPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class Form extends Component
{
    use AuthorizesPermissions, WithFileUploads;

    #[Locked]
    public ?int $medicamentoId = null;

    #[Locked]
    public bool $isEdit = false;

    public string $nombre = '';

    public string $principioActivo = '';

    public string $concentracion = '';

    public string $tipo = 'otro';

    public string $presentacion = '';

    public string $viaPredeterminada = '';

    public string $unidadStock = 'ml';

    public int|float|string $stockMinimo = 0;

    public string $condicionAlmacenamiento = 'ambiente';

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
        $this->codigoLoteSugerido = app(MedicamentoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->codigoLoteAnio
        );
        $this->numeroLote = str_pad((string) $this->codigoLoteSugerido, 3, '0', STR_PAD_LEFT);
        if (! $id) {
            return;
        }

        $medicine = Medicamento::query()
            ->where(fn ($query) => $query->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
            ->findOrFail($id);
        $this->medicamentoId = $medicine->id;
        $this->isEdit = true;
        $this->agregarExistencia = false;
        $this->nombre = $medicine->nombre;
        $this->principioActivo = $medicine->principio_activo ?? '';
        $this->concentracion = $medicine->concentracion ?? '';
        $this->tipo = $medicine->tipo ?: 'otro';
        $this->presentacion = $medicine->presentacion ?? '';
        $this->viaPredeterminada = $medicine->via_predeterminada ?? '';
        $this->unidadStock = $medicine->unidad_stock ?: 'unidad';
        $this->stockMinimo = NumberFormatter::format($medicine->stock_minimo);
        $this->condicionAlmacenamiento = $medicine->condicion_almacenamiento ?: 'ambiente';
        $this->observaciones = $medicine->observaciones ?? '';
        $this->activo = $medicine->activo;
        $this->fotoActual = $medicine->foto_ruta;
        $this->fotoEncuadre = ImageFrame::normalize($medicine->foto_encuadre);
        $this->loadEditableLots($medicine);
    }

    public function updatedLoteId($value): void
    {
        if (! $this->isEdit || ! $value) {
            return;
        }

        $lot = MedicamentoLote::query()
            ->where('fundo_id', session('fundo_id'))
            ->where('medicamento_id', $this->medicamentoId)
            ->findOrFail((int) $value);

        $this->loadLot($lot);
        $this->resetValidation();
    }

    public function updatedFotoEncuadre(): void
    {
        $this->fotoEncuadreChanged = true;
    }

    public function cancelPhotoChange(): void
    {
        $this->reset('foto');
        $this->fotoEncuadre = $this->medicamentoId
            ? ImageFrame::normalize(Medicamento::where('fundo_id', session('fundo_id'))->find($this->medicamentoId)?->foto_encuadre)
            : ImageFrame::DEFAULT;
        $this->fotoEncuadreChanged = false;
        $this->resetValidation('foto');
    }

    public function updatedTipo(string $tipo): void
    {
        if ($this->isEdit || ! array_key_exists($tipo, Medicamento::TYPES)) {
            return;
        }

        [$this->unidadStock, $this->condicionAlmacenamiento, $this->viaPredeterminada] = match ($tipo) {
            'vacuna' => ['dosis', 'refrigerado_2_8', ''],
            'antiseptico' => ['ml', 'ambiente', 'topica'],
            default => ['ml', 'ambiente', ''],
        };
    }

    public function updatedNumeroLote(mixed $value): void
    {
        $this->numeroLote = MedicamentoLotCodeAllocator::normalizeNumber($value);
    }

    public function save(MedicamentoPurchaseService $purchases)
    {
        $this->authorizePermission('medicamentos', $this->isEdit ? 'actualizar' : 'crear');
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        foreach (['nombre', 'principioActivo', 'concentracion', 'presentacion', 'observaciones', 'numeroLote', 'proveedor', 'comprobante', 'ubicacion'] as $field) {
            $this->{$field} = trim((string) $this->{$field});
        }

        $this->unidadStock = $this->inferInventoryUnit();

        $rules = [
            'nombre' => ['required', 'string', 'max:255', Rule::unique('medicamentos', 'nombre')
                ->where('fundo_id', $fundoId)->ignore($this->medicamentoId)],
            'principioActivo' => ['nullable', 'string', 'max:255'],
            'concentracion' => ['nullable', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(array_keys(Medicamento::TYPES))],
            'presentacion' => ['nullable', 'string', 'max:255'],
            'viaPredeterminada' => ['nullable', Rule::in(array_keys(Medicamento::ROUTES))],
            'unidadStock' => ['required', Rule::in(array_keys(Medicamento::UNITS))],
            'stockMinimo' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'condicionAlmacenamiento' => ['required', 'string'],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'activo' => ['boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:2048'],
            ...ImageFrame::rules('fotoEncuadre'),
        ];

        if ($this->agregarExistencia) {
            $linkedPurchase = $this->isEdit && $this->loteId
                && MedicamentoLote::query()->where('fundo_id', $fundoId)->find((int) $this->loteId)?->movimiento_id !== null;
            $rules += [
                'tipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
                'numeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
                'fechaIngreso' => ['required', 'date', 'before_or_equal:today'],
                'fechaVencimiento' => ['required', 'date', 'after_or_equal:fechaIngreso'],
                'cantidadInicial' => ['required', 'numeric', 'gt:0', 'max:999999999'],
                'costoTotal' => [$linkedPurchase ? 'required' : 'nullable', 'numeric', $linkedPurchase ? 'min:0.01' : 'min:0', 'max:999999999'],
                'proveedor' => ['nullable', 'string', 'max:255'],
                'comprobante' => ['nullable', 'string', 'max:100'],
                'ubicacion' => ['nullable', 'string', 'max:255'],
            ];
        }

        $this->validate($rules, [
            'nombre.required' => 'Indica el nombre del producto.',
            'nombre.unique' => 'Ya existe un producto con este nombre.',
            'fechaVencimiento.required' => 'Indica la fecha de vencimiento.',
            'cantidadInicial.required' => 'Indica la cantidad inicial.',
            'cantidadInicial.gt' => 'La cantidad debe ser mayor que cero.',
            'numeroLote.required' => 'Indica los tres dígitos del código de medicamento.',
            'numeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'numeroLote.not_in' => 'La numeración debe iniciar en 001.',
            'costoTotal.required' => 'Indica el precio total de la compra.',
        ]);

        if (trim($this->presentacion) === '') {
            $this->presentacion = 'Envase';
        }

        $medicine = $this->isEdit
            ? Medicamento::query()->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))->findOrFail($this->medicamentoId)
            : new Medicamento(['fundo_id' => $fundoId]);
        $oldPhoto = $medicine->foto_ruta;
        $newPhoto = $this->foto
            ? ImageOptimizer::store($this->foto, "medicamentos/{$fundoId}", 'foto', 1600, 2 * 1024 * 1024, 'public')
            : null;
        $fotoEncuadre = ImageFrame::normalize($this->fotoEncuadre);

        try {
            DB::transaction(function () use ($medicine, $fundoId, $newPhoto, $fotoEncuadre, $purchases) {
                $photoPath = $newPhoto ?: ($this->eliminarFoto ? null : $medicine->foto_ruta);
                $data = [
                    'fundo_id' => $fundoId,
                    'nombre' => $this->nombre,
                    'principio_activo' => $this->principioActivo ?: null,
                    'concentracion' => $this->concentracion ?: null,
                    'tipo' => $this->tipo,
                    'presentacion' => $this->presentacion,
                    'via_predeterminada' => $this->viaPredeterminada ?: null,
                    'unidad_stock' => $this->unidadStock,
                    'stock_minimo' => $this->stockMinimo,
                    'condicion_almacenamiento' => $this->condicionAlmacenamiento,
                    ...($photoPath === null
                        ? ['foto_encuadre' => null, 'foto_ruta' => null]
                        : (($newPhoto || $this->fotoEncuadreChanged) ? ['foto_encuadre' => $fotoEncuadre, 'foto_ruta' => $photoPath] : ['foto_ruta' => $photoPath])),
                    'observaciones' => $this->observaciones ?: null,
                    'activo' => $this->activo,
                ];
                $medicine->fill($data)->save();

                if ($this->agregarExistencia) {
                    $lotData = [
                        'fundo_id' => $fundoId,
                        'codigo_anio' => $this->codigoLoteAnio,
                        'codigo_numero' => $this->numeroLote,
                        'codigo_automatico' => ! $this->isEdit && (int) $this->numeroLote === $this->codigoLoteSugerido,
                        'codigo_error_field' => 'numeroLote',
                        'fecha_ingreso' => $this->fechaIngreso,
                        'fecha_vencimiento' => $this->fechaVencimiento,
                        'cantidad_inicial' => $this->cantidadInicial,
                        'costo_total' => $this->costoTotal !== '' ? $this->costoTotal : null,
                        'proveedor' => $this->proveedor ?: null,
                        'comprobante' => $this->comprobante ?: null,
                        'ubicacion' => $this->ubicacion ?: null,
                    ];

                    if ($this->isEdit) {
                        $lot = MedicamentoLote::query()
                            ->where('fundo_id', $fundoId)
                            ->where('medicamento_id', $medicine->id)
                            ->findOrFail((int) $this->loteId);

                        if ((float) $this->cantidadInicial < $purchases->usedQuantity($lot)) {
                            throw ValidationException::withMessages([
                                'cantidadInicial' => 'La cantidad no puede ser menor que lo ya utilizado del lote.',
                            ]);
                        }

                        $purchases->updateLot($lot, $lotData);

                        return;
                    }

                    $purchases->createLot($medicine, $lotData, $this->tipoIngreso, auth()->id());

                    return;
                }
            });
        } catch (Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if (($newPhoto || $this->eliminarFoto) && $oldPhoto && $oldPhoto !== $newPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        session()->flash('swal', [
            'icon' => 'success',
            'title' => $this->isEdit ? 'Producto actualizado' : 'Medicamento registrado',
            'text' => $this->agregarExistencia
                ? 'Producto, lote y compra quedaron sincronizados.'
                : 'Los datos del producto quedaron guardados.',
        ]);

        return $this->redirectRoute('medicamentos.show', ['id' => $medicine->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.medicamentos.form', [
            'tipos' => Medicamento::TYPES,
            'unidades' => Medicamento::UNITS,
            'condiciones' => Medicamento::STORAGE_CONDITIONS,
            'vias' => Medicamento::ROUTES,
        ])->layout('layouts.app');
    }

    private function inferInventoryUnit(): string
    {
        if ($this->unidadStock !== 'ml') {
            return $this->unidadStock;
        }

        $presentation = Str::lower(Str::ascii($this->presentacion));

        return match (true) {
            Str::contains($presentation, ['tableta', 'comprimido', 'capsula']) => 'tableta',
            Str::contains($presentation, ['sobre']) => 'sobre',
            Str::contains($presentation, [' dosis', 'dosis ']) => 'dosis',
            Str::contains($presentation, [' kg']) => 'kg',
            Str::contains($presentation, [' g']) => 'g',
            default => $this->unidadStock,
        };
    }

    private function loadEditableLots(Medicamento $medicine): void
    {
        $lots = $medicine->lotes()
            ->where('fundo_id', session('fundo_id'))
            ->with('movimientoFinanciero:id,monto')
            ->latest('fecha_ingreso')
            ->latest('id')
            ->get();

        $this->lotesEditables = $lots->mapWithKeys(fn (MedicamentoLote $lot) => [
            (string) $lot->id => sprintf(
                '%s · %s %s · S/ %.2f',
                $lot->numero_lote,
                rtrim(rtrim(number_format((float) $lot->cantidad_inicial, 3, '.', ''), '0'), '.'),
                $medicine->unidad_stock,
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

    private function loadLot(MedicamentoLote $lot): void
    {
        $code = MedicamentoLotCodeAllocator::parse($lot->numero_lote);
        $this->codigoLoteAnio = (int) ($code['year'] ?? $lot->fecha_ingreso->year);
        $this->codigoLoteSugerido = $code['number'] ?? null;
        $this->numeroLote = str_pad((string) ($code['number'] ?? 1), 3, '0', STR_PAD_LEFT);
        $this->fechaIngreso = $lot->fecha_ingreso->format('Y-m-d');
        $this->fechaVencimiento = $lot->fecha_vencimiento->format('Y-m-d');
        $this->cantidadInicial = NumberFormatter::format($lot->cantidad_inicial);
        $this->costoTotal = $lot->costo_total ?? '';
        $this->proveedor = $lot->proveedor ?? '';
        $this->comprobante = $lot->comprobante ?? '';
        $this->ubicacion = $lot->ubicacion ?? '';
        $this->tipoIngreso = $lot->movimiento_id ? 'compra' : 'saldo_inicial';
    }
}
