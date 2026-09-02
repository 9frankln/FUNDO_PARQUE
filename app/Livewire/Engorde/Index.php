<?php

namespace App\Livewire\Engorde;

use App\Models\CategoriaFinanciera;
use App\Models\EngordeAnimal;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\Movimiento;
use App\Services\AnimalInventoryService;
use App\Support\EngordeReport;
use App\Support\ImageOptimizer;
use App\Traits\AuthorizesPermissions;
use App\Support\PaginationOptions;
use App\Traits\HasPdfPreviewModal;
use App\Traits\HasPeriodoFilters;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal, HasPeriodoFilters, HasRecentRecord, WithFileUploads, WithPagination;

    public $search = '';

    public $estado = '';

    public $perPage = 10;

    public string $sortBy = 'fecha_inicio';

    public string $sortDir = 'desc';

    public $showDetailedReportModal = false;

    public $detailedReportScope = 'filtered';

    public array $detailedReportLotIds = [];

    public array $detailedReportColumns = EngordeReport::DEFAULT_COLUMNS;

    // Modales Liquidación / Venta
    public bool $showVenderLoteModal = false;

    public ?int $selectedLoteId = null;

    public ?LoteEngorde $selectedLote = null;

    public string $fechaVenta = '';

    public string $compradorVenta = '';

    public string $montoVenta = '';

    public string $observacionesVenta = '';

    public $comprobanteVenta = null;

    public array $animalesAVender = [];

    public array $preciosAnimales = [];

    // Modal Cierre de Lote
    public bool $showCerrarLoteModal = false;

    public string $fechaCierreLote = '';

    public string $observacionesCierreLote = '';

    public $exportFormat = 'pdf';

    public string $activeExportType = 'summary';

    public $selectedColumns = ['codigo', 'nombre', 'fecha_inicio', 'fecha_fin', 'animales', 'estado'];

    public $availableColumns = [
        'codigo' => 'Código',
        'nombre' => 'Nombre del lote',
        'fecha_inicio' => 'Fecha de inicio',
        'fecha_fin' => 'Fecha de fin',
        'animales' => 'Cantidad de animales',
        'estado' => 'Estado',
        'observaciones' => 'Observaciones',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'estado' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'periodo' => ['except' => ''],
        'anio' => ['except' => ''],
        'mes' => ['except' => ''],
    ];

    protected $listeners = [
        'confirmarEliminacion'   => 'delete',
        'confirmarCierreLote'    => 'finalizarLoteConfirmado',
        'confirmarReabrirLote'   => 'reabrirLoteConfirmado',
    ];

    private const PER_PAGE_OPTIONS = PaginationOptions::PER_PAGE;

    public function updated($property): void
    {
        if (in_array($property, [
            'search',
            'estado',
            'fechaDesde',
            'fechaHasta',
            'periodo',
            'anio',
            'mes',
        ], true)) {
            $this->resetPage();
        }
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, self::PER_PAGE_OPTIONS, true) ? (int) $value : 10;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'estado', 'fechaDesde', 'fechaHasta', 'periodo', 'anio', 'mes']);
        $this->resetPage();
    }

    public function solicitarEliminacion($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Estás seguro?',
            'text' => 'Se eliminará el lote y desvinculará a los animales del proceso.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacion',
            'id' => $id,
        ]);
    }

    public function delete($id = null)
    {
        $this->authorizePermission('engorde', 'eliminar');

        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        $lote = LoteEngorde::find($targetId);
        if ($lote) {
            $lote->delete();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Lote eliminado',
                'text' => 'El lote ha sido eliminado exitosamente.',
            ]);
        }
    }

    public function openVenderLoteModal(int $loteId): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $this->authorizePermission('finanzas', 'crear');
        $fundoId = (int) session('fundo_id');

        $lote = LoteEngorde::where('fundo_id', $fundoId)
            ->with(['animales.animal'])
            ->findOrFail($loteId);

        abort_unless($lote->estado === 'activo', 422, 'El lote ya se encuentra cerrado.');
        $activeAnimals = $lote->animales->filter(fn ($ea) => $ea->estado === 'engorde_activo');
        abort_if($activeAnimals->isEmpty(), 422, 'No hay animales activos en este lote para vender.');

        $this->selectedLoteId = $loteId;
        $this->selectedLote = $lote;
        $this->fechaVenta = now()->format('Y-m-d');
        $this->compradorVenta = '';
        $this->montoVenta = '';
        $this->observacionesVenta = '';
        $this->comprobanteVenta = null;
        $this->animalesAVender = $activeAnimals->pluck('animal_id')->mapWithKeys(fn ($id) => [$id => true])->all();
        $this->preciosAnimales = $activeAnimals->pluck('animal_id')->mapWithKeys(fn ($id) => [$id => ''])->all();
        $this->resetValidation();
        $this->showVenderLoteModal = true;
    }

    public function closeVenderLoteModal(): void
    {
        $this->showVenderLoteModal = false;
        $this->reset(['selectedLoteId', 'selectedLote', 'fechaVenta', 'compradorVenta', 'montoVenta', 'observacionesVenta', 'comprobanteVenta', 'animalesAVender', 'preciosAnimales']);
        $this->resetValidation();
    }

    public function updatedPreciosAnimales(): void
    {
        $this->recalcularMontoVentaDesdePrecios();
    }

    public function updatedAnimalesAVender(): void
    {
        $this->recalcularMontoVentaDesdePrecios();
    }

    private function recalcularMontoVentaDesdePrecios(): void
    {
        $selectedIds = collect($this->animalesAVender)->filter()->keys();
        $total = 0;
        $hasAnyPrice = false;

        foreach ($selectedIds as $id) {
            $price = $this->preciosAnimales[$id] ?? null;
            if ($price !== null && $price !== '' && is_numeric($price)) {
                $total += (float) $price;
                $hasAnyPrice = true;
            }
        }

        if ($hasAnyPrice && $total > 0) {
            $this->montoVenta = number_format($total, 2, '.', '');
        }
    }

    public function distribuirMontoTotalEquitativo(): void
    {
        $selectedIds = collect($this->animalesAVender)->filter()->keys()->values();
        $count = $selectedIds->count();
        if ($count === 0 || ! is_numeric($this->montoVenta) || (float) $this->montoVenta <= 0) {
            return;
        }

        $total = (float) $this->montoVenta;
        $unitPrice = round($total / $count, 2);

        $accumulated = 0;
        foreach ($selectedIds as $index => $id) {
            if ($index === $count - 1) {
                $price = round($total - $accumulated, 2);
            } else {
                $price = $unitPrice;
                $accumulated += $price;
            }
            $this->preciosAnimales[$id] = number_format($price, 2, '.', '');
        }
    }

    public function distribuirMontoPorPeso(): void
    {
        if (! $this->selectedLote) {
            return;
        }

        $selectedIds = collect($this->animalesAVender)->filter()->keys()->values();
        if ($selectedIds->isEmpty() || ! is_numeric($this->montoVenta) || (float) $this->montoVenta <= 0) {
            return;
        }

        $activeRecords = $this->selectedLote->animales
            ->filter(fn ($ea) => $selectedIds->contains($ea->animal_id));

        $totalWeight = $activeRecords->sum(fn ($ea) => (float) ($ea->peso_actual ?: $ea->peso_inicial));
        if ($totalWeight <= 0) {
            $this->distribuirMontoTotalEquitativo();
            return;
        }

        $totalAmount = (float) $this->montoVenta;
        $pricePerKg = $totalAmount / $totalWeight;
        $accumulated = 0;
        $count = $activeRecords->count();
        $i = 0;

        foreach ($activeRecords as $ea) {
            $weight = (float) ($ea->peso_actual ?: $ea->peso_inicial);
            $i++;
            if ($i === $count) {
                $price = round($totalAmount - $accumulated, 2);
            } else {
                $price = round($weight * $pricePerKg, 2);
                $accumulated += $price;
            }
            $this->preciosAnimales[$ea->animal_id] = number_format($price, 2, '.', '');
        }
    }

    public function limpiarComprobanteVenta(): void
    {
        $this->comprobanteVenta = null;
    }

    public function liquidarVentaLote(): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $this->authorizePermission('finanzas', 'crear');

        $animalIds = collect($this->animalesAVender)->filter()->keys()->map(fn ($id) => (int) $id)->values()->all();

        if (empty($animalIds)) {
            throw ValidationException::withMessages([
                'animalesAVender' => 'Selecciona al menos un animal para la venta.',
            ]);
        }

        $this->preciosAnimales = collect($this->preciosAnimales)
            ->map(fn ($p) => ($p === '' || $p === null) ? null : $p)
            ->all();

        $this->validate([
            'fechaVenta' => ['required', 'date', 'before_or_equal:today'],
            'compradorVenta' => ['nullable', 'string', 'max:255'],
            'montoVenta' => ['required', 'numeric', 'gt:0', 'max:9999999.99'],
            'preciosAnimales.*' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'observacionesVenta' => ['nullable', 'string', 'max:1000'],
            'comprobanteVenta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:25600'],
        ], [
            'fechaVenta.required' => 'Selecciona la fecha de la venta.',
            'fechaVenta.before_or_equal' => 'La fecha de venta no puede ser futura.',
            'montoVenta.required' => 'Ingresa el monto total de la venta.',
            'montoVenta.numeric' => 'El monto debe ser un valor numérico.',
            'montoVenta.gt' => 'El monto debe ser mayor a 0.',
            'comprobanteVenta.mimes' => 'El comprobante debe ser una imagen (JPG, PNG, WebP) o un archivo PDF.',
            'comprobanteVenta.max' => 'El archivo no debe superar 25 MB.',
        ]);

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        DB::transaction(function () use ($animalIds, $fundoId): void {
            $lote = LoteEngorde::where('fundo_id', $fundoId)
                ->with(['animales.animal'])
                ->lockForUpdate()
                ->findOrFail($this->selectedLoteId);

            if ($lote->estado !== 'activo') {
                throw ValidationException::withMessages(['fechaVenta' => 'El lote ya se encuentra cerrado.']);
            }

            if (CarbonImmutable::parse($this->fechaVenta)->isBefore($lote->fecha_inicio->startOfDay())) {
                throw ValidationException::withMessages([
                    'fechaVenta' => 'La fecha de venta no puede ser anterior al inicio del lote ('.$lote->fecha_inicio->format('d/m/Y').').',
                ]);
            }

            $categoria = CategoriaFinanciera::firstOrCreate(
                ['fundo_id' => $fundoId, 'tipo' => 'ingreso', 'nombre' => 'Venta de Animales'],
                ['activo' => true]
            );

            $comprobantePath = null;
            if ($this->comprobanteVenta) {
                $isImage = str_starts_with((string) $this->comprobanteVenta->getMimeType(), 'image/');
                $comprobantePath = $isImage
                    ? ImageOptimizer::store(
                        $this->comprobanteVenta,
                        'comprobantes',
                        'comprobanteVenta',
                        1400,
                        900 * 1024,
                        'local'
                    )
                    : $this->comprobanteVenta->store('comprobantes', 'local');
            }

            // Build detailed breakdown
            $breakdown = [];
            foreach ($lote->animales->whereIn('animal_id', $animalIds) as $ea) {
                $code = $ea->animal?->arete ?: "ID {$ea->animal_id}";
                $p = $this->preciosAnimales[$ea->animal_id] ?? null;
                $breakdown[] = $p !== null && $p !== '' && is_numeric($p)
                    ? "{$code}: S/ " . number_format((float) $p, 2)
                    : $code;
            }

            $detalleTexto = ! empty($breakdown) ? ' (' . implode(', ', $breakdown) . ')' : '';
            $descripcion = '[VENTA LOTE ENGORDE: '.$lote->codigo.']' . $detalleTexto . ($this->observacionesVenta ? ' — ' . trim($this->observacionesVenta) : ' Venta y liquidación de animales en engorde.');

            $movimiento = Movimiento::create([
                'fundo_id' => $fundoId,
                'tipo' => 'ingreso',
                'categoria_id' => $categoria->id,
                'monto' => $this->montoVenta,
                'moneda' => 'PEN',
                'beneficiario' => trim($this->compradorVenta) ?: null,
                'proposito' => 'comercial',
                'fecha' => $this->fechaVenta,
                'descripcion' => $descripcion,
                'comprobante_ruta' => $comprobantePath,
            ]);

            app(AnimalInventoryService::class)->linkSale(
                $movimiento,
                $animalIds,
                trim($this->compradorVenta) ?: 'Sin especificar',
                $this->preciosAnimales
            );

            $remainingActive = EngordeAnimal::where('lote_id', $lote->id)
                ->where('estado', 'engorde_activo')
                ->whereNotIn('animal_id', $animalIds)
                ->exists();

            if (! $remainingActive) {
                $lote->update([
                    'estado' => 'cerrado',
                    'fecha_fin' => $this->fechaVenta,
                ]);
            }
        });

        $this->showVenderLoteModal = false;
        $this->reset(['selectedLoteId', 'selectedLote', 'fechaVenta', 'compradorVenta', 'montoVenta', 'observacionesVenta', 'comprobanteVenta', 'animalesAVender', 'preciosAnimales']);

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Venta Registrada en Finanzas',
            'text' => 'Se registraron los ingresos y se actualizó el estado de los animales vendidos.',
        ]);
    }

    public function openCerrarLoteModal(int $loteId): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $fundoId = (int) session('fundo_id');
        $lote = LoteEngorde::where('fundo_id', $fundoId)->findOrFail($loteId);
        abort_unless($lote->estado === 'activo', 422, 'El lote ya está cerrado.');

        $this->selectedLoteId = $loteId;
        $this->selectedLote = $lote;
        $this->fechaCierreLote = now()->format('Y-m-d');
        $this->observacionesCierreLote = '';
        $this->resetValidation();
        $this->showCerrarLoteModal = true;
    }

    public function closeCerrarLoteModal(): void
    {
        $this->showCerrarLoteModal = false;
        $this->reset(['selectedLoteId', 'selectedLote', 'fechaCierreLote', 'observacionesCierreLote']);
        $this->resetValidation();
    }

    /**
     * Called from the modal "Cerrar Lote" button — dispatches swal:confirm to ask once more.
     */
    public function solicitarCierreLote(): void
    {
        $this->authorizePermission('engorde', 'actualizar');

        // Validate first so the user sees form errors before the confirm dialog.
        $this->validate([
            'fechaCierreLote' => ['required', 'date', 'before_or_equal:today'],
            'observacionesCierreLote' => ['nullable', 'string', 'max:1000'],
        ], [
            'fechaCierreLote.required' => 'Selecciona la fecha de cierre.',
            'fechaCierreLote.before_or_equal' => 'La fecha de cierre no puede ser futura.',
        ]);

        $this->dispatch('swal:confirm', [
            'title'             => '¿Cerrar este lote?',
            'text'              => 'El lote se marcará como cerrado y se congelarán las métricas. Esta acción es reversible.',
            'icon'              => 'warning',
            'confirmButtonText' => 'Sí, cerrar lote',
            'cancelButtonText'  => 'Cancelar',
            'confirmButtonColor'=> '#10b981',
            'cancelButtonColor' => '#6b7280',
            'event'             => 'confirmarCierreLote',
            'id'                => $this->selectedLoteId,
        ]);
    }

    public function finalizarLoteConfirmado($payload = null): void
    {
        $this->authorizePermission('engorde', 'actualizar');

        $fundoId = (int) session('fundo_id');
        DB::transaction(function () use ($fundoId): void {
            $lote = LoteEngorde::where('fundo_id', $fundoId)->lockForUpdate()->findOrFail($this->selectedLoteId);
            if (CarbonImmutable::parse($this->fechaCierreLote)->isBefore($lote->fecha_inicio->startOfDay())) {
                throw ValidationException::withMessages([
                    'fechaCierreLote' => 'La fecha de cierre no puede ser anterior al inicio del lote ('.$lote->fecha_inicio->format('d/m/Y').').',
                ]);
            }

            $lote->update([
                'estado'        => 'cerrado',
                'fecha_fin'     => $this->fechaCierreLote,
                'observaciones' => $this->observacionesCierreLote
                    ? ($lote->observaciones ? $lote->observaciones."\n[CIERRE]: ".$this->observacionesCierreLote : $this->observacionesCierreLote)
                    : $lote->observaciones,
            ]);

            EngordeAnimal::where('lote_id', $lote->id)
                ->where('estado', 'engorde_activo')
                ->whereNull('fecha_salida')
                ->update(['fecha_salida' => $this->fechaCierreLote]);
        });

        $this->showCerrarLoteModal = false;
        $this->reset(['selectedLoteId', 'selectedLote', 'fechaCierreLote', 'observacionesCierreLote']);

        $this->dispatch('swal:toast', [
            'icon'  => 'success',
            'title' => 'Lote Finalizado',
            'text'  => 'El lote de engorde ha sido cerrado exitosamente.',
        ]);
    }

    /** @deprecated Use solicitarCierreLote + finalizarLoteConfirmado in UI. Kept for backwards compat. */
    public function finalizarLote(): void
    {
        $this->validate([
            'fechaCierreLote' => ['required', 'date', 'before_or_equal:today'],
            'observacionesCierreLote' => ['nullable', 'string', 'max:1000'],
        ], [
            'fechaCierreLote.required' => 'Selecciona la fecha de cierre.',
            'fechaCierreLote.before_or_equal' => 'La fecha de cierre no puede ser futura.',
        ]);

        $this->finalizarLoteConfirmado();
    }

    public function solicitarReabrirLote(int $loteId): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $fundoId = (int) session('fundo_id');
        $lote = LoteEngorde::where('fundo_id', $fundoId)->findOrFail($loteId);
        abort_unless($lote->estado === 'cerrado', 422, 'El lote no está cerrado.');

        $this->dispatch('swal:confirm', [
            'title'             => '¿Reabrir este lote?',
            'text'              => 'El lote «'.$lote->codigo.'» volverá a estado activo para nuevos pesajes y controles.',
            'icon'              => 'question',
            'confirmButtonText' => 'Sí, reabrir lote',
            'cancelButtonText'  => 'Cancelar',
            'confirmButtonColor'=> '#3b82f6',
            'cancelButtonColor' => '#6b7280',
            'event'             => 'confirmarReabrirLote',
            'id'                => $loteId,
        ]);
    }

    public function reabrirLoteConfirmado($payload = null): void
    {
        $this->authorizePermission('engorde', 'actualizar');
        $id = is_array($payload) ? ($payload['id'] ?? null) : $payload;
        if (! $id) {
            return;
        }

        $fundoId = (int) session('fundo_id');
        $lote = LoteEngorde::where('fundo_id', $fundoId)->findOrFail((int) $id);
        abort_unless($lote->estado === 'cerrado', 422, 'El lote no está cerrado.');

        DB::transaction(function () use ($lote): void {
            $lote->update([
                'estado'    => 'activo',
                'fecha_fin' => null,
            ]);

            EngordeAnimal::where('lote_id', $lote->id)
                ->where('estado', 'engorde_activo')
                ->update(['fecha_salida' => null]);
        });

        $this->dispatch('swal:toast', [
            'icon'  => 'success',
            'title' => 'Lote Reabierto',
            'text'  => 'El lote vuelve a estar en estado activo para nuevos controles.',
        ]);
    }

    /** @deprecated Use solicitarReabrirLote + reabrirLoteConfirmado in UI. Kept for backwards compat. */
    public function reabrirLote(int $loteId): void
    {
        $this->reabrirLoteConfirmado(['id' => $loteId]);
    }

    public function exportar($columns = null)
    {
        $this->authorizePermission('engorde', 'exportar');

        if (is_array($columns)) {
            $this->selectedColumns = $columns;
        }

        $this->validate([
            'selectedColumns' => ['required', 'array', 'min:1'],
            'selectedColumns.*' => ['required', 'string', 'distinct', Rule::in(array_keys($this->availableColumns))],
        ], [
            'selectedColumns.required' => 'Selecciona al menos una columna para el reporte.',
            'selectedColumns.min' => 'Selecciona al menos una columna para el reporte.',
            'selectedColumns.*.in' => 'La selección contiene una columna no válida.',
            'selectedColumns.*.distinct' => 'No se pueden repetir columnas.',
        ]);

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $selectedColumns = array_values(array_intersect(array_keys($this->availableColumns), $this->selectedColumns));
        $lotes = $this->lotesQuery($fundoId)->get();
        $fundo = Fundo::findOrFail($fundoId);
        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $reportSummary = $lotes->count().' lotes, '.(int) $lotes->sum('animales_count').' animales vinculados. Columnas: '.collect($selectedColumns)
            ->map(fn ($column) => $this->availableColumns[$column])
            ->join(', ', ' y ').'.';
        $filterSummary = $this->filterSummary();

        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.engorde', compact(
            'lotes',
            'selectedColumns',
            'fundo',
            'generatedBy',
            'generatedAt',
            'administrators',
            'reportSummary',
            'filterSummary',
            'includeSignatures',
            'scale'
        ))->setPaper('a4', 'landscape');

        $this->activeExportType = 'summary';

        return $this->setPdfPreview(
            $pdf,
            'lotes_engorde_'.now()->format('Ymd_His').'.pdf',
            'Resumen de Lotes de Engorde',
            $lotes->count()
        );
    }

    public function openDetailedReportModal(): void
    {
        $this->authorizePermission('engorde', 'exportar');
        $this->resetValidation([
            'detailedReportScope',
            'detailedReportLotIds',
            'detailedReportColumns',
            'detailedReportColumns.*',
        ]);
        $this->showDetailedReportModal = true;
    }

    public function exportDetailedReport($scope = null, $lotIds = null, $columns = null)
    {
        $this->authorizePermission('engorde', 'exportar');
        $this->activeExportType = 'detailed';

        if (is_string($scope)) {
            $this->detailedReportScope = $scope;
        }
        if (is_array($lotIds)) {
            $this->detailedReportLotIds = $lotIds;
        }
        if (is_array($columns)) {
            $this->detailedReportColumns = $columns;
        }

        $this->validate([
            'detailedReportScope' => ['required', Rule::in(['filtered', 'selected'])],
            'detailedReportLotIds' => ['array', Rule::requiredIf($this->detailedReportScope === 'selected')],
            'detailedReportLotIds.*' => ['integer', 'distinct'],
            'detailedReportColumns' => ['required', 'array', 'min:1'],
            'detailedReportColumns.*' => ['required', 'string', 'distinct', Rule::in(array_keys(EngordeReport::COLUMNS))],
        ], [
            'detailedReportLotIds.required' => 'Selecciona al menos un lote.',
            'detailedReportColumns.required' => 'Selecciona al menos una columna.',
            'detailedReportColumns.min' => 'Selecciona al menos una columna.',
            'detailedReportColumns.*.in' => 'La selección contiene una columna no válida.',
        ]);

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $eligibleQuery = $this->lotesQuery($fundoId);

        if ($this->detailedReportScope === 'selected') {
            $requestedIds = collect($this->detailedReportLotIds)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();
            $eligibleIds = (clone $eligibleQuery)->whereIn('id', $requestedIds)->pluck('id');

            if ($requestedIds->isEmpty() || $eligibleIds->count() !== $requestedIds->count()) {
                throw ValidationException::withMessages([
                    'detailedReportLotIds' => 'Uno o más lotes no pertenecen al fundo o no cumplen los filtros actuales.',
                ]);
            }
        } else {
            $eligibleIds = $eligibleQuery->pluck('id');
        }

        if ($eligibleIds->isEmpty()) {
            throw ValidationException::withMessages([
                'detailedReportLotIds' => 'No hay lotes para generar el reporte.',
            ]);
        }

        $lots = EngordeReport::loadLots($fundoId, $eligibleIds->all());
        $summary = EngordeReport::summarize($lots);
        if ($summary['animals'] > EngordeReport::MAX_ANIMALS) {
            throw ValidationException::withMessages([
                'detailedReportLotIds' => 'Reporte admite hasta 1,000 animales. Reduce selección de lotes.',
            ]);
        }

        $selectedColumns = EngordeReport::normalizeColumns($this->detailedReportColumns);
        $fundo = Fundo::findOrFail($fundoId);
        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $reportSummary = $summary['lots'].' lotes, '.$summary['animals'].' animales. Peso inicial: '.number_format($summary['initial_weight'], 2).' kg. Peso de referencia: '.number_format($summary['reference_weight'], 2).' kg. Ganancia: '.number_format($summary['gain_kg'], 2).' kg.';
        $selectionSummary = $this->detailedReportScope === 'filtered'
            ? 'Todos los resultados filtrados'
            : 'Lotes elegidos: '.$lots->pluck('codigo')->join(', ');
        $filterSummary = $selectionSummary.' | '.$this->filterSummary();
        $title = 'Reporte general detallado de engorde';
        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.engorde-detallado', compact(
            'lots',
            'selectedColumns',
            'summary',
            'fundo',
            'generatedBy',
            'generatedAt',
            'administrators',
            'reportSummary',
            'filterSummary',
            'title',
            'includeSignatures',
            'scale'
        ))->setPaper('a4', 'landscape');

        // Solo cerrar el modal de opciones la PRIMERA vez (no al regenerar desde preview).
        if ($this->exportStep !== 'preview') {
            $this->showDetailedReportModal = false;
        }
        return $this->setPdfPreview(
            $pdf,
            Str::slug('reporte_general_engorde_'.now()->format('Ymd_His'), '_').'.pdf',
            $title,
            $lots->count()
        );
    }

    public function sort(string $column): void
    {
        $allowed = ['id', 'codigo', 'nombre', 'fecha_inicio', 'estado'];
        if (! in_array($column, $allowed, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = in_array($column, ['id', 'fecha_inicio'], true) ? 'desc' : 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        // Livewire deshydrates/rehydrates $selectedLote without eager relations.
        // Reload animales.animal so the blade never lazy-loads.
        if ($this->showVenderLoteModal && $this->selectedLote instanceof LoteEngorde) {
            $this->selectedLote->loadMissing('animales.animal');
        }

        $fundoId = (int) session('fundo_id');
        $perPage = in_array((int) $this->perPage, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPage : 10;
        $sortBy = in_array($this->sortBy, ['id', 'codigo', 'nombre', 'fecha_inicio', 'estado'], true) ? $this->sortBy : 'fecha_inicio';
        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $lotes = $this->pinRecent($this->lotesQuery($fundoId), 'engorde.lotes')
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir)
            ->paginate($perPage);

        $loteStats = LoteEngorde::where('fundo_id', $fundoId)
            ->selectRaw("SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) AS activos")
            ->selectRaw("SUM(CASE WHEN estado = 'cerrado' THEN 1 ELSE 0 END) AS cerrados")
            ->first();

        $animalesActivos = EngordeAnimal::whereHas('lote', fn ($lote) => $lote->where('fundo_id', $fundoId))
            ->where('estado', 'engorde_activo')
            ->count();

        $animalesCerrados = EngordeAnimal::whereHas('lote', fn ($lote) => $lote->where('fundo_id', $fundoId))
            ->where('estado', '!=', 'engorde_activo')
            ->count();

        $totalAnimalesHistorico = $animalesActivos + $animalesCerrados;

        // Dashboard Monthly Trend (Animales ingresados por mes)
        /*
         * OPTIMIZACIÓN DE RENDIMIENTO:
         * Las agregaciones del mini-dashboard (altas mensuales por género y
         * breakdowns) se calculan en BD con GROUP BY y se cachean 5 minutos.
         * Antes se cargaban 12 meses de ingresos completos a memoria.
         */
        $dashCacheKey = 'engorde.dashboard.v2.'.$fundoId;

        [$monthlyData, $estadosAnimales, $sexoData] = Cache::remember($dashCacheKey, now()->addMinutes(5), function () use ($fundoId, $totalAnimalesHistorico): array {
            $monthlyRaw = EngordeAnimal::whereHas('lote', fn ($q) => $q->where('fundo_id', $fundoId))
                ->where('fecha_ingreso', '>=', now()->subMonths(12)->startOfMonth())
                ->join('animales', 'engorde_animales.animal_id', '=', 'animales.id')
                ->selectRaw("substr(engorde_animales.fecha_ingreso, 1, 7) as period, animales.genero, COUNT(*) as count")
                ->groupBy('period', 'animales.genero')
                ->get()
                ->keyBy(fn ($item) => $item->period.'|'.$item->genero);

            $monthsList = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));
            $monthsEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $fullMonthsEs = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

            $monthlyData = $monthsList->map(function ($period) use ($monthlyRaw, $monthsEs, $fullMonthsEs) {
                $dt = CarbonImmutable::createFromFormat('Y-m', $period);
                $hembras = (int) ($monthlyRaw->get($period.'|hembra')->count ?? 0);
                $machos = (int) ($monthlyRaw->get($period.'|macho')->count ?? 0);

                return [
                    'period' => $period,
                    'label' => $monthsEs[$dt->month - 1].' '.$dt->format('y'),
                    'fullLabel' => $fullMonthsEs[$dt->month - 1].' '.$dt->year,
                    'count' => $hembras + $machos,
                    'hembras' => $hembras,
                    'machos' => $machos,
                ];
            })->values()->all();

            $withPercentage = fn ($rows) => collect($rows)->map(function ($item) use ($totalAnimalesHistorico) {
                $item['percentage'] = $totalAnimalesHistorico > 0 ? round(($item['count'] / $totalAnimalesHistorico) * 100, 1) : 0;

                return $item;
            })->all();

            // Breakdowns
            $estadosAnimales = $withPercentage(EngordeAnimal::whereHas('lote', fn ($lote) => $lote->where('fundo_id', $fundoId))
                ->selectRaw("COALESCE(NULLIF(estado, ''), 'Desconocido') as label, COUNT(*) as count")
                ->groupBy('label')
                ->orderBy('count', 'desc')
                ->get()
                ->map(fn ($item) => [
                    'label' => ucfirst(str_replace('_', ' ', $item->label)),
                    'count' => (int) $item->count,
                ])
                ->all());

            $sexoData = $withPercentage(EngordeAnimal::whereHas('lote', fn ($lote) => $lote->where('fundo_id', $fundoId))
                ->join('animales', 'engorde_animales.animal_id', '=', 'animales.id')
                ->selectRaw("COALESCE(NULLIF(animales.genero, ''), 'Sin Registro') as label, COUNT(*) as count")
                ->groupBy('label')
                ->orderBy('count', 'desc')
                ->get()
                ->map(fn ($item) => ['label' => ucfirst($item->label), 'count' => (int) $item->count])
                ->all());

            return [$monthlyData, $estadosAnimales, $sexoData];
        });

        $dashboardData = [
            'generatedAt' => now()->format('H:i'),
            'totalLotes' => (int) (($loteStats->activos ?? 0) + ($loteStats->cerrados ?? 0)),
            'lotesActivos' => (int) ($loteStats->activos ?? 0),
            'animalesActivos' => $animalesActivos,
            'animalesCerrados' => $animalesCerrados,
            'monthly' => $monthlyData,
            'estadosAnimales' => $estadosAnimales,
            'sexoAnimales' => $sexoData,
        ];

        $dateBounds = LoteEngorde::query()
            ->where('fundo_id', $fundoId)
            ->selectRaw('MIN(fecha_inicio) as min_date, MAX(fecha_inicio) as max_date')
            ->first();
        $firstYear = $dateBounds?->min_date
            ? CarbonImmutable::parse($dateBounds->min_date)->year
            : now()->year;
        $lastYear = max(
            now()->year,
            $dateBounds?->max_date ? CarbonImmutable::parse($dateBounds->max_date)->year : now()->year
        );
        $availableYears = range($lastYear, $firstYear);
        $hasActiveFilters = collect([
            $this->search,
            $this->estado,
            $this->periodo,
            $this->anio,
            $this->mes,
            $this->fechaDesde,
            $this->fechaHasta,
        ])->contains(fn ($value) => $value !== '' && $value !== null);
        $detailedReportLots = $this->showDetailedReportModal
            ? $this->lotesQuery($fundoId)->orderByDesc('fecha_inicio')->orderByDesc('id')->get()
            : collect();
        $detailedReportAvailableColumns = EngordeReport::COLUMNS;

        return view('livewire.engorde.index', compact(
            'lotes',
            'loteStats',
            'animalesActivos',
            'dashboardData',
            'availableYears',
            'hasActiveFilters',
            'detailedReportLots',
            'detailedReportAvailableColumns'
        ))
            ->layout('layouts.app');
    }

    private function lotesQuery(int $fundoId): Builder
    {
        [$fechaDesde, $fechaHasta] = $this->effectiveDateRange();

        return LoteEngorde::query()
            ->where('fundo_id', $fundoId)
            ->withCount('animales')
            ->when(trim((string) $this->search) !== '', function (Builder $query) {
                $search = trim((string) $this->search);
                $query->where(fn (Builder $filter) => $filter
                    ->where('codigo', 'like', '%'.$search.'%')
                    ->orWhere('nombre', 'like', '%'.$search.'%'));
            })
            ->when($this->estado !== '', fn (Builder $query) => $query->where('estado', $this->estado))
            ->when($fechaDesde, fn (Builder $query) => $query->where('fecha_inicio', '>=', $fechaDesde))
            ->when($fechaHasta, fn (Builder $query) => $query->where('fecha_inicio', '<', $this->exclusiveEndDate($fechaHasta)));
    }

    private function effectiveDateRange(): array
    {
        $today = CarbonImmutable::today();

        if ($this->periodo !== '') {
            return match ($this->periodo) {
                'hoy' => [$today->toDateString(), $today->toDateString()],
                'ultimos_7_dias' => [$today->subDays(6)->toDateString(), $today->toDateString()],
                'semana_actual' => [$today->startOfWeek()->toDateString(), $today->toDateString()],
                'mes_actual' => [$today->startOfMonth()->toDateString(), $today->toDateString()],
                'mes_anterior' => [
                    $today->subMonthNoOverflow()->startOfMonth()->toDateString(),
                    $today->subMonthNoOverflow()->endOfMonth()->toDateString(),
                ],
                'trimestre_actual' => [$today->startOfQuarter()->toDateString(), $today->toDateString()],
                'anio_actual' => [$today->startOfYear()->toDateString(), $today->toDateString()],
                default => [null, null],
            };
        }

        $year = filter_var($this->anio, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1900, 'max_range' => 2200],
        ]);
        $month = filter_var($this->mes, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 12],
        ]);

        if ($year !== false) {
            $start = CarbonImmutable::create($year, $month !== false ? $month : 1, 1);
            $end = $month !== false ? $start->endOfMonth() : $start->endOfYear();

            return [$start->toDateString(), $end->toDateString()];
        }

        return [$this->validDate($this->fechaDesde), $this->validDate($this->fechaHasta)];
    }

    protected function recentRecordScopes(): array
    {
        return [
            'engorde.lotes' => ['model' => LoteEngorde::class],
        ];
    }

    private function validDate($value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $value);

            return $date && $date->format('Y-m-d') === $value ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function exclusiveEndDate(string $date): string
    {
        return CarbonImmutable::parse($date)->addDay()->toDateString();
    }

    private function periodLabel(): ?string
    {
        return match ($this->periodo) {
            'hoy' => 'Hoy',
            'ultimos_7_dias' => 'Últimos 7 días',
            'semana_actual' => 'Semana actual',
            'mes_actual' => 'Mes actual',
            'mes_anterior' => 'Mes anterior',
            'trimestre_actual' => 'Trimestre actual',
            'anio_actual' => 'Año actual',
            default => null,
        };
    }

    private function monthLabel(): ?string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return $months[(int) $this->mes] ?? null;
    }

    private function filterSummary(): string
    {
        [$fechaDesde, $fechaHasta] = $this->effectiveDateRange();
        $dateFilter = $this->periodLabel();

        if (! $dateFilter && $this->anio !== '') {
            $dateFilter = $this->mes !== ''
                ? $this->monthLabel().' de '.$this->anio
                : 'Año '.$this->anio;
        }
        if (! $dateFilter && ($fechaDesde || $fechaHasta)) {
            $dateFilter = ($fechaDesde ?: 'Inicio').' al '.($fechaHasta ?: 'Hoy');
        }

        return collect([
            'Búsqueda' => trim((string) $this->search) ?: null,
            'Estado' => match ($this->estado) {
                'activo' => 'Activo',
                'cerrado' => 'Cerrado',
                default => null,
            },
            'Periodo de inicio' => $dateFilter,
        ])->filter()->map(fn ($value, $name) => "{$name}: {$value}")->implode(' | ') ?: 'Sin filtros adicionales';
    }
}
