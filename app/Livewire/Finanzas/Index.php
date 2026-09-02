<?php

namespace App\Livewire\Finanzas;

use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Movimiento;
use App\Traits\AuthorizesPermissions;
use App\Support\PaginationOptions;
use App\Traits\HasPdfPreviewModal;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal, HasRecentRecord, WithPagination;

    private const PDF_MAX_ROWS = 500;

    private const REPORT_SECTIONS = [
        'movimientos' => [
            'summary' => ['label' => 'Resumen financiero', 'description' => 'Totales y balance del periodo'],
            'records' => ['label' => 'Movimientos', 'description' => 'Registros esenciales de caja'],
            'categories' => ['label' => 'Categorías', 'description' => 'Totales agrupados por categoría'],
        ],
    ];

    private const REPORT_COLUMNS = [
        'movimientos' => [
            'summary' => [
                'period' => 'Periodo',
                'records' => 'Movimientos',
                'income' => 'Ingresos',
                'expenses' => 'Egresos',
                'balance' => 'Balance',
            ],
            'records' => [
                'date' => 'Fecha',
                'type' => 'Tipo',
                'category' => 'Categoría',
                'description' => 'Descripción',
                'amount' => 'Monto',
            ],
            'categories' => [
                'category' => 'Categoría',
                'type' => 'Tipo',
                'records' => 'Registros',
                'total' => 'Total',
            ],
        ],
    ];

    public string $searchMovimiento = '';

    public string $tipoMovimiento = '';

    public string $categoriaMovimiento = '';

    public string $periodoMovimiento = '';

    public string $fechaDesdeMovimiento = '';

    public string $fechaHastaMovimiento = '';

    public string $montoMinMovimiento = '';

    public string $montoMaxMovimiento = '';

    public string $conComprobante = '';

    public int $perPage = 10;

    public string $sortBy = 'fecha';

    public string $sortDir = 'desc';

    private const SORTABLE_COLUMNS = ['id', 'fecha', 'tipo', 'monto', 'descripcion'];

    public string $tab = 'movimientos';

    public bool $showReportModal = false;

    public string $reportType = 'movimientos';

    public array $selectedReportSections = ['summary'];

    public array $reportColumns = [];

    protected $queryString = [
        'tab' => ['except' => 'movimientos'],
        'searchMovimiento' => ['except' => ''],
        'tipoMovimiento' => ['except' => ''],
        'categoriaMovimiento' => ['except' => ''],
        'periodoMovimiento' => ['except' => ''],
        'fechaDesdeMovimiento' => ['except' => ''],
        'fechaHastaMovimiento' => ['except' => ''],
        'montoMinMovimiento' => ['except' => ''],
        'montoMaxMovimiento' => ['except' => ''],
        'conComprobante' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'confirmarEliminacionMovimiento' => 'deleteMovimiento',
    ];

    private const PER_PAGE_OPTIONS = PaginationOptions::PER_PAGE;

    private const MOVEMENT_FILTERS = [
        'searchMovimiento', 'tipoMovimiento', 'categoriaMovimiento',
        'periodoMovimiento', 'fechaDesdeMovimiento', 'fechaHastaMovimiento',
        'montoMinMovimiento', 'montoMaxMovimiento', 'conComprobante',
    ];

    public function mount(string $tab = 'movimientos'): void
    {
        $this->tab = in_array($tab, ['movimientos', 'asignaciones'], true) ? $tab : 'movimientos';
        $this->reportType = 'movimientos';
        $this->reportColumns = $this->defaultReportColumns($this->reportType);
    }

    public function reportSectionOptions(): array
    {
        return self::REPORT_SECTIONS[$this->reportType] ?? self::REPORT_SECTIONS['movimientos'];
    }

    public function reportColumnOptions(): array
    {
        return self::REPORT_COLUMNS[$this->reportType] ?? self::REPORT_COLUMNS['movimientos'];
    }

    public function openReportModal(): void
    {
        $this->authorizePermission('finanzas', 'exportar');
        $this->reportType = 'movimientos';
        $this->selectedReportSections = ['summary'];
        $this->reportColumns = $this->defaultReportColumns($this->reportType);
        $this->resetValidation();
        $this->showReportModal = true;
    }

    public function downloadReport(?array $selectedSections = null, ?array $columns = null)
    {
        if ($selectedSections !== null) {
            $this->selectedReportSections = $selectedSections;
        }
        if ($columns !== null) {
            $this->reportColumns = $columns;
        }

        $this->authorizePermission('finanzas', 'exportar');
        [$sections, $selectedColumns] = $this->validatedReportSelection();
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $records = $this->movementQuery($fundoId)->orderByDesc('fecha')->orderByDesc('id')->limit(self::PDF_MAX_ROWS + 1)->get();

        if ($records->count() > self::PDF_MAX_ROWS) {
            $this->addError('selectedReportSections', 'El PDF admite hasta 500 registros. Aplica filtros para reducir el reporte.');

            return null;
        }

        $report = $this->buildFinanceReport($records, $sections, $selectedColumns);
        $fundo = Fundo::withoutGlobalScopes()->findOrFail($fundoId);
        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        if ($this->exportStep !== 'preview') {
            $this->showReportModal = false;
        }

        $pdf = Pdf::loadView('pdf.finance-index', compact(
            'report', 'fundo', 'generatedBy', 'generatedAt', 'administrators', 'records', 'includeSignatures', 'scale'
        ))->setPaper('a4', 'landscape');

        return $this->setPdfPreview(
            $pdf,
            'reporte_'.$this->reportType.'_'.now()->format('Ymd_His').'.pdf',
            'Reporte Financiero de Movimientos',
            $records->count()
        );
    }

    public function updated($property): void
    {
        if (in_array($property, self::MOVEMENT_FILTERS, true)) {
            $this->resetPage('movimientosPage');
        }
    }

    public function updatedTipoMovimiento(): void
    {
        $this->categoriaMovimiento = '';
        $this->resetPage('movimientosPage');
    }

    public function updatedPeriodoMovimiento($value): void
    {
        if ($value !== '') {
            $this->fechaDesdeMovimiento = '';
            $this->fechaHastaMovimiento = '';
        }
    }

    public function updatedFechaDesdeMovimiento($value): void
    {
        if ($value !== '') {
            $this->periodoMovimiento = '';
        }
    }

    public function updatedFechaHastaMovimiento($value): void
    {
        if ($value !== '') {
            $this->periodoMovimiento = '';
        }
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, self::PER_PAGE_OPTIONS, true) ? (int) $value : 10;
        $this->resetPage('movimientosPage');
    }

    public function resetMovimientoFilters(): void
    {
        $this->reset(self::MOVEMENT_FILTERS);
        $this->resetPage('movimientosPage');
    }

    public function solicitarEliminacionMovimiento($id): void
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar movimiento?',
            'text' => 'El movimiento será archivado y su comprobante dejará de estar disponible.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionMovimiento',
            'id' => $id,
        ]);
    }

    public function deleteMovimiento(
        \App\Services\MedicamentoPurchaseService $medPurchases,
        \App\Services\InsumoPurchaseService $insPurchases,
        $id = null
    ): void {
        $this->authorizePermission('finanzas', 'eliminar');
        $id = is_array($id) ? ($id['id'] ?? null) : $id;
        $movimiento = Movimiento::query()
            ->where('fundo_id', session('fundo_id'))
            ->find($id);

        if (! $movimiento) {
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($movimiento, $medPurchases, $insPurchases) {
            $insumoLot = \App\Models\InsumoLote::query()->where('movimiento_id', $movimiento->id)->first();
            if ($insumoLot) {
                $used = $insPurchases->usedQuantity($insumoLot);
                if ($used > 0) {
                    $insumoLot->update(['movimiento_id' => null]);
                } else {
                    $insumoLot->movimientosInventario()->delete();
                    $insumoLot->delete();
                }
            }

            $medLot = \App\Models\MedicamentoLote::query()->where('movimiento_id', $movimiento->id)->first();
            if ($medLot) {
                $used = $medPurchases->usedQuantity($medLot);
                if ($used > 0) {
                    $medLot->update(['movimiento_id' => null]);
                } else {
                    $medLot->movimientos()->delete();
                    $medLot->delete();
                }
            }

            $receipt = $movimiento->comprobante_ruta;
            $movimiento->delete();

            if ($receipt) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($receipt);
            }
        });

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Movimiento eliminado',
            'text' => 'El movimiento y sus datos vinculados fueron eliminados correctamente.',
        ]);
    }

    public function sort(string $column): void
    {
        if (! in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = in_array($column, ['id', 'fecha', 'monto'], true) ? 'desc' : 'asc';
        }

        $this->resetPage('movimientosPage');
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $perPage = in_array($this->perPage, self::PER_PAGE_OPTIONS, true) ? $this->perPage : 10;
        $sortBy = in_array($this->sortBy, self::SORTABLE_COLUMNS, true) ? $this->sortBy : 'fecha';
        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        $movementQuery = $this->movementQuery($fundoId);

        $movimientos = $this->pinRecent($movementQuery, 'finanzas.movimientos')
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir)
            ->paginate($perPage, ['*'], 'movimientosPage');

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();
        $movementStats = Movimiento::query()
            ->where('fundo_id', $fundoId)
            ->whereBetween('fecha', [$monthStart, $monthEnd])
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END), 0) AS ingresos")
            ->selectRaw("COALESCE(SUM(CASE WHEN tipo = 'egreso' THEN monto ELSE 0 END), 0) AS egresos")
            ->first();
        $ingresosMes = (float) ($movementStats?->ingresos ?? 0);
        $egresosMes = (float) ($movementStats?->egresos ?? 0);
        $balanceMes = $ingresosMes - $egresosMes;

        $categorias = CategoriaFinanciera::query()
            ->where('activo', true)
            ->when($this->tipoMovimiento !== '', fn (Builder $query) => $query->where('tipo', $this->tipoMovimiento))
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get(['id', 'tipo', 'nombre']);

        $hasMovementFilters = collect(self::MOVEMENT_FILTERS)->contains(
            fn ($property) => (string) $this->{$property} !== ''
        );
        $dashboardData = $this->dashboardData($fundoId);
        $reportSectionOptions = $this->reportSectionOptions();
        $reportColumnOptions = $this->reportColumnOptions();

        return view('livewire.finanzas.index', compact(
            'movimientos',
            'categorias',
            'ingresosMes',
            'egresosMes',
            'balanceMes',
            'hasMovementFilters',
            'dashboardData',
            'reportSectionOptions',
            'reportColumnOptions'
        ))->layout('layouts.app');
    }

    private function validatedReportSelection(): array
    {
        $this->validate([
            'reportType' => ['required', Rule::in(['movimientos'])],
        ]);
        $sections = self::REPORT_SECTIONS[$this->reportType];
        $columns = self::REPORT_COLUMNS[$this->reportType];
        $rules = [
            'selectedReportSections' => ['required', 'array', 'min:1'],
            'selectedReportSections.*' => ['required', 'string', 'distinct', Rule::in(array_keys($sections))],
        ];
        foreach ($this->selectedReportSections as $section) {
            if (! isset($columns[$section])) {
                continue;
            }
            $rules['reportColumns.'.$section] = ['required', 'array', 'min:1'];
            $rules['reportColumns.'.$section.'.*'] = [
                'required', 'string', 'distinct', Rule::in(array_keys($columns[$section])),
            ];
        }
        $this->validate($rules, [
            'selectedReportSections.required' => 'Selecciona al menos una sección.',
            'selectedReportSections.min' => 'Selecciona al menos una sección.',
            'selectedReportSections.*.in' => 'La selección contiene una sección no válida.',
            'reportColumns.*.required' => 'Selecciona al menos un campo para esta sección.',
            'reportColumns.*.min' => 'Selecciona al menos un campo para esta sección.',
            'reportColumns.*.*.in' => 'La selección contiene un campo no válido.',
        ]);

        $selectedSections = array_values(array_intersect(array_keys($sections), $this->selectedReportSections));
        $selectedColumns = [];
        foreach ($selectedSections as $section) {
            $selectedColumns[$section] = array_values(array_intersect(
                array_keys($columns[$section]),
                $this->reportColumns[$section] ?? []
            ));
        }

        return [$selectedSections, $selectedColumns];
    }

    private function defaultReportColumns(string $type): array
    {
        return collect(self::REPORT_COLUMNS[$type] ?? self::REPORT_COLUMNS['movimientos'])
            ->map(fn (array $columns) => array_keys($columns))
            ->all();
    }

    private function buildFinanceReport(Collection $records, array $sections, array $selectedColumns): array
    {
        $type = $this->reportType;
        $period = $this->reportPeriod($records);
        $currency = fn (float $value) => 'S/. '.number_format($value, 2);

        if ($type === 'movimientos') {
            $income = (float) $records->where('tipo', 'ingreso')->sum('monto');
            $expenses = (float) $records->where('tipo', 'egreso')->sum('monto');
            $summary = [
                'period' => $period,
                'records' => number_format($records->count()),
                'income' => $currency($income),
                'expenses' => $currency($expenses),
                'balance' => $currency($income - $expenses),
            ];
            $rows = $records->map(fn (Movimiento $movement) => [
                'date' => $movement->fecha->format('d/m/Y'),
                'type' => ucfirst($movement->tipo),
                'category' => $movement->categoria?->nombre ?? 'Sin categoría',
                'description' => $movement->descripcion ?: '-',
                'amount' => ($movement->tipo === 'ingreso' ? '+' : '-').' '.$currency((float) $movement->monto),
            ])->all();
            $aggregates = $records
                ->groupBy(fn (Movimiento $movement) => $movement->tipo.'|'.($movement->categoria?->nombre ?? 'Sin categoría'))
                ->map(function (Collection $group) use ($currency) {
                    /** @var Movimiento $first */
                    $first = $group->first();

                    return [
                        'category' => $first->categoria?->nombre ?? 'Sin categoría',
                        'type' => ucfirst($first->tipo),
                        'records' => number_format($group->count()),
                        'total' => $currency((float) $group->sum('monto')),
                    ];
                })->sortByDesc(fn (array $row) => (float) str_replace([',', 'S/. '], '', $row['total']))->values()->all();
        }

        return [
            'type' => $type,
            'title' => 'Reporte de movimientos de caja',
            'subtitle' => $this->reportFilterSummary($type),
            'sections' => $sections,
            'selectedColumns' => $selectedColumns,
            'sectionOptions' => self::REPORT_SECTIONS[$type],
            'columnOptions' => self::REPORT_COLUMNS[$type],
            'summary' => $summary,
            'rows' => $rows,
            'aggregates' => $aggregates,
        ];
    }

    private function reportPeriod(Collection $records): string
    {
        if ($records->isEmpty()) {
            return 'Sin registros';
        }

        $dates = $records->pluck('fecha')->sort();

        return $dates->first()->format('d/m/Y').' al '.$dates->last()->format('d/m/Y');
    }

    private function reportFilterSummary(string $type): string
    {
        $active = collect(self::MOVEMENT_FILTERS)->filter(fn (string $property) => (string) $this->{$property} !== '')->count();

        return $active > 0
            ? "Datos según {$active} filtro(s) activo(s) en la tabla."
            : 'Todo el historial disponible del fundo.';
    }

    private function dashboardData(int $fundoId): array
    {
        return Cache::remember("finanzas.dashboard.{$fundoId}", now()->addMinutes(2), function () use ($fundoId) {
            $end = CarbonImmutable::today()->startOfMonth();
            $start = $end->subMonthsNoOverflow(17);
            $movements = Movimiento::query()
                ->where('fundo_id', $fundoId)
                ->whereDate('fecha', '>=', $start->toDateString())
                ->with('categoria:id,nombre')
                ->get(['id', 'categoria_id', 'tipo', 'monto', 'fecha', 'beneficiario', 'proposito']);
            $movementsByMonth = $movements->groupBy(fn (Movimiento $movement) => $movement->fecha->format('Y-m'));
            $months = [];
            $monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            for ($month = $start; $month->lessThanOrEqualTo($end); $month = $month->addMonth()) {
                $monthMovements = $movementsByMonth->get($month->format('Y-m'), collect());
                // Las asignaciones familiares viven dentro de los movimientos (categoría Asignación Familiar)
                $monthAssignments = $monthMovements
                    ->where('tipo', 'egreso')
                    ->filter(fn (Movimiento $movement) => $this->esCategoriaAsignacionFamiliar($movement->categoria?->nombre ?? ''));
                $income = (float) $monthMovements->where('tipo', 'ingreso')->sum('monto');
                $expenses = (float) $monthMovements->where('tipo', 'egreso')->sum('monto');
                $expenseCategories = $monthMovements->where('tipo', 'egreso')
                    ->groupBy(fn (Movimiento $movement) => $movement->categoria?->nombre ?? 'Sin categoría')
                    ->map(fn (Collection $group) => round((float) $group->sum('monto'), 2))->all();
                $incomeCategories = $monthMovements->where('tipo', 'ingreso')
                    ->groupBy(fn (Movimiento $movement) => $movement->categoria?->nombre ?? 'Sin categoría')
                    ->map(fn (Collection $group) => round((float) $group->sum('monto'), 2))->all();
                $purposes = $monthAssignments->groupBy('proposito')
                    ->map(fn (Collection $group) => round((float) $group->sum('monto'), 2))->all();

                $months[] = [
                    'period' => $month->format('Y-m'),
                    'label' => $monthNames[$month->month].' '.$month->format('y'),
                    'fullLabel' => $monthNames[$month->month].' '.$month->year,
                    'income' => round($income, 2),
                    'expenses' => round($expenses, 2),
                    'balance' => round($income - $expenses, 2),
                    'assignments' => round((float) $monthAssignments->sum('monto'), 2),
                    'movements' => $monthMovements->count(),
                    'assignmentRecords' => $monthAssignments->count(),
                    'expenseCategories' => $expenseCategories,
                    'incomeCategories' => $incomeCategories,
                    'purposes' => $purposes,
                ];
            }

            return [
                'monthly' => $months,
                'generatedAt' => now()->timezone('America/Lima')->format('d/m/Y H:i'),
            ];
        });
    }

    private function movementQuery(int $fundoId): Builder
    {
        [$from, $to] = $this->dateRange(
            $this->periodoMovimiento,
            $this->fechaDesdeMovimiento,
            $this->fechaHastaMovimiento
        );

        return Movimiento::query()
            ->where('fundo_id', $fundoId)
            ->with([
                'categoria',
                'animalesVendidos:id,movimiento_venta_id,arete,nombre,comprador_baja',
                'compraMedicamento:id,movimiento_id,medicamento_id,numero_lote,cantidad_inicial,proveedor',
                'compraMedicamento.medicamento:id,nombre,unidad_stock,foto_ruta,foto_encuadre',
                'compraInsumo:id,movimiento_id,insumo_id,numero_lote,cantidad_inicial,proveedor',
                'compraInsumo.insumo:id,nombre,unidad_stock,foto_ruta,foto_encuadre',
            ])
            ->when(trim($this->searchMovimiento) !== '', function (Builder $query) {
                $search = trim($this->searchMovimiento);
                $query->where(function (Builder $scope) use ($search) {
                    $scope->where('descripcion', 'like', "%{$search}%")
                        ->orWhere('beneficiario', 'like', "%{$search}%")
                        ->orWhere('proposito', 'like', "%{$search}%")
                        ->orWhereHas('animalesVendidos', fn (Builder $animals) => $animals->where('arete', 'like', "%{$search}%"))
                        ->orWhereHas('compraMedicamento', fn (Builder $lot) => $lot
                            ->where('numero_lote', 'like', "%{$search}%")
                            ->orWhereHas('medicamento', fn (Builder $medicine) => $medicine->where('nombre', 'like', "%{$search}%")))
                        ->orWhereHas('compraInsumo', fn (Builder $lot) => $lot
                            ->where('numero_lote', 'like', "%{$search}%")
                            ->orWhereHas('insumo', fn (Builder $insumo) => $insumo->where('nombre', 'like', "%{$search}%")))
                        ->orWhereHas('categoria', fn (Builder $category) => $category->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($this->tipoMovimiento, ['ingreso', 'egreso'], true), fn (Builder $query) => $query->where('tipo', $this->tipoMovimiento))
            ->when(filter_var($this->categoriaMovimiento, FILTER_VALIDATE_INT), fn (Builder $query) => $query->where('categoria_id', (int) $this->categoriaMovimiento))
            ->when($from, fn (Builder $query) => $query->whereDate('fecha', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('fecha', '<=', $to))
            ->when(is_numeric($this->montoMinMovimiento), fn (Builder $query) => $query->where('monto', '>=', (float) $this->montoMinMovimiento))
            ->when(is_numeric($this->montoMaxMovimiento), fn (Builder $query) => $query->where('monto', '<=', (float) $this->montoMaxMovimiento))
            ->when($this->conComprobante === '1', fn (Builder $query) => $query->where(fn (Builder $scope) => $scope
                ->whereNotNull('comprobante_ruta')
                ->orWhereHas('compraMedicamento.medicamento', fn (Builder $medicine) => $medicine->whereNotNull('foto_ruta'))
                ->orWhereHas('compraInsumo.insumo', fn (Builder $insumo) => $insumo->whereNotNull('foto_ruta'))))
            ->when($this->conComprobante === '0', fn (Builder $query) => $query
                ->whereNull('comprobante_ruta')
                ->whereDoesntHave('compraMedicamento.medicamento', fn (Builder $medicine) => $medicine->whereNotNull('foto_ruta'))
                ->whereDoesntHave('compraInsumo.insumo', fn (Builder $insumo) => $insumo->whereNotNull('foto_ruta')));
    }

    private function esCategoriaAsignacionFamiliar(string $categoryName): bool
    {
        $name = mb_strtolower($categoryName);

        return str_contains($name, 'asignaci') || str_contains($name, 'familiar');
    }

    private function dateRange(string $period, string $customFrom, string $customTo): array
    {
        $today = CarbonImmutable::today();

        if ($period !== '') {
            return match ($period) {
                'hoy' => [$today->toDateString(), $today->toDateString()],
                'ultimos_7_dias' => [$today->subDays(6)->toDateString(), $today->toDateString()],
                'mes_actual' => [$today->startOfMonth()->toDateString(), $today->toDateString()],
                'mes_anterior' => [
                    $today->subMonthNoOverflow()->startOfMonth()->toDateString(),
                    $today->subMonthNoOverflow()->endOfMonth()->toDateString(),
                ],
                'anio_actual' => [$today->startOfYear()->toDateString(), $today->toDateString()],
                default => [null, null],
            };
        }

        return [$this->validDate($customFrom), $this->validDate($customTo)];
    }

    private function validDate(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('Y-m-d', $value);

            return $date && $date->format('Y-m-d') === $value ? $value : null;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function recentRecordScopes(): array
    {
        return [
            'finanzas.movimientos' => ['model' => Movimiento::class, 'tab' => 'movimientos'],
        ];
    }
}
