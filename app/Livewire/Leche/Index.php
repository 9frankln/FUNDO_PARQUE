<?php

namespace App\Livewire\Leche;

use App\Exports\OrdenosExport;
use App\Models\Animal;
use App\Models\Fundo;
use App\Models\Ordeno;
use App\Models\OrdenoFotoDiaria;
use App\Support\ImageFrame;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasRecentRecord, WithPagination;

    public $fechaDesde = '';

    public $fechaHasta = '';

    public $periodo = '';

    public $anio = '';

    public $mes = '';

    public $turno = '';

    public $tipoRegistro = '';

    public $litrosMin = '';

    public $litrosMax = '';

    public $observacion = '';

    public $conFoto = '';

    public $perPage = 10;

    public $sortBy = 'fecha';

    public $sortDir = 'desc';

    public $showExportModal = false;

    public $exportFormat = 'xlsx';

    public $selectedColumns = [
        'fecha',
        'turno',
        'tipo_registro',
        'litros_total',
        'cantidad_vacas',
        'promedio',
        'foto',
        'observaciones',
        'created_at',
    ];

    public $availableColumns = [
        'fecha' => 'Fecha',
        'turno' => 'Turno',
        'tipo_registro' => 'Tipo de registro',
        'litros_total' => 'Litros totales',
        'cantidad_vacas' => 'Cantidad de vacas',
        'promedio' => 'Promedio por vaca',
        'foto' => 'Foto diaria',
        'observaciones' => 'Observaciones',
        'created_at' => 'Fecha de creación',
    ];

    protected $queryString = [
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'periodo' => ['except' => ''],
        'anio' => ['except' => ''],
        'mes' => ['except' => ''],
        'turno' => ['except' => ''],
        'tipoRegistro' => ['except' => ''],
        'litrosMin' => ['except' => ''],
        'litrosMax' => ['except' => ''],
        'observacion' => ['except' => ''],
        'conFoto' => ['except' => ''],
        'perPage' => ['except' => 10],
        'sortBy' => ['except' => 'fecha'],
        'sortDir' => ['except' => 'desc'],
    ];

    protected $listeners = ['confirmarEliminacion' => 'delete'];

    private const SORTABLE_COLUMNS = [
        'fecha',
        'turno',
        'tipo_registro',
        'cantidad_vacas',
        'litros_total',
        'created_at',
    ];

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function updated($property): void
    {
        if (in_array($property, [
            'fechaDesde',
            'fechaHasta',
            'periodo',
            'anio',
            'mes',
            'turno',
            'tipoRegistro',
            'litrosMin',
            'litrosMax',
            'observacion',
            'conFoto',
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
        $this->reset([
            'fechaDesde',
            'fechaHasta',
            'periodo',
            'anio',
            'mes',
            'turno',
            'tipoRegistro',
            'litrosMin',
            'litrosMax',
            'observacion',
            'conFoto',
        ]);
        $this->resetPage();
    }

    public function sort($column): void
    {
        if (! in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDirection() === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }

        $this->resetPage();
    }

    public function solicitarEliminacion($id): void
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar registro de ordeño?',
            'text' => 'El registro será archivado. La foto diaria también se eliminará si no quedan turnos para esa fecha.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacion',
            'id' => $id,
        ]);
    }

    public function delete($id = null): void
    {
        $this->authorizePermission('leche', 'eliminar');

        $id = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $id) {
            return;
        }

        $fundoId = session('fundo_id');
        $ordeno = Ordeno::query()->where('fundo_id', $fundoId)->find($id);
        if (! $ordeno) {
            return;
        }

        $photoPath = DB::transaction(function () use ($ordeno, $fundoId) {
            $fecha = $ordeno->fecha->toDateString();
            $ordeno->delete();

            if (Ordeno::query()->where('fundo_id', $fundoId)->whereDate('fecha', $fecha)->exists()) {
                return null;
            }

            $photo = OrdenoFotoDiaria::query()
                ->where('fundo_id', $fundoId)
                ->whereDate('fecha', $fecha)
                ->lockForUpdate()
                ->first();

            if (! $photo) {
                return null;
            }

            $path = $photo->foto_ruta;
            $photo->delete();

            return $path;
        });

        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Registro eliminado',
            'text' => 'El ordeño fue archivado correctamente.',
        ]);
    }

    public function exportar($format = null, $columns = null)
    {
        $this->authorizePermission('leche', 'exportar');

        if ($format !== null) {
            $this->exportFormat = $format;
        }
        if ($columns !== null) {
            $this->selectedColumns = $columns;
        }

        $this->validate([
            'exportFormat' => ['required', Rule::in(['xlsx', 'pdf'])],
            'selectedColumns' => ['required', 'array', 'min:1'],
            'selectedColumns.*' => ['required', 'string', 'distinct', Rule::in(array_keys($this->availableColumns))],
        ], [
            'exportFormat.in' => 'Selecciona un formato de exportación válido.',
            'selectedColumns.required' => 'Selecciona al menos una columna para el reporte.',
            'selectedColumns.min' => 'Selecciona al menos una columna para el reporte.',
            'selectedColumns.*.in' => 'La selección contiene una columna no válida.',
            'selectedColumns.*.distinct' => 'No se pueden repetir columnas.',
        ]);

        $fundoId = (int) session('fundo_id');
        $selectedColumns = array_values($this->selectedColumns);
        $filters = $this->exportFilters();
        $generatedBy = auth()->user()->name;
        $this->showExportModal = false;

        if ($this->exportFormat === 'xlsx') {
            return (new OrdenosExport($fundoId, $selectedColumns, $filters, $generatedBy))
                ->download('registro_ordeno_'.now()->format('Ymd_His').'.xlsx');
        }

        $ordenos = $this->ordenosQuery($fundoId)
            ->orderBy($this->sortColumn(), $this->sortDirection())
            ->orderBy('id', $this->sortDirection())
            ->get();
        $this->attachDailyPhotos($ordenos, $fundoId);

        $fundo = Fundo::findOrFail($fundoId);
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $reportSummary = $ordenos->count().' registros, '.number_format((float) $ordenos->sum('litros_total'), 2).' litros. Columnas: '.collect($selectedColumns)
            ->map(fn ($column) => $this->availableColumns[$column])
            ->join(', ', ' y ').'.';
        $filterSummary = $this->filterSummary();

        $pdf = Pdf::loadView('pdf.ordenos', compact(
            'ordenos',
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
            'registro_ordeno_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function filters(): array
    {
        [$fechaDesde, $fechaHasta] = $this->effectiveDateRange();
        $observacion = trim((string) $this->observacion);

        return [
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'turno' => $this->turno,
            'tipoRegistro' => $this->tipoRegistro,
            'litrosMin' => $this->litrosMin,
            'litrosMax' => $this->litrosMax,
            'observacion' => mb_strlen($observacion) >= 2 ? $observacion : '',
            'conFoto' => $this->conFoto,
        ];
    }

    private function exportFilters(): array
    {
        return array_merge($this->filters(), [
            'sortBy' => $this->sortColumn(),
            'sortDir' => $this->sortDirection(),
        ]);
    }

    private function ordenosQuery(int $fundoId): Builder
    {
        return Ordeno::query()
            ->where('fundo_id', $fundoId)
            ->applyFilters($this->filters());
    }

    private function sortColumn(): string
    {
        return in_array($this->sortBy, self::SORTABLE_COLUMNS, true) ? $this->sortBy : 'fecha';
    }

    private function sortDirection(): string
    {
        return in_array($this->sortDir, ['asc', 'desc'], true) ? $this->sortDir : 'desc';
    }

    private function attachDailyPhotos($ordenos, int $fundoId): void
    {
        $dates = $ordenos->pluck('fecha')->filter()->map->toDateString()->unique()->values();
        if ($dates->isEmpty()) {
            return;
        }

        $photos = OrdenoFotoDiaria::query()
            ->where('fundo_id', $fundoId)
            ->where(function (Builder $query) use ($dates) {
                foreach ($dates as $date) {
                    $query->orWhere(fn (Builder $dateQuery) => $dateQuery
                        ->where('fecha', '>=', $date)
                        ->where('fecha', '<', CarbonImmutable::parse($date)->addDay()->toDateString()));
                }
            })
            ->get(['fecha', 'foto_ruta', 'foto_encuadre'])
            ->keyBy(fn ($photo) => $photo->fecha->toDateString());

        $ordenos->each(function ($ordeno) use ($photos) {
            $photo = $photos->get($ordeno->fecha->toDateString());
            $ordeno->setAttribute('foto_ruta_diaria', $photo?->foto_ruta);
            $ordeno->setAttribute('foto_encuadre_diario', $photo ? ImageFrame::normalize($photo->foto_encuadre) : null);
            $ordeno->setAttribute('tiene_foto', (bool) $photo);
        });
    }

    private function filterSummary(): string
    {
        [$fechaDesde, $fechaHasta] = $this->effectiveDateRange();

        return collect([
            'Periodo' => $this->periodLabel(),
            'Año' => $this->anio ?: null,
            'Mes' => $this->monthLabel(),
            'Desde' => $fechaDesde ?: null,
            'Hasta' => $fechaHasta ?: null,
            'Turno' => $this->turno ? Ordeno::turnoLabel($this->turno) : null,
            'Tipo' => $this->tipoRegistro ? Ordeno::tipoLabel($this->tipoRegistro) : null,
            'Litros mín.' => $this->litrosMin !== '' ? $this->litrosMin : null,
            'Litros máx.' => $this->litrosMax !== '' ? $this->litrosMax : null,
            'Observación' => $this->observacion ?: null,
            'Foto' => $this->conFoto === '' ? null : ($this->conFoto === '1' ? 'Con foto' : 'Sin foto'),
        ])->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $name) => "{$name}: {$value}")
            ->implode(' | ') ?: 'Sin filtros adicionales';
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
            $start = CarbonImmutable::create($year, $month !== false ? $month : 1, 1)->startOfDay();
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

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        $perPage = in_array((int) $this->perPage, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPage : 10;
        $ordenos = $this->pinRecent($this->ordenosQuery($fundoId), 'leche.ordenos')
            ->orderBy($this->sortColumn(), $this->sortDirection())
            ->orderBy('id', $this->sortDirection())
            ->paginate($perPage);
        $this->attachDailyPhotos($ordenos->getCollection(), $fundoId);

        // OPTIMIZED DASHBOARD QUERIES
        $vacasAptasCount = Animal::query()
            ->where('fundo_id', $fundoId)
            ->whereHas('especie', fn ($query) => $query->where('nombre', 'Bovino'))
            ->where('genero', 'hembra')
            ->where('apta_ordeno', true)
            ->count();

        $litrosSemana = (float) Ordeno::query()
            ->where('fundo_id', $fundoId)
            ->where('fecha', '>=', now()->startOfWeek()->toDateString())
            ->sum('litros_total');

        $ultimoOrdeno = (float) (Ordeno::query()
            ->where('fundo_id', $fundoId)
            ->latest('fecha')
            ->latest('id')
            ->value('litros_total') ?? 0);

        // Aggregations from DB directly to avoid N+1 and slow PHP mapping
        $historicalStats = DB::table('ordenos')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->selectRaw('SUM(litros_total) as total_litros, SUM(cantidad_vacas) as total_vacas_ordenadas, COUNT(*) as total_registros')
            ->first();

        // 12 months trend (using DB groupBy)
        $monthsList = collect(range(11, 0))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        });
        $minMonthStr = now()->subMonths(12)->startOfMonth()->toDateString();

        $monthlyRaw = DB::table('ordenos')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->where('fecha', '>=', $minMonthStr)
            // substr(fecha, 1, 7) works perfectly in SQLite to extract 'YYYY-MM'
            ->selectRaw('substr(fecha, 1, 7) as month_period, SUM(litros_total) as total_litros, AVG(litros_total) as avg_litros')
            ->groupBy('month_period')
            ->get()
            ->keyBy('month_period');

        $monthlyData = $monthsList->map(function ($period) use ($monthlyRaw) {
            $dt = CarbonImmutable::createFromFormat('Y-m', $period);
            $monthsEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $fullMonthsEs = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $data = $monthlyRaw->get($period);

            return [
                'period' => $period,
                'label' => $monthsEs[$dt->month - 1].' '.$dt->format('y'),
                'fullLabel' => $fullMonthsEs[$dt->month - 1].' '.$dt->year,
                'count' => $data ? (float) $data->total_litros : 0, // 'count' used for the chart Y-axis mapping in Alpine
                'avg_litros' => $data ? (float) $data->avg_litros : 0,
            ];
        })->values()->all();

        // Turno breakdowns
        $turnosData = DB::table('ordenos')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->selectRaw('turno, SUM(litros_total) as total_litros')
            ->groupBy('turno')
            ->get()
            ->map(function ($item) use ($historicalStats) {
                return [
                    'label' => ucfirst($item->turno),
                    'count' => (float) $item->total_litros,
                    'percentage' => $historicalStats->total_litros > 0 ? round(($item->total_litros / $historicalStats->total_litros) * 100, 1) : 0,
                ];
            })->all();

        // Tipos de registro breakdowns
        $tiposData = DB::table('ordenos')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->selectRaw('tipo_registro, SUM(litros_total) as total_litros')
            ->groupBy('tipo_registro')
            ->get()
            ->map(function ($item) use ($historicalStats) {
                return [
                    'label' => ucfirst(str_replace('_', ' ', $item->tipo_registro)),
                    'count' => (float) $item->total_litros,
                    'percentage' => $historicalStats->total_litros > 0 ? round(($item->total_litros / $historicalStats->total_litros) * 100, 1) : 0,
                ];
            })->all();

        $dashboardData = [
            'generatedAt' => now()->format('H:i'),
            'totalLitros' => (float) ($historicalStats->total_litros ?? 0),
            'promedioVacas' => ($historicalStats->total_registros > 0) ? round($historicalStats->total_vacas_ordenadas / $historicalStats->total_registros) : 0,
            'litrosSemana' => $litrosSemana,
            'vacasAptas' => $vacasAptasCount,
            'monthly' => $monthlyData,
            'turnos' => $turnosData,
            'tipos' => $tiposData,
        ];

        $hasActiveFilters = trim((string) $this->observacion) !== ''
            || collect($this->filters())->contains(fn ($value) => $value !== '' && $value !== null);
        $dateBounds = Ordeno::query()
            ->where('fundo_id', $fundoId)
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

        return view('livewire.leche.index', compact(
            'ordenos',
            'vacasAptasCount',
            'litrosSemana',
            'ultimoOrdeno',
            'hasActiveFilters',
            'availableYears',
            'dashboardData'
        ))->layout('layouts.app');
    }

    protected function recentRecordScopes(): array
    {
        return [
            'leche.ordenos' => ['model' => Ordeno::class],
        ];
    }
}
