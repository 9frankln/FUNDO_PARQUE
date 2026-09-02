<?php

namespace App\Livewire\Animal;

use App\Exports\AnimalesExport;
use App\Exports\AnimalesTemplateExport;
use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Services\AnimalImportService;
use App\Services\AnimalInventoryService;
use App\Traits\AuthorizesPermissions;
use App\Support\PaginationOptions;
use App\Traits\HasPeriodoFilters;
use App\Traits\HasPdfPreviewModal;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal, HasPeriodoFilters, HasRecentRecord, WithFileUploads, WithPagination;

    private const EXPORT_COLUMNS = [
        'arete' => 'Código del animal',
        'nombre' => 'Nombre',
        'especie' => 'Especie',
        'raza' => 'Raza',
        'genero' => 'Género',
        'edad' => 'Edad',
        'peso' => 'Peso registrado (kg)',
        'estado_reproductivo' => 'Estado reproductivo',
        'tipo_alta' => 'Procedencia',
        'precio_compra' => 'Precio de compra (S/)',
        'activo' => 'Estado',
        'fecha_alta' => 'Fecha de alta',
    ];

    private const PDF_MAX_ROWS = 1000;

    private const PER_PAGE_OPTIONS = PaginationOptions::PER_PAGE;

    private const SORTABLE_COLUMNS = ['id', 'arete', 'nombre', 'fecha_alta', 'peso', 'fecha_nacimiento'];

    public $search = '';

    public $perPage = 10;

    public $especieId = '';

    public $razaId = '';

    public $genero = '';

    public $activo = '';

    public $motivoBaja = '';

    public $estadoReproductivo = '';

    public $tipoAlta = '';

    public $sortBy = 'id';

    public $sortDir = 'desc';

    public $exportFormat = 'pdf';

    public $selectedColumns = ['arete', 'nombre', 'especie', 'raza', 'genero', 'edad', 'peso', 'tipo_alta', 'activo', 'fecha_alta'];

    public $availableColumns = self::EXPORT_COLUMNS;

    public bool $pdfIncludeSignatures = true;

    public string $pdfScale = '85';

    public bool $showStatusModal = false;

    #[Locked]
    public ?int $statusAnimalId = null;

    public string $statusReason = '';

    public string $statusDate = '';

    public string $statusDetail = '';

    public bool $showImportModal = false;

    public $importFile = null;

    public array $importSummary = [];

    public bool $importSuccess = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'especieId' => ['except' => ''],
        'razaId' => ['except' => ''],
        'genero' => ['except' => ''],
        'activo' => ['except' => ''],
        'motivoBaja' => ['except' => ''],
        'estadoReproductivo' => ['except' => ''],
        'tipoAlta' => ['except' => ''],
        'fechaDesde' => ['except' => ''],
        'fechaHasta' => ['except' => ''],
        'periodo' => ['except' => ''],
        'anio' => ['except' => ''],
        'mes' => ['except' => ''],
    ];

    protected $listeners = ['confirmarEliminacion' => 'delete'];

    public function updated($property): void
    {
        if (in_array($property, [
            'search',
            'especieId',
            'razaId',
            'genero',
            'activo',
            'motivoBaja',
            'estadoReproductivo',
            'tipoAlta',
            'fechaDesde',
            'fechaHasta',
            'periodo',
            'anio',
            'mes',
        ], true)) {
            $this->resetPage();
        }
    }

    public function updatedEspecieId(): void
    {
        $this->razaId = '';
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
            'especieId',
            'razaId',
            'genero',
            'activo',
            'motivoBaja',
            'estadoReproductivo',
            'tipoAlta',
            'fechaDesde',
            'fechaHasta',
            'periodo',
            'anio',
            'mes',
            'search',
        ]);
        $this->resetPage();
    }

    public function sort($column)
    {
        if (! in_array($column, self::SORTABLE_COLUMNS, true)) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = in_array($column, ['id', 'peso', 'fecha_alta'], true) ? 'desc' : 'asc';
        }
    }

    public function solicitarEliminacion($id)
    {
        $animal = Animal::find($id);
        $nombre = $animal ? ($animal->nombre ? $animal->nombre.' ('.$animal->arete.')' : $animal->arete) : 'el animal';

        $this->dispatch('swal:confirm', [
            'title' => '¿Estás seguro?',
            'text' => 'Se eliminará y archivará el registro de '.$nombre.'.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacion',
            'id' => $id,
        ]);
    }

    public function delete($id = null)
    {
        $this->authorizePermission('animal', 'eliminar');

        $id = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $id) {
            return;
        }
        $animal = Animal::find($id);
        if ($animal) {
            $animal->delete();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Animal archivado',
                'text' => 'El animal ha sido archivado exitosamente.',
            ]);
        }
    }

    public function openStatusModal($id): void
    {
        $this->authorizePermission('animal', 'actualizar');
        $animal = Animal::query()
            ->where('fundo_id', session('fundo_id'))
            ->findOrFail((int) $id);

        $this->statusAnimalId = (int) $animal->id;
        $this->statusReason = $animal->activo ? '' : (string) $animal->motivo_baja;
        $this->statusDate = ($animal->fecha_baja ?? today())->format('Y-m-d');
        $this->statusDetail = (string) $animal->detalle_baja;
        $this->resetValidation();
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->resetValidation();
        $this->reset(['showStatusModal', 'statusAnimalId', 'statusReason', 'statusDate', 'statusDetail']);
    }

    public function confirmStatusChange(AnimalInventoryService $inventory)
    {
        $this->authorizePermission('animal', 'actualizar');
        $animal = Animal::query()
            ->where('fundo_id', session('fundo_id'))
            ->findOrFail($this->statusAnimalId);

        if (! $animal->activo) {
            $inventory->reactivate($animal);
            $this->closeStatusModal();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Animal reactivado',
                'text' => "{$animal->arete} volvió al inventario activo.",
            ]);

            return null;
        }

        $this->validate([
            'statusReason' => ['required', Rule::in(array_keys(Animal::INACTIVE_REASONS))],
            'statusDate' => ['required', 'date', 'before_or_equal:today'],
            'statusDetail' => [
                Rule::requiredIf($this->statusReason === 'otro'),
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'statusReason.required' => 'Selecciona el motivo de la baja.',
            'statusDate.before_or_equal' => 'La fecha de baja no puede ser futura.',
            'statusDetail.required' => 'Describe el motivo de la baja.',
        ]);

        if ($this->statusReason === 'venta') {
            if (! auth()->user()->tienePermiso('finanzas', 'crear')) {
                $this->addError('statusReason', 'Necesitas permiso para crear movimientos financieros.');

                return null;
            }

            $animalId = $animal->getKey();
            $saleDate = $this->statusDate;
            $this->closeStatusModal();

            return $this->redirectRoute('finanzas.movimiento.create', [
                'animal' => $animalId,
                'fecha_venta' => $saleDate,
            ], navigate: true);
        }

        $inventory->deactivate($animal, $this->statusReason, $this->statusDate, trim($this->statusDetail) ?: null);
        $reasonLabel = Animal::INACTIVE_REASONS[$this->statusReason];
        $this->closeStatusModal();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Baja registrada',
            'text' => "{$animal->arete} quedó inactivo por {$reasonLabel}.",
        ]);

        return null;
    }

    public function openImportModal(): void
    {
        $this->authorizePermission('animal', 'crear');
        $this->resetValidation();
        $this->reset(['importFile', 'importSummary', 'importSuccess']);
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->resetValidation();
        $this->reset(['showImportModal', 'importFile', 'importSummary', 'importSuccess']);
    }

    public function downloadImportTemplate()
    {
        $this->authorizePermission('animal', 'crear');
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $filename = 'plantilla_importacion_animales_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new AnimalesTemplateExport($fundoId), $filename);
    }

    public function processImport(AnimalImportService $importService)
    {
        $this->authorizePermission('animal', 'crear');
        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');

        $this->validate([
            'importFile' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls,csv,txt'],
        ], [
            'importFile.required' => 'Selecciona un archivo Excel (.xlsx, .xls) o CSV.',
            'importFile.mimes' => 'El archivo debe ser de formato Excel (.xlsx, .xls) o CSV.',
            'importFile.max' => 'El archivo no puede pesar más de 10 MB.',
        ]);

        $result = $importService->import($this->importFile, $fundoId, false);
        $this->importSummary = $result;

        if (($result['imported'] ?? 0) > 0) {
            Cache::forget('animal.dashboard.v2.'.$fundoId);
            $this->importSuccess = true;
            $this->resetPage();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Importación exitosa',
                'text' => "Se registraron exitosamente {$result['imported']} animales.",
            ]);
        }
    }

    public function exportar($format = null, $columns = null)
    {
        $this->authorizePermission('animal', 'exportar');

        if ($format !== null) {
            $this->exportFormat = $format;
        }
        if ($columns !== null) {
            $this->selectedColumns = $columns;
        }

        $this->validate([
            'exportFormat' => ['required', Rule::in(['xlsx', 'pdf'])],
            'selectedColumns' => ['required', 'array', 'min:1'],
            'selectedColumns.*' => ['required', 'string', 'distinct', Rule::in(array_keys(self::EXPORT_COLUMNS))],
        ], [
            'exportFormat.in' => 'Selecciona un formato de exportación válido.',
            'selectedColumns.required' => 'Selecciona al menos una columna para el reporte.',
            'selectedColumns.min' => 'Selecciona al menos una columna para el reporte.',
            'selectedColumns.*.in' => 'La selección contiene una columna no válida.',
            'selectedColumns.*.distinct' => 'No se pueden repetir columnas.',
        ]);

        $fundoId = (int) session('fundo_id');
        abort_unless($fundoId, 403, 'Debe seleccionar un fundo.');
        $selectedColumns = array_keys(array_intersect_key(self::EXPORT_COLUMNS, array_flip($this->selectedColumns)));

        $filters = $this->exportFilters();

        if ($this->exportFormat === 'xlsx') {
            $this->showExportModal = false;

            return (new AnimalesExport($fundoId, $selectedColumns, $filters, auth()->user()->name))
                ->download('inventario_animal_'.now()->format('Ymd_His').'.xlsx');
        }

        $query = Animal::query()->where('fundo_id', $fundoId)->with(['especie', 'raza']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('arete', 'like', '%'.$this->search.'%')
                    ->orWhere('nombre', 'like', '%'.$this->search.'%')
                    ->orWhereExists(fn ($history) => $history
                        ->selectRaw('1')
                        ->from('animal_identifiers')
                        ->whereColumn('animal_identifiers.animal_id', 'animales.id')
                        ->where('animal_identifiers.arete', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->especieId) {
            $query->where('especie_id', $this->especieId);
        }
        if ($this->razaId) {
            $query->where('raza_id', $this->razaId);
        }
        if ($this->genero) {
            $query->where('genero', $this->genero);
        }
        if ($this->activo !== '') {
            $query->where('activo', (bool) $this->activo);
        }
        if ($this->motivoBaja) {
            $query->where('motivo_baja', $this->motivoBaja);
        }
        if ($this->estadoReproductivo) {
            $query->where('estado_reproductivo', $this->estadoReproductivo);
        }
        if ($this->tipoAlta) {
            $query->where('tipo_alta', $this->tipoAlta);
        }
        if ($filters['fechaDesde']) {
            $query->where('fecha_alta', '>=', $filters['fechaDesde']);
        }
        if ($filters['fechaHasta']) {
            $query->where('fecha_alta', '<', $this->exclusiveEndDate($filters['fechaHasta']));
        }

        if ((clone $query)->count() > self::PDF_MAX_ROWS) {
            $this->addError('exportFormat', 'El PDF admite hasta 1,000 animales. Usa Excel para inventarios mayores.');

            return;
        }

        $sortBy = in_array($this->sortBy, self::SORTABLE_COLUMNS, true) ? $this->sortBy : 'id';
        $sortDir = $this->sortDir === 'asc' ? 'asc' : 'desc';
        $animales = $query->with(['especie', 'raza'])->orderBy($sortBy, $sortDir)->get();
        $fundo = Fundo::findOrFail($fundoId);
        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $reportSummary = $animales->count().' registros. Columnas: '.collect($selectedColumns)
            ->map(fn ($column) => self::EXPORT_COLUMNS[$column])
            ->join(', ', ' y ').'.';
        $filterSummary = $this->reportFilterSummary($filters);
        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.animales', compact(
            'animales',
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

        return $this->setPdfPreview(
            $pdf,
            'inventario_animal_'.now()->format('Ymd_His').'.pdf',
            'Inventario Animal ('.count($animales).' registros)',
            $animales->count()
        );
    }

    private function exportFilters(): array
    {
        [$fechaDesde, $fechaHasta] = $this->effectiveDateRange();

        return [
            'search' => $this->search,
            'especieId' => $this->especieId,
            'razaId' => $this->razaId,
            'genero' => $this->genero,
            'activo' => $this->activo,
            'motivoBaja' => $this->motivoBaja,
            'estadoReproductivo' => $this->estadoReproductivo,
            'tipoAlta' => $this->tipoAlta,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'sortBy' => $this->sortBy,
            'sortDir' => $this->sortDir,
        ];
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

    private function reportFilterSummary(array $filters): string
    {
        $customDateRange = $this->periodo === '' && $this->anio === '';

        return collect([
            'Búsqueda' => trim((string) $this->search) ?: null,
            'Especie' => $this->especieId ? Especie::find($this->especieId)?->nombre : null,
            'Raza' => $this->razaId ? Raza::find($this->razaId)?->nombre : null,
            'Género' => match ($this->genero) {
                'macho' => 'Macho',
                'hembra' => 'Hembra',
                default => null,
            },
            'Estado reproductivo' => Animal::REPRODUCTIVE_STATES[$this->estadoReproductivo] ?? null,
            'Procedencia' => Animal::ADMISSION_TYPES[$this->tipoAlta] ?? null,
            'Estado' => $this->activo === '' ? null : ($this->activo ? 'Activo' : 'Inactivo'),
            'Motivo de baja' => Animal::INACTIVE_REASONS[$this->motivoBaja] ?? null,
            'Periodo' => $this->periodLabel(),
            'Año' => $this->periodo === '' ? ($this->anio ?: null) : null,
            'Mes' => $this->periodo === '' ? $this->monthLabel() : null,
            'Desde' => $customDateRange ? ($filters['fechaDesde'] ?: null) : null,
            'Hasta' => $customDateRange ? ($filters['fechaHasta'] ?: null) : null,
        ])->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value, $name) => "{$name}: {$value}")
            ->implode(' | ') ?: 'Sin filtros adicionales';
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        [$fechaDesde, $fechaHasta] = $this->effectiveDateRange();

        $query = Animal::query()
            ->where('fundo_id', $fundoId)
            ->with(['especie', 'raza']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('arete', 'like', '%'.$this->search.'%')
                    ->orWhere('nombre', 'like', '%'.$this->search.'%')
                    ->orWhereExists(fn ($history) => $history
                        ->selectRaw('1')
                        ->from('animal_identifiers')
                        ->whereColumn('animal_identifiers.animal_id', 'animales.id')
                        ->where('animal_identifiers.arete', 'like', '%'.$this->search.'%'));
            });
        }

        if ($this->especieId) {
            $query->where('especie_id', $this->especieId);
        }
        if ($this->razaId) {
            $query->where('raza_id', $this->razaId);
        }
        if ($this->genero) {
            $query->where('genero', $this->genero);
        }
        if ($this->activo !== '') {
            $query->where('activo', (bool) $this->activo);
        }
        if ($this->motivoBaja) {
            $query->where('motivo_baja', $this->motivoBaja);
        }
        if ($this->estadoReproductivo) {
            $query->where('estado_reproductivo', $this->estadoReproductivo);
        }
        if ($this->tipoAlta) {
            $query->where('tipo_alta', $this->tipoAlta);
        }
        if ($fechaDesde) {
            $query->where('fecha_alta', '>=', $fechaDesde);
        }
        if ($fechaHasta) {
            $query->where('fecha_alta', '<', $this->exclusiveEndDate($fechaHasta));
        }

        $perPage = in_array((int) $this->perPage, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPage : 10;
        $sortBy = in_array($this->sortBy, self::SORTABLE_COLUMNS, true) ? $this->sortBy : 'arete';
        $sortDir = $this->sortDir === 'desc' ? 'desc' : 'asc';
        $animales = $this->pinRecent($query, 'animal.animales')
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage);

        $especies = Especie::where('activo', true)->get();
        $razas = $this->especieId
            ? Raza::where('especie_id', $this->especieId)->where('activo', true)->get()
            : Raza::where('activo', true)->get();

        $stats = Animal::where('fundo_id', $fundoId)
            ->where('activo', true)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(CASE WHEN genero = 'hembra' THEN 1 ELSE 0 END) AS hembras")
            ->selectRaw("SUM(CASE WHEN genero = 'macho' THEN 1 ELSE 0 END) AS machos")
            ->selectRaw('SUM(CASE WHEN apta_ordeno = 1 THEN 1 ELSE 0 END) AS aptos')
            ->first();

        $totalAnimals = (int) ($stats->total ?? 0);

        /*
         * OPTIMIZACIÓN DE RENDIMIENTO:
         * Las agregaciones del mini-dashboard (mensual por género, especies,
         * estados, altas y productivo) se calculan en BD con GROUP BY y se
         * cachean 5 minutos por fundo. Antes se cargaban 12 meses de animales
         * completos a memoria en cada render.
         */
        $dashCacheKey = 'animal.dashboard.v2.'.$fundoId;

        [$monthlyData, $especiesData, $estadosData, $altasData, $productivoData] = Cache::remember($dashCacheKey, now()->addMinutes(5), function () use ($fundoId, $totalAnimals): array {
            // Altas mensuales por género — una sola query GROUP BY en BD.
            $monthlyRaw = Animal::where('fundo_id', $fundoId)
                ->where('activo', true)
                ->where('fecha_alta', '>=', now()->subMonths(12)->startOfMonth())
                ->selectRaw("substr(fecha_alta, 1, 7) as period, genero, COUNT(*) as count")
                ->groupBy('period', 'genero')
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

            $withPercentage = fn ($rows) => collect($rows)->map(function ($item) use ($totalAnimals) {
                $item['percentage'] = $totalAnimals > 0 ? round(($item['count'] / $totalAnimals) * 100, 1) : 0;

                return $item;
            })->all();

            $especiesData = $withPercentage(Animal::where('animales.fundo_id', $fundoId)
                ->where('animales.activo', true)
                ->leftJoin('especies', 'animales.especie_id', '=', 'especies.id')
                ->selectRaw("COALESCE(especies.nombre, 'Sin Especie') as label, COUNT(*) as count")
                ->groupBy('label')
                ->orderBy('count', 'desc')
                ->get()
                ->map(fn ($item) => ['label' => $item->label, 'count' => (int) $item->count])
                ->all());

            $estadosData = $withPercentage(Animal::where('fundo_id', $fundoId)
                ->where('activo', true)
                ->selectRaw("COALESCE(NULLIF(estado_reproductivo, ''), 'Sin Registro') as label, COUNT(*) as count")
                ->groupBy('label')
                ->orderBy('count', 'desc')
                ->get()
                ->map(fn ($item) => ['label' => ucfirst($item->label), 'count' => (int) $item->count])
                ->all());

            $altasData = $withPercentage(Animal::where('fundo_id', $fundoId)
                ->where('activo', true)
                ->selectRaw("COALESCE(NULLIF(tipo_alta, ''), 'Desconocido') as label, COUNT(*) as count")
                ->groupBy('label')
                ->orderBy('count', 'desc')
                ->get()
                ->map(fn ($item) => ['label' => ucfirst($item->label), 'count' => (int) $item->count])
                ->all());

            $productivoData = $withPercentage(Animal::where('fundo_id', $fundoId)
                ->where('activo', true)
                ->selectRaw("COALESCE(NULLIF(estado_productivo, ''), 'Sin Registro') as label, COUNT(*) as count")
                ->groupBy('label')
                ->orderBy('count', 'desc')
                ->get()
                ->map(fn ($item) => ['label' => ucfirst($item->label), 'count' => (int) $item->count])
                ->all());

            return [$monthlyData, $especiesData, $estadosData, $altasData, $productivoData];
        });

        $dashboardData = [
            'generatedAt' => now()->format('H:i'),
            'total' => $totalAnimals,
            'hembras' => (int) ($stats->hembras ?? 0),
            'machos' => (int) ($stats->machos ?? 0),
            'aptos' => (int) ($stats->aptos ?? 0),
            'monthly' => $monthlyData,
            'especies' => $especiesData,
            'estados' => $estadosData,
            'altas' => $altasData,
            'productivo' => $productivoData,
        ];

        $hasActiveFilters = collect($this->exportFilters())
            ->except(['sortBy', 'sortDir'])
            ->contains(fn ($value) => $value !== '' && $value !== null);
        $dateBounds = Animal::query()
            ->where('fundo_id', $fundoId)
            ->selectRaw('MIN(fecha_alta) as min_date, MAX(fecha_alta) as max_date')
            ->first();
        $firstYear = $dateBounds?->min_date
            ? CarbonImmutable::parse($dateBounds->min_date)->year
            : now()->year;
        $lastYear = max(
            now()->year,
            $dateBounds?->max_date ? CarbonImmutable::parse($dateBounds->max_date)->year : now()->year
        );
        $availableYears = range($lastYear, $firstYear);
        $statusAnimal = $this->showStatusModal && $this->statusAnimalId
            ? Animal::query()
                ->where('fundo_id', $fundoId)
                ->with('movimientoVenta')
                ->find($this->statusAnimalId)
            : null;

        return view('livewire.animal.index', compact(
            'animales',
            'especies',
            'razas',
            'stats',
            'dashboardData',
            'hasActiveFilters',
            'availableYears',
            'statusAnimal'
        ))
            ->layout('layouts.app');
    }

    protected function recentRecordScopes(): array
    {
        return [
            'animal.animales' => ['model' => Animal::class],
        ];
    }
}
