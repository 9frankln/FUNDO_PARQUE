<?php

namespace App\Livewire\Engorde;

use App\Models\EngordeAnimal;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Support\EngordeReport;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasRecentRecord, WithPagination;

    public $search = '';

    public $estado = '';

    public $perPage = 10;

    public $fechaDesde = '';

    public $fechaHasta = '';

    public $periodo = '';

    public $anio = '';

    public $mes = '';

    public $showExportModal = false;

    public $showDetailedReportModal = false;

    public $detailedReportScope = 'filtered';

    public array $detailedReportLotIds = [];

    public array $detailedReportColumns = EngordeReport::DEFAULT_COLUMNS;

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

    protected $listeners = ['confirmarEliminacion' => 'delete'];

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

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

    public function updatedPeriodo($value): void
    {
        if ($value !== '') {
            $this->reset(['anio', 'mes', 'fechaDesde', 'fechaHasta']);
        }

        $this->resetPage();
    }

    public function updatedAnio($value): void
    {
        $this->reset(['periodo', 'fechaDesde', 'fechaHasta']);

        if ($value === '') {
            $this->mes = '';
        }

        $this->resetPage();
    }

    public function updatedMes($value): void
    {
        if ($value !== '' && $this->anio === '') {
            $this->anio = (string) now()->year;
        }

        $this->reset(['periodo', 'fechaDesde', 'fechaHasta']);
        $this->resetPage();
    }

    public function updatedFechaDesde($value): void
    {
        if ($value !== '') {
            $this->reset(['periodo', 'anio', 'mes']);
        }

        $this->resetPage();
    }

    public function updatedFechaHasta($value): void
    {
        if ($value !== '') {
            $this->reset(['periodo', 'anio', 'mes']);
        }

        $this->resetPage();
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

    public function delete($id)
    {
        $this->authorizePermission('engorde', 'eliminar');

        $lote = LoteEngorde::find($id);
        if ($lote) {
            $lote->delete();
            $this->dispatch('swal:modal', [
                'title' => 'Eliminado',
                'text' => 'El lote ha sido eliminado exitosamente.',
                'icon' => 'success',
            ]);
        }
    }

    public function exportar($columns = null)
    {
        $this->authorizePermission('engorde', 'exportar');

        if ($columns !== null) {
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
        $this->showExportModal = false;

        $pdf = Pdf::loadView('pdf.engorde', compact(
            'lotes',
            'selectedColumns',
            'fundo',
            'generatedBy',
            'generatedAt',
            'administrators',
            'reportSummary',
            'filterSummary'
        ));

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'lotes_engorde_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
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

        if ($scope !== null) {
            $this->detailedReportScope = $scope;
        }
        if ($lotIds !== null) {
            $this->detailedReportLotIds = $lotIds;
        }
        if ($columns !== null) {
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
        $this->showDetailedReportModal = false;

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
            'title'
        ))->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            Str::slug('reporte_general_engorde_'.now()->format('Ymd_His'), '_').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        $perPage = in_array((int) $this->perPage, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPage : 10;
        $lotes = $this->pinRecent($this->lotesQuery($fundoId), 'engorde.lotes')
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
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
        $monthsList = collect(range(11, 0))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        });

        $recentEntries = EngordeAnimal::whereHas('lote', fn ($q) => $q->where('fundo_id', $fundoId))
            ->where('fecha_ingreso', '>=', now()->subMonths(12)->startOfMonth())
            ->join('animales', 'engorde_animales.animal_id', '=', 'animales.id')
            ->get(['fecha_ingreso', 'animales.genero']);

        $groupedByMonth = $recentEntries->groupBy(function ($entry) {
            return CarbonImmutable::parse($entry->fecha_ingreso)->format('Y-m');
        });

        $monthlyData = $monthsList->map(function ($period) use ($groupedByMonth) {
            $dt = CarbonImmutable::createFromFormat('Y-m', $period);
            $monthsEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $fullMonthsEs = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            $items = $groupedByMonth->get($period, collect());
            
            return [
                'period' => $period,
                'label' => $monthsEs[$dt->month - 1].' '.$dt->format('y'),
                'fullLabel' => $fullMonthsEs[$dt->month - 1].' '.$dt->year,
                'count' => $items->count(),
                'hembras' => $items->where('genero', 'hembra')->count(),
                'machos' => $items->where('genero', 'macho')->count(),
            ];
        })->values()->all();

        // Breakdowns
        $estadosAnimales = EngordeAnimal::whereHas('lote', fn ($lote) => $lote->where('fundo_id', $fundoId))
            ->selectRaw("COALESCE(NULLIF(estado, ''), 'Desconocido') as label, COUNT(*) as count")
            ->groupBy('label')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) use ($totalAnimalesHistorico) {
                return [
                    'label' => ucfirst(str_replace('_', ' ', $item->label)),
                    'count' => (int) $item->count,
                    'percentage' => $totalAnimalesHistorico > 0 ? round(($item->count / $totalAnimalesHistorico) * 100, 1) : 0,
                ];
            })->all();

        $sexoData = EngordeAnimal::whereHas('lote', fn ($lote) => $lote->where('fundo_id', $fundoId))
            ->join('animales', 'engorde_animales.animal_id', '=', 'animales.id')
            ->selectRaw("COALESCE(NULLIF(animales.genero, ''), 'Sin Registro') as label, COUNT(*) as count")
            ->groupBy('label')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) use ($totalAnimalesHistorico) {
                return [
                    'label' => ucfirst($item->label),
                    'count' => (int) $item->count,
                    'percentage' => $totalAnimalesHistorico > 0 ? round(($item->count / $totalAnimalesHistorico) * 100, 1) : 0,
                ];
            })->all();

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
