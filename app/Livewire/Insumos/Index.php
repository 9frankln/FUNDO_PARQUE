<?php

namespace App\Livewire\Insumos;

use App\Models\Fundo;
use App\Models\Insumo;
use App\Models\InsumoLote;
use App\Models\InsumoMovimiento;
use App\Services\InsumoPurchaseService;
use App\Support\InsumoLotCodeAllocator;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasPdfPreviewModal;
use App\Support\PaginationOptions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal, WithPagination;

    private const PER_PAGE_OPTIONS = PaginationOptions::PER_PAGE;

    public const PDF_SECTIONS = [
        'insumos' => [
            'label' => 'Insumos y Materiales',
            'description' => 'Catálogo de insumos, materiales descartables, curación y suministros.',
        ],
        'consumos' => [
            'label' => 'Historial de Consumos',
            'description' => 'Salidas, consumos y mermas de insumos del botiquín.',
        ],
    ];

    public const PDF_COLUMNS = [
        'insumos' => [
            'nombre' => 'Insumo / Material',
            'tipo' => 'Categoría / Tipo',
            'marca' => 'Marca / Fabricante',
            'presentacion' => 'Presentación',
            'stock' => 'Stock Disponible',
            'unidad' => 'Unidad',
            'almacenamiento' => 'Conservación',
            'estado' => 'Estado',
        ],
        'consumos' => [
            'fecha' => 'Fecha / Hora',
            'insumo' => 'Insumo / Lote',
            'tipo' => 'Tipo de Movimiento',
            'cantidad' => 'Cantidad Salida',
            'motivo' => 'Motivo / Detalle',
            'responsable' => 'Responsable / Registró',
        ],
    ];

    private const PDF_DEFAULTS = [
        'insumos' => ['nombre', 'tipo', 'marca', 'presentacion', 'stock', 'unidad', 'estado'],
        'consumos' => ['fecha', 'insumo', 'tipo', 'cantidad', 'motivo', 'responsable'],
    ];

    public bool $showInsumosPdfModal = false;

    public array $insumosPdfSections = [];

    public array $insumosPdfColumns = [];

    #[Url]
    public string $tab = 'inventario'; // 'inventario' | 'consumos'

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $estado = 'todos';

    #[Url]
    public string $tipo = '';

    #[Url(as: 'v_desde')]
    public string $vencimientoDesde = '';

    #[Url(as: 'v_hasta')]
    public string $vencimientoHasta = '';

    #[Url(as: 'ord')]
    public string $orden = 'reciente';

    public int $perPage = 10;

    #[Url(as: 'con_q')]
    public string $searchConsumo = '';

    #[Url(as: 'con_ins')]
    public string $insumoConsumoId = '';

    #[Url(as: 'con_desde')]
    public string $fechaDesdeConsumo = '';

    #[Url(as: 'con_hasta')]
    public string $fechaHastaConsumo = '';

    #[Url(as: 'con_per')]
    public string $periodoConsumo = 'todos';

    public int $perPageConsumos = 10;

    // ÄÄÄ Modal Lote: state & props ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ
    public bool $showLoteModal = false;

    public ?int $loteInsumoId = null;

    public string $loteInsumoNombre = '';

    public string $loteInsumoUnidad = '';

    public string $lTipoIngreso = 'compra';

    public string $lNumeroLote = '';

    #[Locked]
    public int $lCodigoLoteAnio;

    #[Locked]
    public ?int $lCodigoLoteSugerido = null;

    public string $lFechaIngreso = '';

    public string $lFechaVencimiento = '';

    public int|float|string $lCantidad = '';

    public int|float|string $lCostoTotal = '';

    public string $lProveedor = '';

    public string $lComprobante = '';

    public string $lUbicacion = '';

    public string $lObservaciones = '';

    public function mount(): void
    {
        // Flujo directo o desde Finanzas
        if (request()->query('action') === 'nuevo-lote') {
            $fundoId = (int) session('fundo_id');
            $first = Insumo::query()
                ->where(fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->where('activo', true)
                ->orderBy('nombre')
                ->first();

            if ($first && auth()->user()->tienePermiso('medicamentos', 'crear')) {
                $this->openLoteModal($first->id);
            }
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['inventario', 'consumos'], true) ? $tab : 'inventario';
    }

    public function sortByField(string $field): void
    {
        $this->orden = match ($field) {
            'nombre' => $this->orden === 'nombre_asc' ? 'nombre_desc' : ($this->orden === 'nombre_desc' ? 'reciente' : 'nombre_asc'),
            'stock' => $this->orden === 'stock_desc' ? 'stock_asc' : 'stock_desc',
            'vencimiento' => $this->orden === 'vencimiento_asc' ? 'reciente' : 'vencimiento_asc',
            default => 'reciente',
        };
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedVencimientoDesde(): void
    {
        $this->resetPage();
    }

    public function updatedVencimientoHasta(): void
    {
        $this->resetPage();
    }

    public function updatedOrden(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        $this->perPage = in_array((int) $value, self::PER_PAGE_OPTIONS, true) ? (int) $value : 10;
        $this->resetPage();
    }

    public function updatedSearchConsumo(): void
    {
        $this->resetPage('consumosPage');
    }

    public function updatedInsumoConsumoId(): void
    {
        $this->resetPage('consumosPage');
    }

    public function updatedFechaDesdeConsumo(): void
    {
        $this->periodoConsumo = 'personalizado';
        $this->resetPage('consumosPage');
    }

    public function updatedFechaHastaConsumo(): void
    {
        $this->periodoConsumo = 'personalizado';
        $this->resetPage('consumosPage');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'tipo', 'estado', 'vencimientoDesde', 'vencimientoHasta', 'orden']);
        $this->resetPage();
    }

    public function resetConsumoFilters(): void
    {
        $this->reset(['searchConsumo', 'insumoConsumoId', 'fechaDesdeConsumo', 'fechaHastaConsumo', 'periodoConsumo']);
        $this->resetPage('consumosPage');
    }

    public function setPeriodoConsumo(string $periodo): void
    {
        $this->periodoConsumo = $periodo;
        match ($periodo) {
            'hoy' => [
                $this->fechaDesdeConsumo = today()->toDateString(),
                $this->fechaHastaConsumo = today()->toDateString(),
            ],
            'semana' => [
                $this->fechaDesdeConsumo = now()->startOfWeek()->toDateString(),
                $this->fechaHastaConsumo = now()->endOfWeek()->toDateString(),
            ],
            'mes' => [
                $this->fechaDesdeConsumo = now()->startOfMonth()->toDateString(),
                $this->fechaHastaConsumo = now()->endOfMonth()->toDateString(),
            ],
            'anio' => [
                $this->fechaDesdeConsumo = now()->startOfYear()->toDateString(),
                $this->fechaHastaConsumo = now()->endOfYear()->toDateString(),
            ],
            default => [
                $this->fechaDesdeConsumo = '',
                $this->fechaHastaConsumo = '',
            ],
        };
        $this->resetPage('consumosPage');
    }

    public function setPresetVencimiento(string $preset): void
    {
        match ($preset) {
            '30d' => [
                $this->vencimientoDesde = today()->toDateString(),
                $this->vencimientoHasta = today()->addDays(30)->toDateString(),
            ],
            '60d' => [
                $this->vencimientoDesde = today()->toDateString(),
                $this->vencimientoHasta = today()->addDays(60)->toDateString(),
            ],
            'este_anio' => [
                $this->vencimientoDesde = today()->toDateString(),
                $this->vencimientoHasta = now()->endOfYear()->toDateString(),
            ],
            'vencidos' => [
                $this->vencimientoDesde = '',
                $this->vencimientoHasta = today()->subDay()->toDateString(),
            ],
            default => [
                $this->vencimientoDesde = '',
                $this->vencimientoHasta = '',
            ],
        };
        $this->resetPage();
    }

    public function updatedPerPageConsumos($value): void
    {
        $this->perPageConsumos = in_array((int) $value, self::PER_PAGE_OPTIONS, true) ? (int) $value : 10;
        $this->resetPage('consumosPage');
    }

    protected $listeners = [
        'confirmarEliminacionInsumo' => 'deleteInsumo',
    ];

    public function solicitarEliminacionInsumo(int $id): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $insumo = Insumo::query()
            ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($id);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar insumo '.$insumo->nombre.'?',
            'text' => 'Se eliminará el insumo, sus lotes y sus egresos financieros vinculados.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionInsumo',
            'id' => $id,
        ]);
    }

    public function deleteInsumo(InsumoPurchaseService $purchases, $id = null): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        try {
            $fundoId = (int) session('fundo_id');
            $insumo = Insumo::query()
                ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->findOrFail((int) $targetId);

            $purchases->deleteInsumo($insumo);

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Insumo eliminado',
                'text' => 'El insumo y sus egresos vinculados fueron eliminados correctamente.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function toggleActive(int $id): void
    {
        $this->authorizePermission('medicamentos', 'actualizar');
        $item = Insumo::query()
            ->where(fn (Builder $builder) => $builder->where('fundo_id', session('fundo_id'))->orWhereNull('fundo_id'))
            ->findOrFail($id);

        $item->update(['activo' => ! $item->activo]);

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => $item->activo ? 'Insumo activado' : 'Insumo archivado',
        ]);
    }

    public function openLoteModal(int $insumoId): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');
        $item = Insumo::query()
            ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($insumoId);

        $this->loteInsumoId = $item->id;
        $this->loteInsumoNombre = $item->nombre;
        $this->loteInsumoUnidad = $item->unidad_label;
        $this->lTipoIngreso = 'compra';
        $this->lCodigoLoteAnio = now()->year;
        $this->lCodigoLoteSugerido = app(InsumoLotCodeAllocator::class)->preview($fundoId, $this->lCodigoLoteAnio);
        $this->lNumeroLote = str_pad((string) $this->lCodigoLoteSugerido, 3, '0', STR_PAD_LEFT);
        $this->lFechaIngreso = now()->toDateString();
        $this->lFechaVencimiento = '';
        $this->lCantidad = '';
        $this->lCostoTotal = '';
        $this->lProveedor = '';
        $this->lComprobante = '';
        $this->lUbicacion = $item->ubicacion_predeterminada ?? '';
        $this->lObservaciones = '';
        $this->showLoteModal = true;
    }

    public function closeLoteModal(): void
    {
        $this->showLoteModal = false;
        $this->loteInsumoId = null;
        $this->resetErrorBag();
    }

    public function updatedLNumeroLote(mixed $value): void
    {
        $this->lNumeroLote = InsumoLotCodeAllocator::normalizeNumber($value);
    }

    public function saveLote(InsumoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');

        $item = Insumo::query()
            ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->loteInsumoId);

        $this->validate([
            'lTipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
            'lNumeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
            'lFechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            'lFechaVencimiento' => ['nullable', 'date', 'after_or_equal:lFechaIngreso'],
            'lCantidad' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'lCostoTotal' => [$this->lTipoIngreso === 'compra' ? 'required' : 'nullable', 'numeric', $this->lTipoIngreso === 'compra' ? 'min:0.01' : 'min:0', 'max:999999999'],
            'lProveedor' => ['nullable', 'string', 'max:255'],
            'lComprobante' => ['nullable', 'string', 'max:100'],
            'lUbicacion' => ['nullable', 'string', 'max:255'],
            'lObservaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'lCantidad.required' => 'Indica la cantidad del ingreso.',
            'lCantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'lCostoTotal.required' => 'Indica el costo total de la compra.',
            'lNumeroLote.required' => 'Indica los tres dígitos del código de insumo.',
            'lNumeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'lNumeroLote.not_in' => 'La numeración debe iniciar en 001.',
        ]);

        $purchases->createLot($item, [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->lCodigoLoteAnio,
            'codigo_numero' => (int) $this->lNumeroLote,
            'codigo_automatico' => (int) $this->lNumeroLote === $this->lCodigoLoteSugerido,
            'codigo_error_field' => 'lNumeroLote',
            'fecha_ingreso' => $this->lFechaIngreso,
            'fecha_vencimiento' => $this->lFechaVencimiento ?: null,
            'cantidad_inicial' => $this->lCantidad,
            'costo_total' => $this->lTipoIngreso === 'compra' ? $this->lCostoTotal : null,
            'proveedor' => trim($this->lProveedor) ?: null,
            'comprobante' => trim($this->lComprobante) ?: null,
            'ubicacion' => trim($this->lUbicacion) ?: null,
            'observaciones' => trim($this->lObservaciones) ?: null,
        ], $this->lTipoIngreso, auth()->id());

        $this->closeLoteModal();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Entrada registrada',
            'text' => $this->lTipoIngreso === 'compra' ? 'Stock y egreso financiero sincronizados.' : 'Stock actualizado.',
        ]);
    }

    public static function pdfColumnLabels(): array
    {
        return self::PDF_COLUMNS;
    }

    public static function pdfSectionOptions(): array
    {
        return self::PDF_SECTIONS;
    }

    public function openInsumosPdfModal(): void
    {
        $current = $this->tab === 'consumos' ? 'consumos' : 'insumos';
        $this->insumosPdfSections = [$current];
        $this->insumosPdfColumns = self::PDF_DEFAULTS;
        $this->resetValidation();
        $this->showInsumosPdfModal = true;
    }

    public function downloadInsumosReport()
    {
        $this->authorizePermission('medicamentos', 'ver');

        $allowedSections = array_keys(self::PDF_SECTIONS);
        $rules = [
            'insumosPdfSections' => ['required', 'array', 'min:1'],
            'insumosPdfSections.*' => ['required', 'string', 'distinct', Rule::in($allowedSections)],
        ];
        foreach ($this->insumosPdfSections as $section) {
            if (! isset(self::PDF_COLUMNS[$section])) {
                continue;
            }
            $rules['insumosPdfColumns.'.$section] = ['required', 'array', 'min:1'];
            $rules['insumosPdfColumns.'.$section.'.*'] = [
                'required',
                'string',
                'distinct',
                Rule::in(array_keys(self::PDF_COLUMNS[$section])),
            ];
        }
        $this->validate($rules, [
            'insumosPdfSections.required' => 'Selecciona al menos una sección.',
            'insumosPdfSections.min' => 'Selecciona al menos una sección.',
            'insumosPdfSections.*.in' => 'La selección contiene una sección no válida.',
            'insumosPdfSections.*.distinct' => 'No se pueden repetir secciones.',
            'insumosPdfColumns.*.required' => 'Selecciona al menos un campo para esta sección.',
            'insumosPdfColumns.*.min' => 'Selecciona al menos un campo para esta sección.',
            'insumosPdfColumns.*.*.in' => 'La selección contiene un campo no válido.',
            'insumosPdfColumns.*.*.distinct' => 'No se pueden repetir campos.',
        ]);

        $selectedSections = array_keys(array_intersect_key(
            self::PDF_SECTIONS,
            array_flip(array_intersect($allowedSections, $this->insumosPdfSections))
        ));
        $selectedColumns = [];
        foreach ($selectedSections as $section) {
            $selectedColumns[$section] = array_values(array_intersect(
                array_keys(self::PDF_COLUMNS[$section]),
                $this->insumosPdfColumns[$section] ?? []
            ));
        }

        $fundoId = (int) session('fundo_id');
        $fundo = Fundo::withoutGlobalScopes()->findOrFail($fundoId);

        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';

        $reportSections = [];
        foreach ($selectedSections as $section) {
            $rows = $this->queryPdfData($fundoId, $section);
            $reportSections[] = [
                'key' => $section,
                'label' => self::PDF_SECTIONS[$section]['label'],
                'rows' => $rows,
                'columns' => $selectedColumns[$section],
                'columnLabels' => self::PDF_COLUMNS[$section],
                'filterSummary' => $this->insumosFilterSummary($section),
            ];
        }

        $title = count($reportSections) > 1
            ? 'Reporte Integral de Insumos y Consumos'
            : 'Reporte de Insumos: '.$reportSections[0]['label'];

        // Solo cerrar el modal de opciones la PRIMERA vez (no al regenerar desde preview).
        if ($this->exportStep !== 'preview') {
            $this->showInsumosPdfModal = false;
        }

        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.medicamentos', compact(
            'reportSections', 'fundo', 'generatedBy', 'generatedAt', 'administrators', 'title',
            'includeSignatures', 'scale'
        ))->setPaper('a4', 'landscape');

        return $this->setPdfPreview(
            $pdf,
            Str::slug('reporte_insumos_'.now()->format('Ymd_His'), '_').'.pdf',
            $title,
            collect($reportSections)->sum(fn ($s) => count($s['rows']))
        );
    }

    private function queryPdfData(int $fundoId, string $section): array
    {
        $today = today()->toDateString();

        switch ($section) {
            case 'insumos':
                $query = Insumo::query()
                    ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                    ->withCount(['lotes as lotes_activos_count' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)])
                    ->withSum(['lotes as stock_total' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)], 'cantidad_disponible')
                    ->withMin(['lotes as proximo_vencimiento' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)->where('cantidad_disponible', '>', 0)->where(fn ($q) => $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', $today))], 'fecha_vencimiento');

                if ($this->search !== '') {
                    $search = '%'.trim($this->search).'%';
                    $query->where(fn ($q) => $q->where('nombre', 'like', $search)->orWhere('marca_laboratorio', 'like', $search)->orWhere('presentacion', 'like', $search));
                }
                if ($this->tipo !== '') {
                    $query->where('tipo', $this->tipo);
                }

                if ($this->vencimientoDesde !== '') {
                    $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '>=', $this->vencimientoDesde));
                }
                if ($this->vencimientoHasta !== '') {
                    $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '<=', $this->vencimientoHasta));
                }

                $insStockSql = '(SELECT COALESCE(SUM(il.cantidad_disponible), 0) FROM insumo_lotes il WHERE il.insumo_id = insumos.id AND il.fundo_id = ? AND il.activo = 1 AND (il.fecha_vencimiento IS NULL OR il.fecha_vencimiento >= ?))';
                match ($this->estado) {
                    'disponible' => $query->whereRaw($insStockSql.' > 0', [$fundoId, $today]),
                    'stock_bajo' => $query->whereRaw($insStockSql.' <= insumos.stock_minimo', [$fundoId, $today]),
                    'por_vencer' => $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereBetween('fecha_vencimiento', [$today, $limit])),
                    'vencido' => $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '<', $today)),
                    'archivado' => $query->where('activo', false),
                    default => $query->where('activo', true),
                };

                match ($this->orden) {
                    'nombre_desc' => $query->orderByDesc('nombre'),
                    'nombre_asc' => $query->orderBy('nombre', 'asc'),
                    'stock_asc' => $query->orderBy('stock_total', 'asc')->orderBy('nombre'),
                    'stock_desc' => $query->orderBy('stock_total', 'desc')->orderBy('nombre'),
                    'vencimiento_asc' => $query->orderByRaw('proximo_vencimiento IS NULL, proximo_vencimiento ASC'),
                    default => $query->orderByDesc('id'),
                };

                return $query->limit(1000)->get()->map(function (Insumo $i) {
                    $stock = (float) $i->stock_total;
                    $stockMin = (float) $i->stock_minimo;

                    $status = 'Disponible';
                    if (! $i->activo) {
                        $status = 'Archivado';
                    } elseif ($stock <= 0) {
                        $status = 'Agotado';
                    } elseif ($stock <= $stockMin) {
                        $status = 'Stock Bajo';
                    }

                    return [
                        'nombre' => $i->nombre,
                        'tipo' => $i->tipo_label,
                        'marca' => $i->marca_laboratorio ?: '-',
                        'presentacion' => $i->presentacion ?: '-',
                        'stock' => rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') ?: '0',
                        'unidad' => $i->unidad_label,
                        'almacenamiento' => Insumo::STORAGE_CONDITIONS[$i->condicion_almacenamiento] ?? 'Ambiente',
                        'estado' => $status,
                    ];
                })->all();

            case 'consumos':
                $query = InsumoMovimiento::where('fundo_id', $fundoId)
                    ->whereIn('tipo', ['consumo', 'salida_consumo', 'descarte', 'merma_vencimiento', 'ajuste_salida', 'ajuste_negativo'])
                    ->with(['insumo', 'lote', 'animal', 'usuario'])
                    ->latest('fecha_hora');

                if ($this->searchConsumo !== '') {
                    $term = '%'.trim($this->searchConsumo).'%';
                    $query->where(function (Builder $b) use ($term) {
                        $b->whereHas('insumo', fn ($m) => $m->where('nombre', 'like', $term))
                            ->orWhereHas('lote', fn ($l) => $l->where('numero_lote', 'like', $term))
                            ->orWhere('detalle', 'like', $term);
                    });
                }
                if ($this->insumoConsumoId !== '') {
                    $query->where('insumo_id', (int) $this->insumoConsumoId);
                }
                if ($this->fechaDesdeConsumo !== '') {
                    $query->whereDate('fecha_hora', '>=', $this->fechaDesdeConsumo);
                }
                if ($this->fechaHastaConsumo !== '') {
                    $query->whereDate('fecha_hora', '<=', $this->fechaHastaConsumo);
                }

                return $query->limit(1000)->get()->map(function (InsumoMovimiento $mov) {
                    return [
                        'fecha' => $mov->fecha_hora->format('d/m/Y H:i'),
                        'insumo' => ($mov->insumo?->nombre ?? 'Insumo') . ($mov->lote ? " ({$mov->lote->numero_lote})" : ''),
                        'tipo' => InsumoMovimiento::TYPES[$mov->tipo] ?? $mov->tipo,
                        'cantidad' => rtrim(rtrim(number_format(abs((float) $mov->cantidad), 3, '.', ''), '0'), '.') . ' ' . $mov->unidad,
                        'motivo' => $mov->detalle ?: '-',
                        'responsable' => $mov->usuario?->name ?? 'Sistema',
                    ];
                })->all();

            default:
                return [];
        }
    }

    private function insumosFilterSummary(string $section): string
    {
        $parts = [];
        if ($section === 'insumos') {
            if ($this->search !== '') $parts[] = 'Búsqueda: "'.$this->search.'"';
            if ($this->tipo !== '') $parts[] = 'Tipo: '.(Insumo::TYPES[$this->tipo] ?? $this->tipo);
            if ($this->estado !== 'todos') $parts[] = 'Estado: '.ucfirst(str_replace('_', ' ', $this->estado));
            if ($this->vencimientoDesde !== '' || $this->vencimientoHasta !== '') {
                $parts[] = 'Vencimiento: '.($this->vencimientoDesde ?: 'Inicio').' al '.($this->vencimientoHasta ?: 'Fin');
            }
        } elseif ($section === 'consumos') {
            if ($this->searchConsumo !== '') $parts[] = 'Búsqueda: "'.$this->searchConsumo.'"';
            if ($this->fechaDesdeConsumo !== '' || $this->fechaHastaConsumo !== '') {
                $parts[] = 'Período: '.($this->fechaDesdeConsumo ?: 'Inicio').' al '.($this->fechaHastaConsumo ?: 'Fin');
            }
        }

        return count($parts) > 0 ? implode(' · ', $parts) : 'Sin filtros aplicados (todos los registros vigentes)';
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        $today = today()->toDateString();
        $limit = today()->addDays(30)->toDateString();

        $query = Insumo::query()
            ->where(fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->withCount(['lotes as lotes_activos_count' => fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->where('activo', true)])
            ->withSum(['lotes as stock_total' => fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->where('activo', true)], 'cantidad_disponible')
            ->withSum(['lotes as stock_vencido' => fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->where('activo', true)->whereNotNull('fecha_vencimiento')->whereDate('fecha_vencimiento', '<', $today)], 'cantidad_disponible')
            ->withMin(['lotes as proximo_vencimiento' => fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->where('activo', true)->where('cantidad_disponible', '>', 0)->where(fn ($q) => $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', $today))], 'fecha_vencimiento');

        if ($this->search !== '') {
            $search = '%'.trim($this->search).'%';
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('nombre', 'like', $search)
                    ->orWhere('marca_laboratorio', 'like', $search)
                    ->orWhere('presentacion', 'like', $search);
            });
        }
        if ($this->tipo !== '') {
            $query->where('tipo', $this->tipo);
        }

        if ($this->vencimientoDesde !== '') {
            $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '>=', $this->vencimientoDesde));
        }
        if ($this->vencimientoHasta !== '') {
            $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '<=', $this->vencimientoHasta));
        }

        $stockSql = '(SELECT COALESCE(SUM(il.cantidad_disponible), 0) FROM insumo_lotes il WHERE il.insumo_id = insumos.id AND il.fundo_id = ? AND il.activo = 1 AND (il.fecha_vencimiento IS NULL OR il.fecha_vencimiento >= ?))';
        match ($this->estado) {
            'disponible' => $query->whereRaw($stockSql.' > 0', [$fundoId, $today]),
            'stock_bajo' => $query->whereRaw($stockSql.' <= insumos.stock_minimo', [$fundoId, $today]),
            'por_vencer' => $query->whereHas('lotes', fn (Builder $b) => $b
                ->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)
                ->whereBetween('fecha_vencimiento', [$today, $limit])),
            'vencido' => $query->whereHas('lotes', fn (Builder $b) => $b
                ->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)
                ->whereDate('fecha_vencimiento', '<', $today)),
            'archivado' => $query->where('activo', false),
            default => $query->where('activo', true),
        };

        match ($this->orden) {
            'nombre_desc' => $query->orderByDesc('nombre'),
            'nombre_asc' => $query->orderBy('nombre', 'asc'),
            'stock_asc' => $query->orderBy('stock_total', 'asc')->orderBy('nombre'),
            'stock_desc' => $query->orderBy('stock_total', 'desc')->orderBy('nombre'),
            'vencimiento_asc' => $query->orderByRaw('proximo_vencimiento IS NULL, proximo_vencimiento ASC'),
            default => $query->orderByDesc('id'),
        };

        $perPage = in_array((int) $this->perPage, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPage : 10;
        $insumos = $query->paginate($perPage);

        $lotes = InsumoLote::query()->where('fundo_id', $fundoId)->where('activo', true);
        $lotStats = (clone $lotes)->selectRaw(
            'COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= ?) THEN insumo_id END) AS con_stock,
             COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND fecha_vencimiento BETWEEN ? AND ? THEN insumo_id END) AS por_vencer,
             COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND fecha_vencimiento < ? THEN insumo_id END) AS vencidos',
            [$today, $today, $limit, $today]
        )->first();

        $stats = [
            'productos' => Insumo::query()
                ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->where('activo', true)->count(),
            'con_stock' => (int) $lotStats->con_stock,
            'por_vencer' => (int) $lotStats->por_vencer,
            'vencidos' => (int) $lotStats->vencidos,
        ];

        $consumosQuery = InsumoMovimiento::query()
            ->where('fundo_id', $fundoId)
            ->whereIn('tipo', ['consumo', 'descarte', 'ajuste_salida'])
            ->with(['insumo', 'lote', 'animal', 'usuario'])
            ->latest('fecha_hora');

        if ($this->searchConsumo !== '') {
            $term = '%'.trim($this->searchConsumo).'%';
            $consumosQuery->where(function (Builder $b) use ($term) {
                $b->whereHas('insumo', fn ($i) => $i->where('nombre', 'like', $term))
                    ->orWhereHas('lote', fn ($l) => $l->where('numero_lote', 'like', $term))
                    ->orWhereHas('animal', fn ($a) => $a->where('arete', 'like', $term)->orWhere('nombre', 'like', $term))
                    ->orWhere('detalle', 'like', $term);
            });
        }

        if ($this->insumoConsumoId !== '') {
            $consumosQuery->where('insumo_id', (int) $this->insumoConsumoId);
        }

        if ($this->fechaDesdeConsumo !== '') {
            $consumosQuery->whereDate('fecha_hora', '>=', $this->fechaDesdeConsumo);
        }

        if ($this->fechaHastaConsumo !== '') {
            $consumosQuery->whereDate('fecha_hora', '<=', $this->fechaHastaConsumo);
        }

        $perPageCon = in_array((int) $this->perPageConsumos, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPageConsumos : 10;
        $consumos = $consumosQuery->paginate($perPageCon, ['*'], 'consumosPage');

        $insumosLista = Insumo::query()
            ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad_stock']);

        return view('livewire.insumos.index', [
            'insumos' => $insumos,
            'consumos' => $consumos,
            'insumosLista' => $insumosLista,
            'stats' => $stats,
            'tipos' => Insumo::TYPES,
        ])->layout('layouts.app');
    }
}
