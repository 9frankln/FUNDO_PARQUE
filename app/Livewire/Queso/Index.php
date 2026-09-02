<?php

namespace App\Livewire\Queso;

use App\Models\Fundo;
use App\Models\ProduccionQueso;
use App\Models\ProduccionQuesoPresentacion;
use App\Traits\AuthorizesPermissions;
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

    public const PDF_MAX_ROWS = 1000;

    public const REPORT_SECTIONS = [
        'summary' => ['label' => 'Resumen productivo', 'description' => 'Indicadores, periodo y volumen elaborado'],
        'daily' => ['label' => 'Elaboraciones registradas', 'description' => 'Detalle de cada jornada de producción'],
        'weekly' => ['label' => 'Consolidado semanal', 'description' => 'Totales y promedios agrupados por semana'],
        'monthly' => ['label' => 'Consolidado mensual', 'description' => 'Producción histórica mes por mes'],
        'annual' => ['label' => 'Consolidado anual', 'description' => 'Totales comparativos por año'],
    ];

    public const REPORT_COLUMNS = [
        'summary' => [
            'period' => 'Periodo cubierto',
            'records' => 'Elaboraciones registradas',
            'days' => 'Días con producción',
            'units' => 'Moldes elaborados',
            'weight' => 'Peso total producido',
            'average_units' => 'Promedio de moldes por jornada',
            'average_weight' => 'Promedio de peso por jornada',
            'last_production' => 'Última fecha de producción',
        ],
        'daily' => [
            'date' => 'Fecha de elaboración',
            'photo' => 'Fotografía',
            'units' => 'Moldes elaborados',
            'presentations' => 'Presentaciones',
            'weight' => 'Peso total',
            'observations' => 'Observaciones',
            'registered_at' => 'Fecha de registro',
        ],
        'weekly' => [
            'week' => 'Semana',
            'period' => 'Periodo',
            'days' => 'Días de producción',
            'units' => 'Moldes elaborados',
            'weight' => 'Peso total',
            'average_units' => 'Promedio de moldes',
            'average_weight' => 'Promedio de peso',
        ],
        'monthly' => [
            'month' => 'Mes',
            'records' => 'Elaboraciones registradas',
            'days' => 'Días de producción',
            'units' => 'Moldes elaborados',
            'weight' => 'Peso total',
            'average_units' => 'Promedio diario de moldes',
            'average_weight' => 'Promedio diario de peso',
        ],
        'annual' => [
            'year' => 'Año',
            'months' => 'Meses con producción',
            'records' => 'Elaboraciones registradas',
            'days' => 'Días de producción',
            'units' => 'Moldes elaborados',
            'weight' => 'Peso total',
            'average_units' => 'Promedio mensual de moldes',
            'average_weight' => 'Promedio mensual de peso',
        ],
    ];

    public $search = '';

    public $perPage = 10;

    public $sortBy = 'id';

    public $sortDir = 'desc';

    private const SORTABLE_COLUMNS = ['id', 'fecha', 'litros_leche_usados', 'peso_total_kg', 'unidades'];

    public $tab = 'diario'; // diario, semanal

    public $periodo = '';

    public $anio = '';

    public $mes = '';

    public $fechaDesde = '';

    public $fechaHasta = '';

    public $showReportModal = false;

    public $selectedReportSections = ['summary', 'daily'];

    public $reportColumns = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'periodo' => ['except' => ''],
        'anio' => ['except' => ''],
        'mes' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
    ];

    protected $listeners = ['confirmarEliminacion' => 'delete'];

    public function mount(): void
    {
        $this->reportColumns = $this->defaultReportColumns();
    }

    public static function reportSectionOptions(): array
    {
        return self::REPORT_SECTIONS;
    }

    public static function reportColumnOptions(): array
    {
        return self::REPORT_COLUMNS;
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'periodo', 'anio', 'mes', 'fechaDesde', 'fechaHasta'], true)) {
            $this->resetPage();
        }
    }

    public function updatedTab(): void
    {
        if ($this->tab !== 'diario') {
            $this->periodo = '';
        }
        if ($this->tab === 'anual') {
            $this->mes = '';
            $this->fechaDesde = '';
            $this->fechaHasta = '';
        }
        $this->resetPage();
    }

    public function updatedPeriodo($value): void
    {
        if ($value !== '') {
            $this->reset(['anio', 'mes', 'fechaDesde', 'fechaHasta']);
        }
    }

    public function updatedAnio($value): void
    {
        $this->reset(['periodo', 'fechaDesde', 'fechaHasta']);

        if ($value === '') {
            $this->mes = '';
        }
    }

    public function updatedMes($value): void
    {
        if ($value !== '' && $this->anio === '') {
            $this->anio = (string) now()->year;
        }

        $this->reset(['periodo', 'fechaDesde', 'fechaHasta']);
    }

    public function updatedFechaDesde($value): void
    {
        if ($value !== '') {
            $this->reset(['periodo', 'anio', 'mes']);
        }
    }

    public function updatedFechaHasta($value): void
    {
        if ($value !== '') {
            $this->reset(['periodo', 'anio', 'mes']);
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'periodo', 'anio', 'mes', 'fechaDesde', 'fechaHasta']);
        $this->resetPage();
    }

    public function openQuesoReportModal(): void
    {
        $this->selectedReportSections = ['summary'];
        $this->reportColumns = $this->defaultReportColumns();
        $this->resetValidation();
        $this->showReportModal = true;
    }

    public function closeQuesoReportModal(): void
    {
        $this->showReportModal = false;
        $this->resetValidation();
    }

    public function toggleAllQuesoReportSections(): void
    {
        $availableSections = array_keys(self::REPORT_SECTIONS);
        $selectedSections = array_intersect($availableSections, $this->selectedReportSections);
        $this->selectedReportSections = count($selectedSections) === count($availableSections)
            ? []
            : $availableSections;
        $this->resetValidation('selectedReportSections');
    }

    public function toggleAllQuesoReportColumns(string $section): void
    {
        if (! isset(self::REPORT_COLUMNS[$section])) {
            return;
        }

        $availableColumns = array_keys(self::REPORT_COLUMNS[$section]);
        $selectedColumns = array_intersect($availableColumns, $this->reportColumns[$section] ?? []);
        $this->reportColumns[$section] = count($selectedColumns) === count($availableColumns)
            ? []
            : $availableColumns;
        $this->resetValidation('reportColumns.'.$section);
    }

    public function downloadQuesoReport(?array $selectedSections = null, ?array $columns = null)
    {
        if ($selectedSections !== null) {
            $this->selectedReportSections = $selectedSections;
        }
        if ($columns !== null) {
            $this->reportColumns = $columns;
        }

        $this->authorizePermission('queso', 'exportar');

        $availableSections = array_keys(self::REPORT_SECTIONS);
        $rules = [
            'selectedReportSections' => ['required', 'array', 'min:1'],
            'selectedReportSections.*' => ['required', 'string', 'distinct', Rule::in($availableSections)],
        ];
        foreach ($this->selectedReportSections as $section) {
            if (! isset(self::REPORT_COLUMNS[$section])) {
                continue;
            }
            $rules['reportColumns.'.$section] = ['required', 'array', 'min:1'];
            $rules['reportColumns.'.$section.'.*'] = [
                'required',
                'string',
                'distinct',
                Rule::in(array_keys(self::REPORT_COLUMNS[$section])),
            ];
        }
        $this->validate($rules, [
            'selectedReportSections.required' => 'Selecciona al menos una sección.',
            'selectedReportSections.min' => 'Selecciona al menos una sección.',
            'selectedReportSections.*.in' => 'La selección contiene una sección no válida.',
            'selectedReportSections.*.distinct' => 'No se pueden repetir secciones.',
            'reportColumns.*.required' => 'Selecciona al menos un campo para esta sección.',
            'reportColumns.*.min' => 'Selecciona al menos un campo para esta sección.',
            'reportColumns.*.*.in' => 'La selección contiene un campo no válido.',
            'reportColumns.*.*.distinct' => 'No se pueden repetir campos.',
        ]);

        $selectedSections = array_keys(array_intersect_key(
            self::REPORT_SECTIONS,
            array_flip(array_intersect($availableSections, $this->selectedReportSections))
        ));
        $selectedColumns = [];
        foreach ($selectedSections as $section) {
            $selectedColumns[$section] = array_values(array_intersect(
                array_keys(self::REPORT_COLUMNS[$section]),
                $this->reportColumns[$section] ?? []
            ));
        }

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $query = $this->filteredProductionsQuery($fundoId);
        if ((clone $query)->count() > self::PDF_MAX_ROWS) {
            $this->addError('selectedReportSections', 'El PDF admite hasta 1,000 elaboraciones. Aplica filtros para reducir el reporte.');

            return null;
        }

        $productions = $query->with('presentaciones')->orderByDesc('id')->get();
        $weeklySummaries = $this->weeklySummaries($productions);
        $monthlySummaries = $this->monthlySummaries($productions);
        $annualSummaries = $this->annualSummaries($productions);
        $summary = $this->productionSummary($productions);
        $photoDataUris = [];
        if (in_array('daily', $selectedSections, true)
            && in_array('photo', $selectedColumns['daily'] ?? [], true)) {
            foreach ($productions as $production) {
                $photoDataUris[$production->id] = $this->photoDataUri($production->foto_ruta);
            }
        }

        $fundo = Fundo::withoutGlobalScopes()->findOrFail($fundoId);
        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $filterSummary = $this->reportFilterSummary();
        $reportSummary = 'Secciones incluidas: '.collect($selectedSections)
            ->map(fn ($section) => self::REPORT_SECTIONS[$section]['label'])
            ->join(', ').'. '.$productions->count().' elaboraciones, '
            .$weeklySummaries->count().' semanas, '.$monthlySummaries->count().' meses y '
            .$annualSummaries->count().' años consolidados.';
        $sectionOptions = self::REPORT_SECTIONS;
        $columnOptions = self::REPORT_COLUMNS;
        // Solo cerrar el modal de opciones la PRIMERA vez (no al regenerar desde preview).
        if ($this->exportStep !== 'preview') {
            $this->showReportModal = false;
        }

        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.queso', compact(
            'productions',
            'weeklySummaries',
            'monthlySummaries',
            'annualSummaries',
            'summary',
            'photoDataUris',
            'selectedSections',
            'selectedColumns',
            'fundo',
            'generatedBy',
            'generatedAt',
            'administrators',
            'filterSummary',
            'reportSummary',
            'sectionOptions',
            'columnOptions',
            'includeSignatures',
            'scale'
        ))->setPaper('a4', 'landscape');

        return $this->setPdfPreview(
            $pdf,
            'reporte_queso_'.now()->format('Ymd_His').'.pdf',
            'Producción y Transformación de Queso',
            $productions->count()
        );
    }

    public function solicitarEliminacion($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Estás seguro?',
            'text' => 'Se eliminará el registro de producción de queso seleccionado.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacion',
            'id' => $id,
        ]);
    }

    public function delete($id = null)
    {
        $this->authorizePermission('queso', 'eliminar');

        $id = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $id) {
            return;
        }

        $prod = ProduccionQueso::where('fundo_id', session('fundo_id'))->find($id);
        if ($prod) {
            $prod->delete();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Eliminado',
                'text' => 'El registro ha sido eliminado exitosamente.',
            ]);
        }
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
            $this->sortDir = in_array($column, ['id', 'fecha', 'peso_total_kg', 'unidades', 'litros_leche_usados'], true) ? 'desc' : 'asc';
        }

        $this->resetPage();
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        $sortBy = in_array($this->sortBy, self::SORTABLE_COLUMNS, true) ? $this->sortBy : 'id';
        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        // Query diario
        $produccionesDiarias = $this->filteredProductionsQuery($fundoId)
            ->with('presentaciones')
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir)
            ->paginate($this->perPage);

        $produccionesSemanales = collect();
        $produccionesMensuales = collect();
        $produccionesAnuales = collect();
        if (in_array($this->tab, ['semanal', 'mensual', 'anual'], true)) {
            $aggregateProductions = $this->filteredProductionsQuery($fundoId)->get();
            $produccionesSemanales = $this->tab === 'semanal'
                ? $this->weeklySummaries($aggregateProductions)
                : collect();
            $produccionesMensuales = $this->tab === 'mensual'
                ? $this->monthlySummaries($aggregateProductions)
                : collect();
            $produccionesAnuales = $this->tab === 'anual'
                ? $this->annualSummaries($aggregateProductions)
                : collect();
        }

        $hasActiveFilters = collect([
            $this->search, $this->periodo, $this->anio, $this->mes, $this->fechaDesde, $this->fechaHasta,
        ])->contains(fn ($value) => $value !== '' && $value !== null);
        $dateBounds = ProduccionQueso::where('fundo_id', $fundoId)
            ->selectRaw('MIN(fecha) as min_date, MAX(fecha) as max_date')
            ->first();
        $firstYear = $dateBounds?->min_date
            ? CarbonImmutable::parse($dateBounds->min_date)->year
            : now()->year;
        $lastYear = max(
            now()->year,
            $dateBounds?->max_date ? CarbonImmutable::parse($dateBounds->max_date)->year : now()->year
        );
        $availableYears = range($lastYear, $firstYear);
        $dashboardData = $this->dashboardData($fundoId);

        return view('livewire.queso.index', compact(
            'produccionesDiarias',
            'produccionesSemanales',
            'produccionesMensuales',
            'produccionesAnuales',
            'hasActiveFilters',
            'availableYears',
            'dashboardData'
        ))->layout('layouts.app');
    }

    private function dashboardData(int $fundoId): array
    {
        return Cache::remember('queso.dashboard.v1.'.$fundoId, now()->addMinutes(10), function () use ($fundoId) {
            $endMonth = CarbonImmutable::today()->startOfMonth();
            $startMonth = $endMonth->subMonthsNoOverflow(23);
            $recentProductions = ProduccionQueso::query()
                ->where('fundo_id', $fundoId)
                ->whereDate('fecha', '>=', $startMonth->toDateString())
                ->with('presentaciones:id,produccion_queso_id,peso_gramos,cantidad')
                ->get(['id', 'fecha', 'unidades', 'peso_total_kg']);
            $productionsByMonth = $recentProductions->groupBy(fn (ProduccionQueso $production) => $production->fecha->format('Y-m'));
            $monthly = [];

            for ($month = $startMonth; $month->lessThanOrEqualTo($endMonth); $month = $month->addMonth()) {
                $records = $productionsByMonth->get($month->format('Y-m'), collect());
                $presentations = [];
                $weekdays = array_fill(1, 7, ['weight' => 0.0, 'units' => 0, 'days' => 0]);

                foreach ($records as $record) {
                    $weekday = $record->fecha->dayOfWeekIso;
                    $weekdays[$weekday]['weight'] += (float) $record->peso_total_kg;
                    $weekdays[$weekday]['units'] += (int) $record->unidades;
                    $weekdays[$weekday]['days']++;

                    foreach ($record->presentaciones as $presentation) {
                        $weight = (string) $presentation->peso_gramos;
                        $presentations[$weight] = ($presentations[$weight] ?? 0) + $presentation->cantidad;
                    }
                }

                $monthly[] = [
                    'period' => $month->format('Y-m'),
                    'label' => mb_substr($this->monthName($month->month) ?? '', 0, 3).' '.$month->format('y'),
                    'fullLabel' => ($this->monthName($month->month) ?? '').' '.$month->year,
                    'weight' => round((float) $records->sum('peso_total_kg'), 2),
                    'units' => (int) $records->sum('unidades'),
                    'records' => $records->count(),
                    'days' => $records->pluck('fecha')->map(fn ($date) => $date->format('Y-m-d'))->unique()->count(),
                    'presentations' => $presentations,
                    'weekdays' => $weekdays,
                ];
            }

            $annual = ProduccionQueso::query()
                ->where('fundo_id', $fundoId)
                ->get(['fecha', 'unidades', 'peso_total_kg'])
                ->groupBy(fn (ProduccionQueso $production) => $production->fecha->format('Y'))
                ->map(function (Collection $records, string $year) {
                    return [
                        'year' => (int) $year,
                        'weight' => round((float) $records->sum('peso_total_kg'), 2),
                        'units' => (int) $records->sum('unidades'),
                        'records' => $records->count(),
                        'months' => $records->pluck('fecha')->map(fn ($date) => $date->format('Y-m'))->unique()->count(),
                    ];
                })
                ->sortBy('year')
                ->values()
                ->all();

            return [
                'monthly' => $monthly,
                'annual' => $annual,
                'presentationLabels' => collect(ProduccionQuesoPresentacion::PESOS)
                    ->mapWithKeys(fn ($label, $weight) => [(string) $weight => $label])
                    ->all(),
                'generatedAt' => now()->timezone('America/Lima')->format('d/m/Y H:i'),
            ];
        });
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

    private function filteredProductionsQuery(int $fundoId): Builder
    {
        [$fechaDesde, $fechaHasta] = $this->effectiveDateRange();
        $query = ProduccionQueso::query()->where('fundo_id', $fundoId);

        $search = trim((string) $this->search);
        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery->where('observaciones', 'like', '%'.$search.'%');

                if (is_numeric($search)) {
                    $numericValue = str_contains($search, '.') ? (float) $search : (int) $search;
                    $searchQuery->orWhere('unidades', $numericValue)
                        ->orWhere('peso_total_kg', $numericValue)
                        ->orWhereHas('presentaciones', function (Builder $presentationQuery) use ($numericValue) {
                            $presentationQuery->where('peso_gramos', $numericValue)
                                ->orWhere('cantidad', $numericValue);
                        });
                }
            });
        }
        if ($fechaDesde !== null) {
            $query->whereDate('fecha', '>=', $fechaDesde);
        }
        if ($fechaHasta !== null) {
            $query->whereDate('fecha', '<=', $fechaHasta);
        }

        return $query;
    }

    private function weeklySummaries(Collection $productions): Collection
    {
        return $productions
            ->groupBy(fn (ProduccionQueso $production) => $production->fecha->copy()->startOfWeek()->format('oW'))
            ->map(function (Collection $records, string $week) {
                $dates = $records->pluck('fecha')->sortBy(fn ($date) => $date->format('Y-m-d'))->values();
                $days = $dates->map(fn ($date) => $date->format('Y-m-d'))->unique()->count();
                $units = (int) $records->sum('unidades');
                $weight = (float) $records->sum('peso_total_kg');

                return (object) [
                    'semana' => $week,
                    'inicio_semana' => $dates->first()?->format('Y-m-d'),
                    'fin_semana' => $dates->last()?->format('Y-m-d'),
                    'dias_producidos' => $days,
                    'total_unidades' => $units,
                    'total_peso' => $weight,
                    'promedio_unidades' => $days > 0 ? $units / $days : 0,
                    'promedio_peso' => $days > 0 ? $weight / $days : 0,
                ];
            })
            ->sortByDesc('semana')
            ->values();
    }

    private function monthlySummaries(Collection $productions): Collection
    {
        return $productions
            ->groupBy(fn (ProduccionQueso $production) => $production->fecha->format('Y-m'))
            ->map(function (Collection $records, string $period) {
                $days = $records->pluck('fecha')
                    ->map(fn ($date) => $date->format('Y-m-d'))
                    ->unique()
                    ->count();
                $units = (int) $records->sum('unidades');
                $weight = (float) $records->sum('peso_total_kg');
                [$year, $month] = array_map('intval', explode('-', $period));

                return (object) [
                    'periodo' => $period,
                    'anio' => $year,
                    'mes' => $month,
                    'mes_nombre' => $this->monthName($month),
                    'registros' => $records->count(),
                    'dias_producidos' => $days,
                    'total_unidades' => $units,
                    'total_peso' => $weight,
                    'promedio_unidades' => $days > 0 ? $units / $days : 0,
                    'promedio_peso' => $days > 0 ? $weight / $days : 0,
                ];
            })
            ->sortByDesc('periodo')
            ->values();
    }

    private function annualSummaries(Collection $productions): Collection
    {
        return $productions
            ->groupBy(fn (ProduccionQueso $production) => $production->fecha->format('Y'))
            ->map(function (Collection $records, string $year) {
                $months = $records->pluck('fecha')
                    ->map(fn ($date) => $date->format('Y-m'))
                    ->unique()
                    ->count();
                $days = $records->pluck('fecha')
                    ->map(fn ($date) => $date->format('Y-m-d'))
                    ->unique()
                    ->count();
                $units = (int) $records->sum('unidades');
                $weight = (float) $records->sum('peso_total_kg');

                return (object) [
                    'anio' => (int) $year,
                    'meses_producidos' => $months,
                    'registros' => $records->count(),
                    'dias_producidos' => $days,
                    'total_unidades' => $units,
                    'total_peso' => $weight,
                    'promedio_unidades' => $months > 0 ? $units / $months : 0,
                    'promedio_peso' => $months > 0 ? $weight / $months : 0,
                ];
            })
            ->sortByDesc('anio')
            ->values();
    }

    private function productionSummary(Collection $productions): array
    {
        $dates = $productions->pluck('fecha')->sortBy(fn ($date) => $date->format('Y-m-d'))->values();
        $days = $dates->map(fn ($date) => $date->format('Y-m-d'))->unique()->count();
        $units = (int) $productions->sum('unidades');
        $weight = (float) $productions->sum('peso_total_kg');
        $firstDate = $dates->first();
        $lastDate = $dates->last();
        $period = 'Sin registros';
        if ($firstDate && $lastDate) {
            $period = $firstDate->isSameDay($lastDate)
                ? $firstDate->format('d/m/Y')
                : $firstDate->format('d/m/Y').' al '.$lastDate->format('d/m/Y');
        }

        return [
            'period' => $period,
            'records' => $productions->count().' registro(s)',
            'days' => $days.' día(s)',
            'units' => number_format($units, 0, ',', '.').' moldes',
            'weight' => number_format($weight, 2, ',', '.').' kg',
            'average_units' => number_format($days > 0 ? $units / $days : 0, 1, ',', '.').' moldes/día',
            'average_weight' => number_format($days > 0 ? $weight / $days : 0, 2, ',', '.').' kg/día',
            'last_production' => $lastDate?->format('d/m/Y') ?? 'Sin registros',
        ];
    }

    private function reportFilterSummary(): string
    {
        $customDateRange = $this->periodo === '' && $this->anio === '';

        return collect([
            'Periodo' => $this->periodLabel(),
            'Año' => $this->periodo === '' ? ($this->anio ?: null) : null,
            'Mes' => $this->periodo === '' ? $this->monthLabel() : null,
            'Desde' => $customDateRange ? ($this->fechaDesde ?: null) : null,
            'Hasta' => $customDateRange ? ($this->fechaHasta ?: null) : null,
            'Búsqueda' => trim((string) $this->search) ?: null,
        ])->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $name) => $name.': '.$value)
            ->implode(' | ') ?: 'Todo el historial, sin búsqueda textual';
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
        return $this->monthName((int) $this->mes);
    }

    private function monthName(int $month): ?string
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        return $months[$month] ?? null;
    }

    private function photoDataUri(?string $path): ?string
    {
        $path = ltrim(str_replace('\\', '/', (string) $path), '/');
        if ($path === '' || str_contains($path, '../')) {
            return null;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents);
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function defaultReportColumns(): array
    {
        return collect(self::REPORT_COLUMNS)
            ->map(fn ($columns) => array_keys($columns))
            ->all();
    }

    protected function recentRecordScopes(): array
    {
        return [
            'queso.producciones' => [
                'model' => ProduccionQueso::class,
                'tab' => 'diario',
            ],
        ];
    }
}
