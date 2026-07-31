<?php

namespace App\Livewire\Finanzas;

use App\Models\AsignacionFamiliar;
use App\Models\CategoriaFinanciera;
use App\Models\Fundo;
use App\Models\Movimiento;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasRecentRecord, WithPagination;

    private const PDF_MAX_ROWS = 500;

    private const REPORT_SECTIONS = [
        'movimientos' => [
            'summary' => ['label' => 'Resumen financiero', 'description' => 'Totales y balance del periodo'],
            'records' => ['label' => 'Movimientos', 'description' => 'Registros esenciales de caja'],
            'categories' => ['label' => 'Categorías', 'description' => 'Totales agrupados por categoría'],
        ],
        'asignaciones' => [
            'summary' => ['label' => 'Resumen de asignaciones', 'description' => 'Total, promedio y periodo'],
            'records' => ['label' => 'Asignaciones', 'description' => 'Entregas esenciales registradas'],
            'purposes' => ['label' => 'Propósitos', 'description' => 'Totales agrupados por destino'],
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
        'asignaciones' => [
            'summary' => [
                'period' => 'Periodo',
                'records' => 'Asignaciones',
                'total' => 'Total entregado',
                'average' => 'Promedio',
            ],
            'records' => [
                'date' => 'Fecha',
                'beneficiary' => 'Beneficiario',
                'purpose' => 'Propósito',
                'amount' => 'Monto',
            ],
            'purposes' => [
                'purpose' => 'Propósito',
                'records' => 'Entregas',
                'total' => 'Total',
            ],
        ],
    ];

    public string $tab = 'movimientos';

    public string $searchMovimiento = '';

    public string $tipoMovimiento = '';

    public string $categoriaMovimiento = '';

    public string $periodoMovimiento = '';

    public string $fechaDesdeMovimiento = '';

    public string $fechaHastaMovimiento = '';

    public string $montoMinMovimiento = '';

    public string $montoMaxMovimiento = '';

    public string $conComprobante = '';

    public string $searchAsignacion = '';

    public string $propositoAsignacion = '';

    public string $periodoAsignacion = '';

    public string $fechaDesdeAsignacion = '';

    public string $fechaHastaAsignacion = '';

    public string $montoMinAsignacion = '';

    public string $montoMaxAsignacion = '';

    public string $conFoto = '';

    public int $perPage = 10;

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
        'searchAsignacion' => ['except' => ''],
        'propositoAsignacion' => ['except' => ''],
        'periodoAsignacion' => ['except' => ''],
        'fechaDesdeAsignacion' => ['except' => ''],
        'fechaHastaAsignacion' => ['except' => ''],
        'montoMinAsignacion' => ['except' => ''],
        'montoMaxAsignacion' => ['except' => ''],
        'conFoto' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'confirmarEliminacionMovimiento' => 'deleteMovimiento',
        'confirmarEliminacionAsignacion' => 'deleteAsignacion',
    ];

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    private const MOVEMENT_FILTERS = [
        'searchMovimiento', 'tipoMovimiento', 'categoriaMovimiento',
        'periodoMovimiento', 'fechaDesdeMovimiento', 'fechaHastaMovimiento',
        'montoMinMovimiento', 'montoMaxMovimiento', 'conComprobante',
    ];

    private const ASSIGNMENT_FILTERS = [
        'searchAsignacion', 'propositoAsignacion', 'periodoAsignacion',
        'fechaDesdeAsignacion', 'fechaHastaAsignacion',
        'montoMinAsignacion', 'montoMaxAsignacion', 'conFoto',
    ];

    public function mount(): void
    {
        $this->tab = in_array($this->tab, ['movimientos', 'asignaciones'], true)
            ? $this->tab
            : 'movimientos';
        $this->reportType = $this->tab;
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
        $this->reportType = in_array($this->tab, ['movimientos', 'asignaciones'], true) ? $this->tab : 'movimientos';
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

        $records = $this->reportType === 'movimientos'
            ? $this->movementQuery($fundoId)->orderByDesc('fecha')->orderByDesc('id')->limit(self::PDF_MAX_ROWS + 1)->get()
            : $this->assignmentQuery($fundoId)->orderByDesc('fecha')->orderByDesc('id')->limit(self::PDF_MAX_ROWS + 1)->get();

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
        $this->showReportModal = false;

        $pdf = Pdf::loadView('pdf.finance-index', compact(
            'report', 'fundo', 'generatedBy', 'generatedAt', 'administrators', 'records'
        ))->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'reporte_'.$this->reportType.'_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function updated($property): void
    {
        if (in_array($property, self::MOVEMENT_FILTERS, true)) {
            $this->resetPage('movimientosPage');
        }

        if (in_array($property, self::ASSIGNMENT_FILTERS, true)) {
            $this->resetPage('asignacionesPage');
        }
    }

    public function updatedTab($value): void
    {
        if (! in_array($value, ['movimientos', 'asignaciones'], true)) {
            $this->tab = 'movimientos';
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

    public function updatedPeriodoAsignacion($value): void
    {
        if ($value !== '') {
            $this->fechaDesdeAsignacion = '';
            $this->fechaHastaAsignacion = '';
        }
    }

    public function updatedFechaDesdeAsignacion($value): void
    {
        if ($value !== '') {
            $this->periodoAsignacion = '';
        }
    }

    public function updatedFechaHastaAsignacion($value): void
    {
        if ($value !== '') {
            $this->periodoAsignacion = '';
        }
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, self::PER_PAGE_OPTIONS, true) ? (int) $value : 10;
        $this->resetPage('movimientosPage');
        $this->resetPage('asignacionesPage');
    }

    public function resetMovimientoFilters(): void
    {
        $this->reset(self::MOVEMENT_FILTERS);
        $this->resetPage('movimientosPage');
    }

    public function resetAsignacionFilters(): void
    {
        $this->reset(self::ASSIGNMENT_FILTERS);
        $this->resetPage('asignacionesPage');
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

    public function deleteMovimiento($id = null): void
    {
        $this->authorizePermission('finanzas', 'eliminar');
        $id = is_array($id) ? ($id['id'] ?? null) : $id;
        $movimiento = Movimiento::query()
            ->where('fundo_id', session('fundo_id'))
            ->find($id);

        if (! $movimiento) {
            return;
        }

        $receipt = $movimiento->comprobante_ruta;
        $movimiento->delete();

        if ($receipt) {
            Storage::disk('local')->delete($receipt);
        }

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Movimiento eliminado',
            'text' => 'El movimiento fue archivado correctamente.',
        ]);
    }

    public function solicitarEliminacionAsignacion($id): void
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar asignación familiar?',
            'text' => 'La asignación será archivada y su foto dejará de estar disponible.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionAsignacion',
            'id' => $id,
        ]);
    }

    public function deleteAsignacion($id = null): void
    {
        $this->authorizePermission('finanzas', 'eliminar');
        $id = is_array($id) ? ($id['id'] ?? null) : $id;
        $asignacion = AsignacionFamiliar::query()
            ->where('fundo_id', session('fundo_id'))
            ->find($id);

        if (! $asignacion) {
            return;
        }

        $photo = $asignacion->foto_ruta;
        $asignacion->delete();

        if ($photo) {
            Storage::disk('local')->delete($photo);
        }

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Asignación eliminada',
            'text' => 'La asignación fue archivada correctamente.',
        ]);
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $perPage = in_array($this->perPage, self::PER_PAGE_OPTIONS, true) ? $this->perPage : 10;
        $movementQuery = $this->movementQuery($fundoId);
        $assignmentQuery = $this->assignmentQuery($fundoId);

        $movimientos = $this->pinRecent($movementQuery, 'finanzas.movimientos')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'movimientosPage');

        $asignaciones = $this->pinRecent($assignmentQuery, 'finanzas.asignaciones')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'asignacionesPage');

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
        $asignacionesMes = (float) AsignacionFamiliar::query()
            ->where('fundo_id', $fundoId)
            ->whereBetween('fecha', [$monthStart, $monthEnd])
            ->sum('monto');

        $categorias = CategoriaFinanciera::query()
            ->where('activo', true)
            ->when($this->tipoMovimiento !== '', fn (Builder $query) => $query->where('tipo', $this->tipoMovimiento))
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get(['id', 'tipo', 'nombre']);

        $hasMovementFilters = collect(self::MOVEMENT_FILTERS)->contains(
            fn ($property) => (string) $this->{$property} !== ''
        );
        $hasAssignmentFilters = collect(self::ASSIGNMENT_FILTERS)->contains(
            fn ($property) => (string) $this->{$property} !== ''
        );
        $dashboardData = $this->dashboardData($fundoId);
        $reportSectionOptions = $this->reportSectionOptions();
        $reportColumnOptions = $this->reportColumnOptions();

        return view('livewire.finanzas.index', compact(
            'movimientos',
            'asignaciones',
            'categorias',
            'ingresosMes',
            'egresosMes',
            'balanceMes',
            'asignacionesMes',
            'hasMovementFilters',
            'hasAssignmentFilters',
            'dashboardData',
            'reportSectionOptions',
            'reportColumnOptions'
        ))->layout('layouts.app');
    }

    private function validatedReportSelection(): array
    {
        $this->validate([
            'reportType' => ['required', Rule::in(['movimientos', 'asignaciones'])],
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
        } else {
            $total = (float) $records->sum('monto');
            $summary = [
                'period' => $period,
                'records' => number_format($records->count()),
                'total' => $currency($total),
                'average' => $currency($records->count() > 0 ? $total / $records->count() : 0),
            ];
            $rows = $records->map(fn (AsignacionFamiliar $assignment) => [
                'date' => $assignment->fecha->format('d/m/Y'),
                'beneficiary' => $assignment->beneficiario,
                'purpose' => ucfirst(str_replace('_', ' ', $assignment->proposito)),
                'amount' => $currency((float) $assignment->monto),
            ])->all();
            $aggregates = $records
                ->groupBy('proposito')
                ->map(fn (Collection $group, string $purpose) => [
                    'purpose' => ucfirst(str_replace('_', ' ', $purpose)),
                    'records' => number_format($group->count()),
                    'total' => $currency((float) $group->sum('monto')),
                ])->sortByDesc(fn (array $row) => (float) str_replace([',', 'S/. '], '', $row['total']))->values()->all();
        }

        return [
            'type' => $type,
            'title' => $type === 'movimientos' ? 'Reporte de movimientos de caja' : 'Reporte de asignación familiar',
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
        $properties = $type === 'movimientos' ? self::MOVEMENT_FILTERS : self::ASSIGNMENT_FILTERS;
        $active = collect($properties)->filter(fn (string $property) => (string) $this->{$property} !== '')->count();

        return $active > 0
            ? "Datos según {$active} filtro(s) activo(s) en la tabla."
            : 'Todo el historial disponible del fundo.';
    }

    private function dashboardData(int $fundoId): array
    {
        $end = CarbonImmutable::today()->startOfMonth();
        $start = $end->subMonthsNoOverflow(17);
        $movements = Movimiento::query()
            ->where('fundo_id', $fundoId)
            ->whereDate('fecha', '>=', $start->toDateString())
            ->with('categoria:id,nombre')
            ->get(['id', 'categoria_id', 'tipo', 'monto', 'fecha']);
        $assignments = AsignacionFamiliar::query()
            ->where('fundo_id', $fundoId)
            ->whereDate('fecha', '>=', $start->toDateString())
            ->get(['id', 'proposito', 'monto', 'fecha']);
        $movementsByMonth = $movements->groupBy(fn (Movimiento $movement) => $movement->fecha->format('Y-m'));
        $assignmentsByMonth = $assignments->groupBy(fn (AsignacionFamiliar $assignment) => $assignment->fecha->format('Y-m'));
        $months = [];
        $monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        for ($month = $start; $month->lessThanOrEqualTo($end); $month = $month->addMonth()) {
            $monthMovements = $movementsByMonth->get($month->format('Y-m'), collect());
            $monthAssignments = $assignmentsByMonth->get($month->format('Y-m'), collect());
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
            ->with('categoria')
            ->when(trim($this->searchMovimiento) !== '', function (Builder $query) {
                $search = trim($this->searchMovimiento);
                $query->where(function (Builder $scope) use ($search) {
                    $scope->where('descripcion', 'like', "%{$search}%")
                        ->orWhereHas('categoria', fn (Builder $category) => $category->where('nombre', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($this->tipoMovimiento, ['ingreso', 'egreso'], true), fn (Builder $query) => $query->where('tipo', $this->tipoMovimiento))
            ->when(filter_var($this->categoriaMovimiento, FILTER_VALIDATE_INT), fn (Builder $query) => $query->where('categoria_id', (int) $this->categoriaMovimiento))
            ->when($from, fn (Builder $query) => $query->whereDate('fecha', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('fecha', '<=', $to))
            ->when(is_numeric($this->montoMinMovimiento), fn (Builder $query) => $query->where('monto', '>=', (float) $this->montoMinMovimiento))
            ->when(is_numeric($this->montoMaxMovimiento), fn (Builder $query) => $query->where('monto', '<=', (float) $this->montoMaxMovimiento))
            ->when($this->conComprobante === '1', fn (Builder $query) => $query->whereNotNull('comprobante_ruta'))
            ->when($this->conComprobante === '0', fn (Builder $query) => $query->whereNull('comprobante_ruta'));
    }

    private function assignmentQuery(int $fundoId): Builder
    {
        [$from, $to] = $this->dateRange(
            $this->periodoAsignacion,
            $this->fechaDesdeAsignacion,
            $this->fechaHastaAsignacion
        );

        return AsignacionFamiliar::query()
            ->where('fundo_id', $fundoId)
            ->when(trim($this->searchAsignacion) !== '', function (Builder $query) {
                $search = trim($this->searchAsignacion);
                $query->where(function (Builder $scope) use ($search) {
                    $scope->where('beneficiario', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($this->propositoAsignacion !== '', fn (Builder $query) => $query->where('proposito', $this->propositoAsignacion))
            ->when($from, fn (Builder $query) => $query->whereDate('fecha', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('fecha', '<=', $to))
            ->when(is_numeric($this->montoMinAsignacion), fn (Builder $query) => $query->where('monto', '>=', (float) $this->montoMinAsignacion))
            ->when(is_numeric($this->montoMaxAsignacion), fn (Builder $query) => $query->where('monto', '<=', (float) $this->montoMaxAsignacion))
            ->when($this->conFoto === '1', fn (Builder $query) => $query->whereNotNull('foto_ruta'))
            ->when($this->conFoto === '0', fn (Builder $query) => $query->whereNull('foto_ruta'));
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
            'finanzas.asignaciones' => ['model' => AsignacionFamiliar::class, 'tab' => 'asignaciones'],
        ];
    }
}
