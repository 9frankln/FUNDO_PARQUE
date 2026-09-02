<?php

namespace App\Livewire\Finanzas;

use App\Models\Animal;
use App\Models\CategoriaFinanciera;
use App\Models\Insumo;
use App\Models\InsumoLote;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\Movimiento;
use App\Services\AnimalInventoryService;
use App\Services\InsumoPurchaseService;
use App\Services\MedicamentoPurchaseService;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use App\Support\InsumoLotCodeAllocator;
use App\Support\MedicamentoLotCodeAllocator;
use App\Support\NumberFormatter;
use App\Traits\AuthorizesPermissions;
use App\Traits\PublishesRecentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

    public $destinoMedicamento = 'personas';

    public bool $modoMedicamentoNuevo = false;

    public string $nombreMedicamento = '';

    public string $principioActivoMedicamento = '';

    public string $concentracionMedicamento = '';

    public string $tipoMedicamento = 'antibiotico';

    public string $presentacionMedicamento = '';

    public string $laboratorioMedicamento = '';

    #[Locked]
    public ?int $medicamentoLoteId = null;

    public string $medicamentoId = '';

    public array $medicamentosDisponibles = [];

    public string $numeroLoteMedicamento = '';

    #[Locked]
    public int $codigoLoteMedicamentoAnio;

    #[Locked]
    public ?int $codigoLoteMedicamentoSugerido = null;

    public string $fechaVencimientoMedicamento = '';

    public int|float|string $cantidadMedicamento = '';

    public string $unidadMedicamento = 'ml';

    public string $proveedorMedicamento = '';

    public string $comprobanteMedicamento = '';

    public string $ubicacionMedicamento = '';

    public string $condicionAlmacenamientoMedicamento = 'ambiente';

    public string $viaPredeterminadaMedicamento = '';

    public int|float|string $stockMinimoMedicamento = 0;

    public string $observacionesMedicamento = '';

    public ?string $medicamentoFotoActual = null;

    public $destinoInsumo = 'animales';

    #[Locked]
    public ?int $insumoLoteId = null;

    public bool $modoInsumoNuevo = false;

    public string $nombreInsumo = '';

    public string $insumoId = '';

    public array $insumosDisponibles = [];

    public string $numeroLoteInsumo = '';

    #[Locked]
    public int $codigoLoteInsumoAnio;

    #[Locked]
    public ?int $codigoLoteInsumoSugerido = null;

    public string $fechaVencimientoInsumo = '';

    public int|float|string $cantidadInsumo = '';

    public string $unidadInsumo = 'unidad';

    public string $proveedorInsumo = '';

    public string $comprobanteInsumo = '';

    public string $ubicacionInsumo = '';

    public string $tipoInsumo = 'material_descartable';

    public string $presentacionInsumo = '';

    public string $marcaLaboratorioInsumo = '';

    public string $condicionAlmacenamientoInsumo = 'ambiente';

    public int|float|string $stockMinimoInsumo = 0;

    public string $observacionesInsumo = '';

    public ?string $insumoFotoActual = null;

    public $cantidadLitros = '';

    public $cantidadQuesos = '';

    public $beneficiario = '';

    public $proposito = 'estudio';

    public array $animalesDisponibles = [];

    #[Url(as: 'animal', except: null)]
    public ?int $animalVentaId = null;

    public function mount($id = null, $movId = null)
    {
        $id = $id ?? $movId;
        $requestedSaleDate = request()->query('fecha_venta');
        $this->fecha = is_string($requestedSaleDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedSaleDate)
            ? $requestedSaleDate
            : now()->format('Y-m-d');
        $this->codigoLoteMedicamentoAnio = now()->year;
        $this->codigoLoteMedicamentoSugerido = app(MedicamentoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->codigoLoteMedicamentoAnio
        );
        $this->numeroLoteMedicamento = str_pad((string) $this->codigoLoteMedicamentoSugerido, 3, '0', STR_PAD_LEFT);

        $this->codigoLoteInsumoAnio = now()->year;
        $this->codigoLoteInsumoSugerido = app(InsumoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->codigoLoteInsumoAnio
        );
        $this->numeroLoteInsumo = str_pad((string) $this->codigoLoteInsumoSugerido, 3, '0', STR_PAD_LEFT);

        $this->cargarCategorias();
        $this->cargarMedicamentos();
        $this->cargarInsumos();

        if (! $id) {
            $this->modoInsumoNuevo = true;
            $this->modoMedicamentoNuevo = true;
        }
        $this->animalesDisponibles = Animal::with(['especie', 'raza'])
            ->where('fundo_id', session('fundo_id'))
            ->where('activo', true)
            ->get()
            ->map(fn ($a) => [
                'id' => (string) $a->id,
                'code' => $a->arete,
                'name' => $a->nombre,
                'subtitle' => $a->subtitle,
                'details' => $a->details,
            ])
            ->all();

        if (! $id && $this->animalVentaId) {
            $requestedAnimalId = $this->animalVentaId;
            $animalToSell = $requestedAnimalId
                ? Animal::query()
                    ->where('fundo_id', session('fundo_id'))
                    ->where('activo', true)
                    ->find($requestedAnimalId)
                : null;

            if ($animalToSell) {
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
            $mov = Movimiento::query()
                ->with(['categoria', 'compraMedicamento.medicamento', 'compraInsumo.insumo'])
                ->where('fundo_id', session('fundo_id'))
                ->findOrFail($id);
            $this->movId = $mov->getKey();
            $this->isEdit = true;

            $this->tipo = $mov->tipo;
            $this->categoriaId = (string) $mov->categoria_id;
            $this->monto = $mov->monto;
            $this->moneda = $mov->moneda;
            $this->fecha = $mov->fecha->format('Y-m-d');
            $this->descripcion = $mov->descripcion;
            $this->comprobanteRuta = $mov->comprobante_ruta;
            $this->comprobanteEncuadre = $mov->comprobanteEsImagen()
                ? ImageFrame::normalize($mov->comprobante_encuadre)
                : ImageFrame::DEFAULT;

            $this->cargarCategorias();
            $this->selectedCategoriaNombre = mb_strtolower($mov->categoria?->nombre ?? '');

            if ($lot = $mov->compraMedicamento) {
                $medicine = $lot->medicamento;
                $this->destinoMedicamento = 'animales';
                $this->medicamentoLoteId = (int) $lot->id;
                $this->medicamentoId = (string) $lot->medicamento_id;
                $this->nombreMedicamento = $medicine->nombre;
                $this->principioActivoMedicamento = $medicine->principio_activo ?? '';
                $this->concentracionMedicamento = $medicine->concentracion ?? '';
                $this->tipoMedicamento = $medicine->tipo ?? 'antibiotico';
                $this->presentacionMedicamento = $medicine->presentacion ?? '';
                $this->laboratorioMedicamento = $medicine->laboratorio ?? '';
                $lotCode = MedicamentoLotCodeAllocator::parse($lot->numero_lote);
                $this->codigoLoteMedicamentoAnio = $lotCode['year'] ?? $lot->fecha_ingreso->year;
                $this->codigoLoteMedicamentoSugerido = $lotCode['number'] ?? null;
                $this->numeroLoteMedicamento = str_pad((string) ($lotCode['number'] ?? 1), 3, '0', STR_PAD_LEFT);
                $this->fechaVencimientoMedicamento = $lot->fecha_vencimiento->format('Y-m-d');
                $this->cantidadMedicamento = NumberFormatter::format($lot->cantidad_inicial);
                $this->unidadMedicamento = $medicine->unidad_stock;
                $this->proveedorMedicamento = $lot->proveedor ?? '';
                $this->comprobanteMedicamento = $lot->comprobante ?? '';
                $this->ubicacionMedicamento = $lot->ubicacion ?? '';
                $this->condicionAlmacenamientoMedicamento = $medicine->condicion_almacenamiento ?? 'ambiente';
                $this->viaPredeterminadaMedicamento = $medicine->via_predeterminada ?? '';
                $this->stockMinimoMedicamento = NumberFormatter::format($medicine->stock_minimo ?? 0);
                $this->observacionesMedicamento = $medicine->observaciones ?? '';
                $this->medicamentoFotoActual = $medicine->foto_ruta;
                $this->comprobanteEncuadre = ImageFrame::normalize($medicine->foto_encuadre);
            } elseif ($insLot = $mov->compraInsumo) {
                $insumo = $insLot->insumo;
                $this->destinoInsumo = 'animales';
                $this->insumoLoteId = (int) $insLot->id;
                $this->insumoId = (string) $insLot->insumo_id;
                $this->nombreInsumo = $insumo->nombre;
                $this->tipoInsumo = $insumo->tipo ?? 'material_descartable';
                $this->presentacionInsumo = $insumo->presentacion ?? '';
                $this->marcaLaboratorioInsumo = $insumo->marca_laboratorio ?? '';
                $lotCode = InsumoLotCodeAllocator::parse($insLot->numero_lote);
                $this->codigoLoteInsumoAnio = $lotCode['year'] ?? $insLot->fecha_ingreso->year;
                $this->codigoLoteInsumoSugerido = $lotCode['number'] ?? null;
                $this->numeroLoteInsumo = str_pad((string) ($lotCode['number'] ?? 1), 3, '0', STR_PAD_LEFT);
                $this->fechaVencimientoInsumo = $insLot->fecha_vencimiento ? $insLot->fecha_vencimiento->format('Y-m-d') : '';
                $this->cantidadInsumo = NumberFormatter::format($insLot->cantidad_inicial);
                $this->unidadInsumo = $insumo->unidad_stock;
                $this->proveedorInsumo = $insLot->proveedor ?? '';
                $this->comprobanteInsumo = $insLot->comprobante ?? '';
                $this->ubicacionInsumo = $insLot->ubicacion ?? '';
                $this->condicionAlmacenamientoInsumo = $insumo->condicion_almacenamiento ?? 'ambiente';
                $this->stockMinimoInsumo = NumberFormatter::format($insumo->stock_minimo ?? 0);
                $this->observacionesInsumo = $insumo->observaciones ?? '';
                $this->insumoFotoActual = $insumo->foto_ruta;
                $this->comprobanteEncuadre = ImageFrame::normalize($insumo->foto_encuadre);
            }

            // Solo cargar beneficiario/proposito si la categoría es Asignación Familiar;
            // de lo contrario, proposito queda en default válido para evitar fallo de validación.
            $isEditAsignacion = $this->tipo === 'egreso'
                && (str_contains($this->selectedCategoriaNombre, 'asignaci') || str_contains($this->selectedCategoriaNombre, 'familiar'));
            $this->beneficiario = $isEditAsignacion ? ($mov->beneficiario ?? '') : '';
            $this->proposito = $isEditAsignacion ? ($mov->proposito ?? 'estudio') : 'estudio';
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
        if ($this->isMedicationInventoryPurchase) {
            $medicine = $this->medicamentoId
                ? Medicamento::query()
                    ->where(fn ($query) => $query->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
                    ->find($this->medicamentoId)
                : null;
            $this->comprobanteEncuadre = ImageFrame::normalize($medicine?->foto_encuadre);
            $this->comprobanteEncuadreChanged = false;
            $this->resetValidation('comprobante');

            return;
        }

        if ($this->isInsumoInventoryPurchase) {
            $insumo = $this->insumoId
                ? Insumo::query()
                    ->where(fn ($query) => $query->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
                    ->find($this->insumoId)
                : null;
            $this->comprobanteEncuadre = ImageFrame::normalize($insumo?->foto_encuadre);
            $this->comprobanteEncuadreChanged = false;
            $this->resetValidation('comprobante');

            return;
        }

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
        $this->destinoMedicamento = 'personas';
        $this->cargarCategorias();
    }

    public function updatedCategoriaId($value): void
    {
        $this->destinoMedicamento = 'personas';
        $this->destinoInsumo = 'personas';

        if ($value) {
            $cat = collect($this->categorias)->firstWhere('id', (int) $value);
            $this->selectedCategoriaNombre = $cat ? mb_strtolower($cat['nombre']) : '';
            if (in_array((string) $value, $this->medicamentoCategoriaIds, true)) {
                $this->destinoMedicamento = 'animales';
            }
            if (in_array((string) $value, $this->insumoCategoriaIds, true)) {
                $this->destinoInsumo = 'animales';
            }
        } else {
            $this->selectedCategoriaNombre = '';
        }

        // Reset dynamic fields
        $this->dineroProviene = '';
        $this->animalesIds = [];
        $this->comprador = '';
        $this->cantidadLitros = '';
        $this->cantidadQuesos = '';
        $this->beneficiario = '';
        $this->proposito = 'estudio';
        $this->resetMedicationFields();
        $this->resetInsumoFields();
    }

    public function updatedMedicamentoId($value): void
    {
        if ($value) {
            $this->modoMedicamentoNuevo = false;
        }

        $medicine = $value
            ? Medicamento::query()
                ->where('activo', true)
                ->where(fn ($query) => $query->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
                ->find($value)
            : null;

        $this->unidadMedicamento = $medicine?->unidad_stock ?? 'ml';
        $this->medicamentoFotoActual = $medicine?->foto_ruta;
        $this->condicionAlmacenamientoMedicamento = $medicine?->condicion_almacenamiento ?? 'ambiente';
        $this->viaPredeterminadaMedicamento = $medicine?->via_predeterminada ?? '';
        $this->stockMinimoMedicamento = NumberFormatter::format($medicine?->stock_minimo ?? 0);
        $this->observacionesMedicamento = $medicine?->observaciones ?? '';
        $this->comprobanteEncuadre = ImageFrame::normalize($medicine?->foto_encuadre);
        $this->comprobanteEncuadreChanged = false;
    }

    public function updatedInsumoId($value): void
    {
        if ($value) {
            $this->modoInsumoNuevo = false;
        }

        $insumo = $value
            ? Insumo::query()
                ->where('activo', true)
                ->where(fn ($query) => $query->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
                ->find($value)
            : null;

        $this->unidadInsumo = $insumo?->unidad_stock ?? 'unidad';
        $this->tipoInsumo = $insumo?->tipo ?? 'material_descartable';
        $this->presentacionInsumo = $insumo?->presentacion ?? '';
        $this->marcaLaboratorioInsumo = $insumo?->marca_laboratorio ?? '';
        $this->insumoFotoActual = $insumo?->foto_ruta;
        $this->condicionAlmacenamientoInsumo = $insumo?->condicion_almacenamiento ?? 'ambiente';
        $this->stockMinimoInsumo = NumberFormatter::format($insumo?->stock_minimo ?? 0);
        $this->observacionesInsumo = $insumo?->observaciones ?? '';
        $this->comprobanteEncuadre = ImageFrame::normalize($insumo?->foto_encuadre);
        $this->comprobanteEncuadreChanged = false;
    }

    public function updatedNumeroLoteMedicamento(mixed $value): void
    {
        $this->numeroLoteMedicamento = MedicamentoLotCodeAllocator::normalizeNumber($value);
    }

    public function updatedNumeroLoteInsumo(mixed $value): void
    {
        $this->numeroLoteInsumo = InsumoLotCodeAllocator::normalizeNumber($value);
    }

    public function getSelectedCategoriaNombreProperty(): string
    {
        if (! $this->categoriaId) {
            return '';
        }
        $cat = collect($this->categorias)->firstWhere('id', (int) $this->categoriaId);

        return $cat ? mb_strtolower($cat['nombre']) : '';
    }

    public function getIsAsignacionFamiliarProperty(): bool
    {
        if ($this->tipo !== 'egreso') {
            return false;
        }
        $nombre = $this->selectedCategoriaNombre;

        return str_contains($nombre, 'asignaci') || str_contains($nombre, 'familiar');
    }

    public function getAsignacionCategoriaIdProperty(): ?int
    {
        foreach ($this->categorias as $cat) {
            $nombre = mb_strtolower((string) ($cat['nombre'] ?? ''));
            if (str_contains($nombre, 'asignaci') || str_contains($nombre, 'familiar')) {
                return (int) $cat['id'];
            }
        }

        return null;
    }

    public function getMedicamentoCategoriaIdsProperty(): array
    {
        $ids = [];
        foreach ($this->categorias as $cat) {
            $nombre = mb_strtolower((string) ($cat['nombre'] ?? ''));
            if (stripos($nombre, 'medicamento') !== false || stripos($nombre, 'fármaco') !== false || stripos($nombre, 'farmaco') !== false) {
                $ids[] = (string) $cat['id'];
            }
        }
        if ($this->medicamentoLoteId && $this->categoriaId && ! in_array((string) $this->categoriaId, $ids, true)) {
            $ids[] = (string) $this->categoriaId;
        }

        return $ids;
    }

    public function getMedicamentoCategoriaIdProperty(): ?int
    {
        $ids = $this->medicamentoCategoriaIds;

        return ! empty($ids) ? (int) $ids[0] : null;
    }

    public function getInsumoCategoriaIdsProperty(): array
    {
        $ids = [];
        foreach ($this->categorias as $cat) {
            $nombre = mb_strtolower((string) ($cat['nombre'] ?? ''));
            if (stripos($nombre, 'insumo') !== false || stripos($nombre, 'material') !== false) {
                $ids[] = (string) $cat['id'];
            }
        }
        if ($this->insumoLoteId && $this->categoriaId && ! in_array((string) $this->categoriaId, $ids, true)) {
            $ids[] = (string) $this->categoriaId;
        }

        return $ids;
    }

    public function getInsumoCategoriaIdProperty(): ?int
    {
        $ids = $this->insumoCategoriaIds;

        return ! empty($ids) ? (int) $ids[0] : null;
    }

    public function getIsMedicationInventoryPurchaseProperty(): bool
    {
        return $this->tipo === 'egreso'
            && $this->destinoMedicamento === 'animales'
            && (
                in_array((string) $this->categoriaId, $this->medicamentoCategoriaIds, true)
                || $this->medicamentoLoteId !== null
            );
    }

    public function getIsInsumoInventoryPurchaseProperty(): bool
    {
        return $this->tipo === 'egreso'
            && (
                in_array((string) $this->categoriaId, $this->insumoCategoriaIds, true)
                || $this->insumoLoteId !== null
            );
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

    public function cargarMedicamentos(): void
    {
        $this->medicamentosDisponibles = Medicamento::query()
            ->where('activo', true)
            ->where(fn ($query) => $query->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad_stock'])
            ->mapWithKeys(fn (Medicamento $medicine) => [
                (string) $medicine->id => $medicine->nombre.' · '.$medicine->unidad_stock,
            ])
            ->all();

        if (empty($this->medicamentosDisponibles) && ! $this->medicamentoLoteId) {
            $this->modoMedicamentoNuevo = true;
        }
    }

    public function cargarInsumos(): void
    {
        $this->insumosDisponibles = Insumo::query()
            ->where('activo', true)
            ->where(fn ($query) => $query->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad_stock'])
            ->mapWithKeys(fn (Insumo $insumo) => [
                (string) $insumo->id => $insumo->nombre.' · '.$insumo->unidad_stock,
            ])
            ->all();

        if (empty($this->insumosDisponibles) && ! $this->insumoLoteId) {
            $this->modoInsumoNuevo = true;
        }
    }

    public function updatedModoInsumoNuevo(bool $value): void
    {
        if ($value) {
            $this->insumoId = '';
            $this->unidadInsumo = 'unidad';
            $this->tipoInsumo = 'material_descartable';
            $this->condicionAlmacenamientoInsumo = 'ambiente';
            $this->stockMinimoInsumo = 0;
        }
    }

    public function updatedModoMedicamentoNuevo(bool $value): void
    {
        if ($value) {
            $this->medicamentoId = '';
            $this->unidadMedicamento = 'ml';
            $this->tipoMedicamento = 'antibiotico';
            $this->condicionAlmacenamientoMedicamento = 'ambiente';
            $this->stockMinimoMedicamento = 0;
        }
    }

    public function save(
        AnimalInventoryService $inventory,
        MedicamentoPurchaseService $purchases,
        InsumoPurchaseService $insumoPurchases
    ) {
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

        $isAsignacion = $this->tipo === 'egreso'
            && (str_contains($selectedCategoryName, 'asignaci') || str_contains($selectedCategoryName, 'familiar'));
        $isMedicationPurchase = $this->tipo === 'egreso'
            && $this->destinoMedicamento === 'animales'
            && (stripos($selectedCategoryName, 'medicamento') !== false || $this->medicamentoLoteId !== null);
        $isInsumoPurchase = $this->tipo === 'egreso'
            && $this->destinoInsumo === 'animales'
            && (
                stripos($selectedCategoryName, 'insumo') !== false
                || stripos($selectedCategoryName, 'material') !== false
                || ($this->insumoLoteId !== null)
            );

        if ($isAsignacion) {
            $rules['beneficiario'] = ['required', 'string', 'max:150'];
            $rules['proposito'] = ['required', 'string', Rule::in(['estudio', 'salud', 'alimentacion', 'vivienda', 'transporte', 'ropa', 'gastos_personales', 'emergencia', 'otros'])];
        }

        if ($isMedicationPurchase) {
            $isCreatingNewMed = $this->modoMedicamentoNuevo && empty($this->medicamentoId) && ! $this->medicamentoLoteId;
            if ($isCreatingNewMed) {
                $rules += [
                    'nombreMedicamento' => ['required', 'string', 'max:150'],
                    'unidadMedicamento' => ['required', 'string', Rule::in(array_keys(Medicamento::UNITS))],
                    'tipoMedicamento' => ['nullable', 'string', Rule::in(array_keys(Medicamento::TYPES))],
                ];
            } else {
                $rules += [
                    'medicamentoId' => [
                        'required',
                        Rule::exists('medicamentos', 'id')->where(fn ($query) => $query
                            ->where('activo', true)
                            ->where(fn ($scope) => $scope->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))),
                    ],
                ];
            }

            $rules += [
                'numeroLoteMedicamento' => [
                    'required',
                    'regex:/^\d{3}$/',
                    'not_in:000',
                ],
                'fechaVencimientoMedicamento' => ['required', 'date', 'after_or_equal:fecha'],
                'cantidadMedicamento' => ['required', 'numeric', 'gt:0', 'max:999999999'],
                'proveedorMedicamento' => ['nullable', 'string', 'max:255'],
                'comprobanteMedicamento' => ['nullable', 'string', 'max:100'],
                'ubicacionMedicamento' => ['nullable', 'string', 'max:255'],
                'comprobante' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ];
        }

        if ($isInsumoPurchase) {
            $isCreatingNewInsumo = $this->modoInsumoNuevo && empty($this->insumoId) && ! $this->insumoLoteId;
            if ($isCreatingNewInsumo) {
                $rules += [
                    'nombreInsumo' => ['required', 'string', 'max:150'],
                    'unidadInsumo' => ['required', 'string', Rule::in(array_keys(Insumo::UNITS))],
                    'tipoInsumo' => ['nullable', 'string', Rule::in(array_keys(Insumo::TYPES))],
                ];
            } else {
                $rules += [
                    'insumoId' => [
                        'required',
                        Rule::exists('insumos', 'id')->where(fn ($query) => $query
                            ->where('activo', true)
                            ->where(fn ($scope) => $scope->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))),
                    ],
                ];
            }

            $rules += [
                'numeroLoteInsumo' => [
                    'required',
                    'regex:/^\d{3}$/',
                    'not_in:000',
                ],
                'fechaVencimientoInsumo' => ['nullable', 'date', 'after_or_equal:fecha'],
                'cantidadInsumo' => ['required', 'numeric', 'gt:0', 'max:999999999'],
                'proveedorInsumo' => ['nullable', 'string', 'max:255'],
                'comprobanteInsumo' => ['nullable', 'string', 'max:100'],
                'ubicacionInsumo' => ['nullable', 'string', 'max:255'],
                'comprobante' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ];
        }

        // Los datos de origen y venta solo se capturan al crear; al editar se conserva el vínculo original.
        if ($this->tipo === 'ingreso' && ! $wasEdit) {
            if (str_contains($selectedCategoryName, 'préstamo') || str_contains($selectedCategoryName, 'prestamo') || str_contains($selectedCategoryName, 'subsidio')) {
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
            'nombreMedicamento.required' => 'Indica el nombre del nuevo medicamento.',
            'unidadMedicamento.required' => 'Selecciona la unidad de medida del medicamento.',
            'medicamentoId.required' => 'Selecciona el medicamento que ingresa al inventario.',
            'cantidadMedicamento.required' => 'Indica la cantidad comprada.',
            'cantidadMedicamento.gt' => 'La cantidad debe ser mayor que cero.',
            'fechaVencimientoMedicamento.required' => 'Indica la fecha de vencimiento.',
            'numeroLoteMedicamento.required' => 'Indica los tres dígitos del código de medicamento.',
            'numeroLoteMedicamento.regex' => 'La numeración debe contener tres dígitos.',
            'numeroLoteMedicamento.not_in' => 'La numeración debe iniciar en 001.',
            'nombreInsumo.required' => 'Indica el nombre del nuevo insumo / material.',
            'unidadInsumo.required' => 'Selecciona la unidad de medida del insumo.',
            'insumoId.required' => 'Selecciona el insumo que ingresa al inventario.',
            'cantidadInsumo.required' => 'Indica la cantidad comprada.',
            'cantidadInsumo.gt' => 'La cantidad debe ser mayor que cero.',
            'numeroLoteInsumo.required' => 'Indica los tres dígitos del código de insumo.',
            'numeroLoteInsumo.regex' => 'La numeración debe contener tres dígitos.',
            'numeroLoteInsumo.not_in' => 'La numeración debe iniciar en 001.',
        ]);

        $category = $selectedCategory ?? CategoriaFinanciera::query()->findOrFail((int) $this->categoriaId);
        $categoryName = mb_strtolower($category->nombre);
        $isAnimalSale = $this->tipo === 'ingreso'
            && stripos($categoryName, 'venta de animal') !== false;
        $this->selectedCategoriaNombre = $categoryName;

        if ($isMedicationPurchase) {
            return $this->saveMedicationPurchase(
                $purchases,
                $existingMovimiento,
                $category,
                $wasEdit
            );
        }

        if ($isInsumoPurchase) {
            return $this->saveInsumoPurchase(
                $insumoPurchases,
                $existingMovimiento,
                $category,
                $wasEdit
            );
        }

        $finalDescription = trim($this->descripcion);
        $saleAnimalCodes = [];

        if ($this->tipo === 'ingreso' && ! $wasEdit) {
            $prefix = '';
            if (str_contains($this->selectedCategoriaNombre, 'préstamo') || str_contains($this->selectedCategoriaNombre, 'prestamo')) {
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
            'beneficiario' => $isAsignacion ? trim($this->beneficiario) : null,
            'proposito' => $isAsignacion ? $this->proposito : null,
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

                $fundoId = (int) session('fundo_id');
                if ($insumo = $movimiento->compraInsumo?->insumo) {
                    if ($newReceipt && $receiptIsImage) {
                        $ext = pathinfo($newReceipt, PATHINFO_EXTENSION) ?: 'webp';
                        $publicInsumoPath = "insumos/{$fundoId}/insumo_{$insumo->id}_".\Illuminate\Support\Str::random(8).".{$ext}";
                        Storage::disk('public')->put($publicInsumoPath, Storage::disk('local')->get($newReceipt));
                        if ($insumo->foto_ruta && Storage::disk('public')->exists($insumo->foto_ruta)) {
                            Storage::disk('public')->delete($insumo->foto_ruta);
                        }
                        $insumo->update([
                            'foto_ruta' => $publicInsumoPath,
                            'foto_encuadre' => $data['comprobante_encuadre'] ?? $insumo->foto_encuadre,
                        ]);
                    } elseif ($this->comprobanteEncuadreChanged && ! empty($data['comprobante_encuadre'])) {
                        $insumo->update(['foto_encuadre' => $data['comprobante_encuadre']]);
                    }
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
            'title' => $wasEdit ? '¡Actualizado!' : '¡Registrado!',
            'text' => $wasEdit
                ? 'El movimiento y su comprobante se actualizaron correctamente.'
                : 'El movimiento fue registrado correctamente.',
        ]);
        $this->publishRecentRecord('finanzas.movimientos', $movimiento);

        return $this->redirectRoute('finanzas.index', ['tab' => 'movimientos'], navigate: true);
    }

    public function render()
    {
        return view('livewire.finanzas.movimiento-form')
            ->layout('layouts.app');
    }

    private function saveMedicationPurchase(
        MedicamentoPurchaseService $purchases,
        ?Movimiento $existingMovimiento,
        CategoriaFinanciera $category,
        bool $wasEdit
    ) {
        $fundoId = (int) session('fundo_id');
        $lot = $this->medicamentoLoteId
            ? MedicamentoLote::query()
                ->with('medicamento')
                ->where('fundo_id', $fundoId)
                ->where('movimiento_id', $existingMovimiento?->id)
                ->findOrFail($this->medicamentoLoteId)
            : null;

        if ($lot && (int) $this->medicamentoId !== (int) $lot->medicamento_id) {
            throw ValidationException::withMessages([
                'medicamentoId' => 'No puedes cambiar el producto de una compra existente.',
            ]);
        }

        $isCreatingNewMed = $this->modoMedicamentoNuevo && empty($this->medicamentoId) && ! $lot;
        if ($isCreatingNewMed) {
            $medicine = new Medicamento([
                'fundo_id' => $fundoId,
                'nombre' => trim($this->nombreMedicamento),
                'principio_activo' => trim($this->principioActivoMedicamento) ?: null,
                'concentracion' => trim($this->concentracionMedicamento) ?: null,
                'tipo' => $this->tipoMedicamento ?: 'antibiotico',
                'presentacion' => trim($this->presentacionMedicamento) ?: null,
                'laboratorio' => trim($this->laboratorioMedicamento) ?: null,
                'via_predeterminada' => $this->viaPredeterminadaMedicamento ?: null,
                'unidad_stock' => $this->unidadMedicamento ?: 'ml',
                'stock_minimo' => $this->stockMinimoMedicamento !== '' ? $this->stockMinimoMedicamento : 0,
                'condicion_almacenamiento' => $this->condicionAlmacenamientoMedicamento ?: 'ambiente',
                'observaciones' => trim($this->observacionesMedicamento) ?: null,
                'activo' => true,
            ]);
        } else {
            $medicine = $lot?->medicamento ?? Medicamento::query()
                ->where('activo', true)
                ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->findOrFail((int) $this->medicamentoId);
        }

        if ($lot && (float) $this->cantidadMedicamento < $purchases->usedQuantity($lot)) {
            throw ValidationException::withMessages([
                'cantidadMedicamento' => 'La cantidad no puede ser menor que lo ya utilizado del lote.',
            ]);
        }

        $oldPhoto = $medicine->foto_ruta;
        $newPhoto = $this->comprobante
            ? ImageOptimizer::store(
                $this->comprobante,
                "medicamentos/{$fundoId}",
                'foto',
                1600,
                2 * 1024 * 1024,
                'public'
            )
            : null;
        $frame = ImageFrame::normalize($this->comprobanteEncuadre);
        $lotData = [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->codigoLoteMedicamentoAnio,
            'codigo_numero' => trim($this->numeroLoteMedicamento),
            'codigo_automatico' => ! $lot && (int) $this->numeroLoteMedicamento === $this->codigoLoteMedicamentoSugerido,
            'codigo_error_field' => 'numeroLoteMedicamento',
            'fecha_ingreso' => $this->fecha,
            'fecha_vencimiento' => $this->fechaVencimientoMedicamento,
            'cantidad_inicial' => $this->cantidadMedicamento,
            'costo_total' => $this->monto,
            'proveedor' => trim($this->proveedorMedicamento) ?: null,
            'comprobante' => trim($this->comprobanteMedicamento) ?: null,
            'ubicacion' => trim($this->ubicacionMedicamento) ?: null,
        ];
        $movementOverrides = [
            'categoria_id' => $category->id,
            'moneda' => $this->moneda,
            'descripcion' => trim($this->descripcion),
        ];

        try {
            $lot = DB::transaction(function () use ($medicine, $lot, $lotData, $movementOverrides, $purchases, $newPhoto, $frame) {
                if ($medicine->exists) {
                    $medicine->fill([
                        'condicion_almacenamiento' => $this->condicionAlmacenamientoMedicamento,
                        'via_predeterminada' => $this->viaPredeterminadaMedicamento ?: null,
                        'stock_minimo' => $this->stockMinimoMedicamento !== '' ? $this->stockMinimoMedicamento : 0,
                        'observaciones' => trim($this->observacionesMedicamento) ?: null,
                    ]);
                }

                if ($newPhoto || $this->comprobanteEncuadreChanged) {
                    $medicine->forceFill([
                        ...($newPhoto ? ['foto_ruta' => $newPhoto] : []),
                        'foto_encuadre' => $frame,
                    ]);
                }
                $medicine->save();

                return $lot
                    ? $purchases->updateLot($lot, $lotData, $movementOverrides)
                    : $purchases->createLot($medicine, $lotData, 'compra', auth()->id(), $movementOverrides);
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto && $oldPhoto && $oldPhoto !== $newPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $movimiento = $lot->movimientoFinanciero;
        session()->flash('swal', [
            'icon' => 'success',
            'title' => $wasEdit ? '¡Compra actualizada!' : '¡Compra registrada!',
            'text' => 'Finanzas, inventario, lote y foto quedaron sincronizados.',
        ]);
        $this->publishRecentRecord('finanzas.movimientos', $movimiento);

        return $this->redirectRoute('finanzas.index', ['tab' => 'movimientos'], navigate: true);
    }

    private function saveInsumoPurchase(
        InsumoPurchaseService $purchases,
        ?Movimiento $existingMovimiento,
        CategoriaFinanciera $category,
        bool $wasEdit
    ) {
        $fundoId = (int) session('fundo_id');
        $lot = $this->insumoLoteId
            ? InsumoLote::query()
                ->with('insumo')
                ->where('fundo_id', $fundoId)
                ->where('movimiento_id', $existingMovimiento?->id)
                ->findOrFail($this->insumoLoteId)
            : null;

        if ($lot && (int) $this->insumoId !== (int) $lot->insumo_id) {
            throw ValidationException::withMessages([
                'insumoId' => 'No puedes cambiar el producto de una compra existente.',
            ]);
        }

        $isCreatingNewInsumo = $this->modoInsumoNuevo && empty($this->insumoId) && ! $lot;
        if ($isCreatingNewInsumo) {
            $insumo = new Insumo([
                'fundo_id' => $fundoId,
                'nombre' => trim($this->nombreInsumo),
                'tipo' => $this->tipoInsumo ?: 'material_descartable',
                'presentacion' => trim($this->presentacionInsumo) ?: null,
                'marca_laboratorio' => trim($this->marcaLaboratorioInsumo) ?: null,
                'unidad_stock' => $this->unidadInsumo ?: 'unidad',
                'stock_minimo' => $this->stockMinimoInsumo !== '' ? $this->stockMinimoInsumo : 0,
                'condicion_almacenamiento' => $this->condicionAlmacenamientoInsumo ?: 'ambiente',
                'observaciones' => trim($this->observacionesInsumo) ?: null,
                'activo' => true,
            ]);
        } else {
            $insumo = $lot?->insumo ?? Insumo::query()
                ->where('activo', true)
                ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->findOrFail((int) $this->insumoId);
        }

        $oldPhoto = $insumo->foto_ruta;
        $newPhoto = $this->comprobante
            ? ImageOptimizer::store(
                $this->comprobante,
                "insumos/{$fundoId}",
                'foto',
                1600,
                2 * 1024 * 1024,
                'public'
            )
            : null;
        $frame = ImageFrame::normalize($this->comprobanteEncuadre);
        $lotData = [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->codigoLoteInsumoAnio,
            'codigo_numero' => trim($this->numeroLoteInsumo),
            'codigo_automatico' => ! $lot && (int) $this->numeroLoteInsumo === $this->codigoLoteInsumoSugerido,
            'codigo_error_field' => 'numeroLoteInsumo',
            'fecha_ingreso' => $this->fecha,
            'fecha_vencimiento' => $this->fechaVencimientoInsumo ?: null,
            'cantidad_inicial' => $this->cantidadInsumo,
            'costo_total' => $this->monto,
            'proveedor' => trim($this->proveedorInsumo) ?: null,
            'comprobante' => trim($this->comprobanteInsumo) ?: null,
            'ubicacion' => trim($this->ubicacionInsumo) ?: null,
        ];
        $movementOverrides = [
            'categoria_id' => $category->id,
            'moneda' => $this->moneda,
            'descripcion' => trim($this->descripcion),
        ];

        try {
            $lot = DB::transaction(function () use ($insumo, $lot, $lotData, $movementOverrides, $purchases, $newPhoto, $frame) {
                if ($insumo->exists) {
                    $insumo->fill([
                        'tipo' => $this->tipoInsumo ?: $insumo->tipo,
                        'presentacion' => trim($this->presentacionInsumo) ?: null,
                        'marca_laboratorio' => trim($this->marcaLaboratorioInsumo) ?: null,
                        'condicion_almacenamiento' => $this->condicionAlmacenamientoInsumo,
                        'stock_minimo' => $this->stockMinimoInsumo !== '' ? $this->stockMinimoInsumo : 0,
                        'observaciones' => trim($this->observacionesInsumo) ?: null,
                    ]);
                }

                if ($newPhoto || $this->comprobanteEncuadreChanged) {
                    $insumo->forceFill([
                        ...($newPhoto ? ['foto_ruta' => $newPhoto] : []),
                        'foto_encuadre' => $frame,
                    ]);
                }
                $insumo->save();

                return $lot
                    ? $purchases->updateLot($lot, $lotData, $movementOverrides)
                    : $purchases->createLot($insumo, $lotData, 'compra', auth()->id(), $movementOverrides);
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto && $oldPhoto && $oldPhoto !== $newPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $movimiento = $lot->movimientoFinanciero;
        session()->flash('swal', [
            'icon' => 'success',
            'title' => $wasEdit ? '¡Compra actualizada!' : '¡Compra registrada!',
            'text' => 'Finanzas, insumo, lote y foto quedaron sincronizados.',
        ]);
        $this->publishRecentRecord('finanzas.movimientos', $movimiento);

        return $this->redirectRoute('finanzas.index', ['tab' => 'movimientos'], navigate: true);
    }

    private function resetMedicationFields(): void
    {
        $this->modoMedicamentoNuevo = true;
        $this->nombreMedicamento = '';
        $this->principioActivoMedicamento = '';
        $this->concentracionMedicamento = '';
        $this->tipoMedicamento = 'antibiotico';
        $this->presentacionMedicamento = '';
        $this->laboratorioMedicamento = '';
        $this->viaPredeterminadaMedicamento = '';
        $this->medicamentoLoteId = null;
        $this->medicamentoId = '';
        $this->codigoLoteMedicamentoAnio = now()->year;
        $this->codigoLoteMedicamentoSugerido = app(MedicamentoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->codigoLoteMedicamentoAnio
        );
        $this->numeroLoteMedicamento = str_pad((string) $this->codigoLoteMedicamentoSugerido, 3, '0', STR_PAD_LEFT);
        $this->fechaVencimientoMedicamento = '';
        $this->cantidadMedicamento = '';
        $this->unidadMedicamento = 'ml';
        $this->proveedorMedicamento = '';
        $this->comprobanteMedicamento = '';
        $this->ubicacionMedicamento = '';
        $this->condicionAlmacenamientoMedicamento = 'ambiente';
        $this->stockMinimoMedicamento = 0;
        $this->observacionesMedicamento = '';
        $this->medicamentoFotoActual = null;
    }

    private function resetInsumoFields(): void
    {
        $this->modoInsumoNuevo = true;
        $this->nombreInsumo = '';
        $this->tipoInsumo = 'material_descartable';
        $this->presentacionInsumo = '';
        $this->marcaLaboratorioInsumo = '';
        $this->unidadInsumo = 'unidad';
        $this->insumoLoteId = null;
        $this->insumoId = '';
        $this->codigoLoteInsumoAnio = now()->year;
        $this->codigoLoteInsumoSugerido = app(InsumoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->codigoLoteInsumoAnio
        );
        $this->numeroLoteInsumo = str_pad((string) $this->codigoLoteInsumoSugerido, 3, '0', STR_PAD_LEFT);
        $this->fechaVencimientoInsumo = '';
        $this->cantidadInsumo = '';
        $this->proveedorInsumo = '';
        $this->comprobanteInsumo = '';
        $this->ubicacionInsumo = '';
        $this->condicionAlmacenamientoInsumo = 'ambiente';
        $this->stockMinimoInsumo = 0;
        $this->observacionesInsumo = '';
        $this->insumoFotoActual = null;
    }
}
