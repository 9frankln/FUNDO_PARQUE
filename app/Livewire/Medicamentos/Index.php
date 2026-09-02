<?php

namespace App\Livewire\Medicamentos;

use App\Models\Fundo;
use App\Models\Insumo;
use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\MedicamentoMovimiento;
use App\Models\TratamientoDosis;
use App\Services\MedicamentoPurchaseService;
use App\Support\MedicamentoLotCodeAllocator;
use App\Traits\AuthorizesPermissions;
use App\Support\PaginationOptions;
use App\Exports\MedicamentosTemplateExport;
use App\Services\MedicamentoImportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use App\Traits\HasPdfPreviewModal;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal, WithFileUploads, WithPagination;

    private const PER_PAGE_OPTIONS = PaginationOptions::PER_PAGE;

    public const PDF_SECTIONS = [
        'medicamentos' => [
            'label' => 'Medicamentos y Fármacos',
            'description' => 'Catálogo de medicamentos, tipo, stock y próximo vencimiento.',
        ],
        'insumos' => [
            'label' => 'Insumos y Materiales',
            'description' => 'Material descartable, curación, antisépticos y suministros.',
        ],
        'aplicaciones' => [
            'label' => 'Historial de Aplicaciones',
            'description' => 'Tratamientos, dosis administradas y eventos de sanidad animal.',
        ],
    ];

    public const PDF_COLUMNS = [
        'medicamentos' => [
            'nombre' => 'Medicamento / Fármaco',
            'tipo' => 'Tipo / Clasificación',
            'principio_activo' => 'Principio Activo',
            'concentracion' => 'Concentración',
            'stock' => 'Stock Disponible',
            'unidad' => 'Unidad',
            'proximo_vencimiento' => 'Próx. Vencimiento',
            'lotes_activos' => 'Lotes Activos',
            'almacenamiento' => 'Conservación',
            'estado' => 'Estado',
        ],
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
        'aplicaciones' => [
            'fecha' => 'Fecha / Hora',
            'animal' => 'Animal / Arete',
            'medicamento' => 'Medicamento / Lote',
            'dosis' => 'Dosis Aplicada',
            'via' => 'Vía Administración',
            'diagnostico' => 'Diagnóstico / Motivo',
            'responsable' => 'Responsable / Atendió',
        ],
    ];

    private const PDF_DEFAULTS = [
        'medicamentos' => ['nombre', 'tipo', 'principio_activo', 'stock', 'unidad', 'proximo_vencimiento', 'estado'],
        'insumos' => ['nombre', 'tipo', 'marca', 'presentacion', 'stock', 'unidad', 'estado'],
        'aplicaciones' => ['fecha', 'animal', 'medicamento', 'dosis', 'via', 'diagnostico', 'responsable'],
    ];

    public bool $showMedicamentosPdfModal = false;

    public array $medicamentosPdfSections = [];

    public array $medicamentosPdfColumns = [];

    public bool $showImportModal = false;

    public $importFile = null;

    public array $importSummary = [];

    public bool $importSuccess = false;

    #[Url]
    public string $tab = 'inventario'; // 'inventario' | 'insumos' | 'aplicaciones'

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

    // ÄÄÄ Insumos Tab Filters ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ
    #[Url(as: 'ins_q')]
    public string $searchInsumo = '';

    #[Url(as: 'ins_tipo')]
    public string $tipoInsumo = '';

    #[Url(as: 'ins_est')]
    public string $estadoInsumo = 'todos';

    #[Url(as: 'ins_v_desde')]
    public string $vencimientoDesdeInsumo = '';

    #[Url(as: 'ins_v_hasta')]
    public string $vencimientoHastaInsumo = '';

    #[Url(as: 'ins_ord')]
    public string $ordenInsumo = 'reciente';

    public int $perPageInsumos = 10;

    // ÄÄÄ Modal Insumo Lote ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ
    public bool $showInsumoLoteModal = false;

    public ?int $insumoLoteId = null;

    public string $insumoLoteNombre = '';

    public string $insumoLoteUnidad = '';

    public string $insTipoIngreso = 'compra';

    public string $insNumeroLote = '';

    #[Locked]
    public int $insCodigoLoteAnio;

    #[Locked]
    public ?int $insCodigoLoteSugerido = null;

    public string $insFechaIngreso = '';

    public string $insFechaVencimiento = '';

    public int|float|string $insCantidad = '';

    public int|float|string $insCostoTotal = '';

    public string $insProveedor = '';

    public string $insComprobante = '';

    public string $insUbicacion = '';

    public string $insObservaciones = '';

    // ÄÄÄ Aplicaciones Tab Filters ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ
    #[Url(as: 'app_q')]
    public string $searchAplicacion = '';

    #[Url(as: 'app_med')]
    public string $medicamentoAplicacionId = '';

    #[Url(as: 'app_desde')]
    public string $fechaDesdeAplicacion = '';

    #[Url(as: 'app_hasta')]
    public string $fechaHastaAplicacion = '';

    #[Url(as: 'app_per')]
    public string $periodoAplicacion = 'todos';

    public int $perPageAplicaciones = 10;

    // ÄÄÄ Modal Lote Medicamento: state & props ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ
    public bool $showLoteModal = false;

    public ?int $loteMedicamentoId = null;

    public string $loteMedicamentoNombre = '';

    public string $loteMedicamentoUnidad = '';

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
        // Flujo desde Finanzas: "Registrar compra en Medicamentos" (?action=nuevo-lote)
        if (request()->query('action') === 'nuevo-lote') {
            $fundoId = (int) session('fundo_id');
            $first = Medicamento::query()
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
        $this->tab = in_array($tab, ['inventario', 'insumos', 'aplicaciones'], true) ? $tab : 'inventario';
    }

    public function sortByField(string $field): void
    {
        if ($this->tab === 'insumos') {
            $this->ordenInsumo = match ($field) {
                'nombre' => $this->ordenInsumo === 'nombre_asc' ? 'nombre_desc' : ($this->ordenInsumo === 'nombre_desc' ? 'reciente' : 'nombre_asc'),
                'stock' => $this->ordenInsumo === 'stock_desc' ? 'stock_asc' : 'stock_desc',
                'vencimiento' => $this->ordenInsumo === 'vencimiento_asc' ? 'reciente' : 'vencimiento_asc',
                default => 'reciente',
            };
        } else {
            $this->orden = match ($field) {
                'nombre' => $this->orden === 'nombre_asc' ? 'nombre_desc' : ($this->orden === 'nombre_desc' ? 'reciente' : 'nombre_asc'),
                'stock' => $this->orden === 'stock_desc' ? 'stock_asc' : 'stock_desc',
                'vencimiento' => $this->orden === 'vencimiento_asc' ? 'reciente' : 'vencimiento_asc',
                default => 'reciente',
            };
        }
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTipo(): void
    {
        $this->resetPage();
    }

    public function updatedEstado(): void
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

    public function updatedSearchInsumo(): void
    {
        $this->resetPage('insumosPage');
    }

    public function updatedTipoInsumo(): void
    {
        $this->resetPage('insumosPage');
    }

    public function updatedEstadoInsumo(): void
    {
        $this->resetPage('insumosPage');
    }

    public function updatedVencimientoDesdeInsumo(): void
    {
        $this->resetPage('insumosPage');
    }

    public function updatedVencimientoHastaInsumo(): void
    {
        $this->resetPage('insumosPage');
    }

    public function updatedOrdenInsumo(): void
    {
        $this->resetPage('insumosPage');
    }

    public function updatedPerPageInsumos($value): void
    {
        $this->perPageInsumos = in_array((int) $value, self::PER_PAGE_OPTIONS, true) ? (int) $value : 10;
        $this->resetPage('insumosPage');
    }

    public function updatedSearchAplicacion(): void
    {
        $this->resetPage('aplicacionesPage');
    }

    public function updatedMedicamentoAplicacionId(): void
    {
        $this->resetPage('aplicacionesPage');
    }

    public function updatedFechaDesdeAplicacion(): void
    {
        $this->periodoAplicacion = 'personalizado';
        $this->resetPage('aplicacionesPage');
    }

    public function updatedFechaHastaAplicacion(): void
    {
        $this->periodoAplicacion = 'personalizado';
        $this->resetPage('aplicacionesPage');
    }

    public function updatedPerPageAplicaciones($value): void
    {
        $this->perPageAplicaciones = in_array((int) $value, self::PER_PAGE_OPTIONS, true) ? (int) $value : 10;
        $this->resetPage('aplicacionesPage');
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'tipo', 'estado', 'vencimientoDesde', 'vencimientoHasta', 'orden']);
        $this->resetPage();
    }

    public function resetInsumoFilters(): void
    {
        $this->reset(['searchInsumo', 'tipoInsumo', 'estadoInsumo', 'vencimientoDesdeInsumo', 'vencimientoHastaInsumo', 'ordenInsumo']);
        $this->resetPage('insumosPage');
    }

    public function resetAplicacionFilters(): void
    {
        $this->reset(['searchAplicacion', 'medicamentoAplicacionId', 'fechaDesdeAplicacion', 'fechaHastaAplicacion', 'periodoAplicacion']);
        $this->resetPage('aplicacionesPage');
    }

    public function setPeriodoAplicacion(string $periodo): void
    {
        $this->periodoAplicacion = $periodo;
        match ($periodo) {
            'hoy' => [
                $this->fechaDesdeAplicacion = today()->toDateString(),
                $this->fechaHastaAplicacion = today()->toDateString(),
            ],
            'semana' => [
                $this->fechaDesdeAplicacion = now()->startOfWeek()->toDateString(),
                $this->fechaHastaAplicacion = now()->endOfWeek()->toDateString(),
            ],
            'mes' => [
                $this->fechaDesdeAplicacion = now()->startOfMonth()->toDateString(),
                $this->fechaHastaAplicacion = now()->endOfMonth()->toDateString(),
            ],
            'anio' => [
                $this->fechaDesdeAplicacion = now()->startOfYear()->toDateString(),
                $this->fechaHastaAplicacion = now()->endOfYear()->toDateString(),
            ],
            default => [
                $this->fechaDesdeAplicacion = '',
                $this->fechaHastaAplicacion = '',
            ],
        };
        $this->resetPage('aplicacionesPage');
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
            '90d' => [
                $this->vencimientoDesde = today()->toDateString(),
                $this->vencimientoHasta = today()->addDays(90)->toDateString(),
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

    public function setPresetVencimientoInsumo(string $preset): void
    {
        match ($preset) {
            '30d' => [
                $this->vencimientoDesdeInsumo = today()->toDateString(),
                $this->vencimientoHastaInsumo = today()->addDays(30)->toDateString(),
            ],
            '60d' => [
                $this->vencimientoDesdeInsumo = today()->toDateString(),
                $this->vencimientoHastaInsumo = today()->addDays(60)->toDateString(),
            ],
            'este_anio' => [
                $this->vencimientoDesdeInsumo = today()->toDateString(),
                $this->vencimientoHastaInsumo = now()->endOfYear()->toDateString(),
            ],
            'vencidos' => [
                $this->vencimientoDesdeInsumo = '',
                $this->vencimientoHastaInsumo = today()->subDay()->toDateString(),
            ],
            default => [
                $this->vencimientoDesdeInsumo = '',
                $this->vencimientoHastaInsumo = '',
            ],
        };
        $this->resetPage('insumosPage');
    }

    public function updatedInsNumeroLote(mixed $value): void
    {
        $this->insNumeroLote = \App\Support\InsumoLotCodeAllocator::normalizeNumber($value);
    }

    public function openInsumoLoteModal(int $insumoId): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');
        $insumo = \App\Models\Insumo::query()
            ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($insumoId);

        $this->insumoLoteId = $insumo->id;
        $this->insumoLoteNombre = $insumo->nombre;
        $this->insumoLoteUnidad = $insumo->unidad_label;
        $this->insTipoIngreso = 'compra';
        $this->insCodigoLoteAnio = now()->year;
        $this->insCodigoLoteSugerido = app(\App\Support\InsumoLotCodeAllocator::class)->preview($fundoId, $this->insCodigoLoteAnio);
        $this->insNumeroLote = str_pad((string) $this->insCodigoLoteSugerido, 3, '0', STR_PAD_LEFT);
        $this->insFechaIngreso = now()->toDateString();
        $this->insFechaVencimiento = '';
        $this->insCantidad = '';
        $this->insCostoTotal = '';
        $this->insProveedor = '';
        $this->insComprobante = '';
        $this->insUbicacion = $insumo->ubicacion_predeterminada ?? '';
        $this->insObservaciones = '';
        $this->showInsumoLoteModal = true;
    }

    public function closeInsumoLoteModal(): void
    {
        $this->showInsumoLoteModal = false;
        $this->insumoLoteId = null;
        $this->resetErrorBag();
    }

    public function saveInsumoLote(\App\Services\InsumoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');

        $insumo = \App\Models\Insumo::query()
            ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($this->insumoLoteId);

        $this->validate([
            'insTipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
            'insNumeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
            'insFechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            'insFechaVencimiento' => ['nullable', 'date', 'after_or_equal:insFechaIngreso'],
            'insCantidad' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'insCostoTotal' => [$this->insTipoIngreso === 'compra' ? 'required' : 'nullable', 'numeric', $this->insTipoIngreso === 'compra' ? 'min:0.01' : 'min:0', 'max:999999999'],
            'insProveedor' => ['nullable', 'string', 'max:255'],
            'insComprobante' => ['nullable', 'string', 'max:100'],
            'insUbicacion' => ['nullable', 'string', 'max:255'],
            'insObservaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'insCantidad.required' => 'Indica la cantidad del ingreso.',
            'insCantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'insCostoTotal.required' => 'Indica el costo total de la compra.',
            'insNumeroLote.required' => 'Indica los tres dígitos del código de insumo.',
            'insNumeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'insNumeroLote.not_in' => 'La numeración debe iniciar en 001.',
        ]);

        $purchases->createLot($insumo, [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->insCodigoLoteAnio,
            'codigo_numero' => (int) $this->insNumeroLote,
            'codigo_automatico' => (int) $this->insNumeroLote === $this->insCodigoLoteSugerido,
            'codigo_error_field' => 'insNumeroLote',
            'fecha_ingreso' => $this->insFechaIngreso,
            'fecha_vencimiento' => $this->insFechaVencimiento ?: null,
            'cantidad_inicial' => $this->insCantidad,
            'costo_total' => $this->insTipoIngreso === 'compra' ? $this->insCostoTotal : null,
            'proveedor' => trim($this->insProveedor) ?: null,
            'comprobante' => trim($this->insComprobante) ?: null,
            'ubicacion' => trim($this->insUbicacion) ?: null,
            'observaciones' => trim($this->insObservaciones) ?: null,
        ], $this->insTipoIngreso, auth()->id());

        $this->closeInsumoLoteModal();
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Entrada registrada',
            'text' => $this->insTipoIngreso === 'compra' ? 'Stock y egreso financiero sincronizados.' : 'Stock actualizado.',
        ]);
    }

    protected $listeners = [
        'confirmarEliminacion' => 'delete',
        'confirmarEliminacionInsumo' => 'deleteInsumo',
        'confirmarEliminacionAplicacion' => 'deleteAplicacion',
    ];

    public function toggleActive(int $id): void
    {
        $this->authorizePermission('medicamentos', 'actualizar');
        $medicine = Medicamento::query()
            ->where('fundo_id', session('fundo_id'))
            ->findOrFail($id);
        $medicine->update(['activo' => ! $medicine->activo]);

        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => $medicine->activo ? 'Producto activado' : 'Producto archivado',
        ]);
    }

    public function solicitarEliminacion($id): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $medicine = $this->findMedicine((int) $id);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar medicamento?',
            'text' => 'Se eliminará "'.$medicine->nombre.'" del catálogo.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacion',
            'id' => (int) $id,
        ]);
    }

    public function delete(\App\Services\MedicamentoPurchaseService $purchases, $id = null): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');

        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        try {
            $medicine = $this->findMedicine((int) $targetId);
            $purchases->deleteMedicine($medicine);

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Medicamento eliminado',
                'text' => 'El medicamento y sus egresos vinculados fueron eliminados correctamente.',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function solicitarEliminacionInsumo($id): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $insumo = \App\Models\Insumo::query()
            ->where(fn ($q) => $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail((int) $id);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar insumo '.$insumo->nombre.'?',
            'text' => 'Se eliminará el insumo, sus lotes y sus egresos financieros vinculados.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionInsumo',
            'id' => (int) $id,
        ]);
    }

    public function deleteInsumo(\App\Services\InsumoPurchaseService $purchases, $id = null): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        try {
            $fundoId = (int) session('fundo_id');
            $insumo = \App\Models\Insumo::query()
                ->where(fn ($q) => $q->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
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

    public function solicitarEliminacionAplicacion($id): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $fundoId = (int) session('fundo_id');
        $app = MedicamentoMovimiento::query()
            ->where('fundo_id', $fundoId)
            ->findOrFail((int) $id);

        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar registro de aplicación?',
            'text' => 'Se revertirá el descuento de stock en el lote del botiquín.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionAplicacion',
            'id' => (int) $id,
        ]);
    }

    public function deleteAplicacion($id = null): void
    {
        $this->authorizePermission('medicamentos', 'eliminar');
        $targetId = is_array($id) ? ($id['id'] ?? null) : $id;
        if (! $targetId) {
            return;
        }

        try {
            $fundoId = (int) session('fundo_id');
            DB::transaction(function () use ($fundoId, $targetId) {
                $app = MedicamentoMovimiento::query()
                    ->where('fundo_id', $fundoId)
                    ->lockForUpdate()
                    ->findOrFail((int) $targetId);

                if ($app->medicamento_lote_id) {
                    $lot = MedicamentoLote::query()
                        ->where('fundo_id', $fundoId)
                        ->lockForUpdate()
                        ->find($app->medicamento_lote_id);

                    if ($lot) {
                        $revertedQty = abs((float) $app->cantidad);
                        $lot->increment('cantidad_disponible', $revertedQty);
                    }
                }

                $doseId = $app->tratamiento_dosis_id ?? null;
                if ($doseId) {
                    \App\Models\TratamientoDosis::where('id', $doseId)->delete();
                }

                $app->delete();
            });

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Aplicación eliminada y stock revertido',
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal:toast', [
                'icon' => 'error',
                'title' => 'No se pudo eliminar',
                'text' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    // ÄÄÄ Modal Lote ÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄÄ

    public function openLoteModal(int $id): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $medicine = $this->findMedicine($id);

        $this->loteMedicamentoId = $medicine->id;
        $this->loteMedicamentoNombre = $medicine->nombre;
        $this->loteMedicamentoUnidad = $medicine->unidad_label;

        $this->lTipoIngreso = 'compra';
        $this->lCodigoLoteAnio = now()->year;
        $this->lCodigoLoteSugerido = app(MedicamentoLotCodeAllocator::class)->preview(
            (int) session('fundo_id'),
            $this->lCodigoLoteAnio
        );
        $this->lNumeroLote = str_pad((string) $this->lCodigoLoteSugerido, 3, '0', STR_PAD_LEFT);
        $this->lFechaIngreso = now()->toDateString();
        $this->lFechaVencimiento = '';
        $this->lCantidad = '';
        $this->lCostoTotal = '';
        $this->lProveedor = '';
        $this->lComprobante = '';
        $this->lUbicacion = '';
        $this->lObservaciones = '';
        $this->resetErrorBag();
        $this->showLoteModal = true;
    }

    public function updatedLNumeroLote(mixed $value): void
    {
        $this->lNumeroLote = MedicamentoLotCodeAllocator::normalizeNumber($value);
    }

    public function closeLoteModal(): void
    {
        $this->showLoteModal = false;
    }

    public function saveLote(MedicamentoPurchaseService $purchases): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $fundoId = (int) session('fundo_id');
        $medicine = $this->findMedicine($this->loteMedicamentoId);

        foreach (['lNumeroLote', 'lProveedor', 'lComprobante', 'lUbicacion', 'lObservaciones'] as $field) {
            $this->{$field} = trim($this->{$field});
        }

        $this->validate([
            'lTipoIngreso' => ['required', Rule::in(['compra', 'donacion', 'saldo_inicial'])],
            'lNumeroLote' => ['required', 'regex:/^\d{3}$/', 'not_in:000'],
            'lFechaIngreso' => ['required', 'date', 'before_or_equal:today'],
            'lFechaVencimiento' => ['required', 'date', 'after_or_equal:lFechaIngreso'],
            'lCantidad' => ['required', 'numeric', 'gt:0', 'max:999999999'],
            'lCostoTotal' => ['nullable', 'numeric', 'min:0', 'max:999999999'],
            'lProveedor' => ['nullable', 'string', 'max:255'],
            'lComprobante' => ['nullable', 'string', 'max:100'],
            'lUbicacion' => ['nullable', 'string', 'max:255'],
            'lObservaciones' => ['nullable', 'string', 'max:2000'],
        ], [
            'lNumeroLote.required' => 'Indica los tres dígitos del código de medicamento.',
            'lNumeroLote.regex' => 'La numeración debe contener tres dígitos.',
            'lNumeroLote.not_in' => 'La numeración debe iniciar en 001.',
            'lFechaVencimiento.required' => 'Indica la fecha de vencimiento.',
            'lCantidad.gt' => 'La cantidad debe ser mayor que cero.',
            'lCostoTotal.required' => 'El costo total es obligatorio en compras.',
        ]);

        $lot = $purchases->createLot($medicine, [
            'fundo_id' => $fundoId,
            'codigo_anio' => $this->lCodigoLoteAnio,
            'codigo_numero' => $this->lNumeroLote,
            'codigo_automatico' => (int) $this->lNumeroLote === $this->lCodigoLoteSugerido,
            'codigo_error_field' => 'lNumeroLote',
            'fecha_ingreso' => $this->lFechaIngreso,
            'fecha_vencimiento' => $this->lFechaVencimiento,
            'cantidad_inicial' => $this->lCantidad,
            'costo_total' => $this->lCostoTotal !== '' ? $this->lCostoTotal : null,
            'proveedor' => $this->lProveedor ?: null,
            'comprobante' => $this->lComprobante ?: null,
            'ubicacion' => $this->lUbicacion ?: null,
            'observaciones' => $this->lObservaciones ?: null,
        ], $this->lTipoIngreso, auth()->id());

        $this->showLoteModal = false;
        $this->dispatch('swal:toast', [
            'icon' => 'success',
            'title' => 'Lote ingresado',
            'text' => "Lote {$lot->numero_lote} agregado a {$medicine->nombre}.",
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

    public function openMedicamentosPdfModal(): void
    {
        $current = match ($this->tab) {
            'insumos' => 'insumos',
            'aplicaciones' => 'aplicaciones',
            default => 'medicamentos',
        };
        $this->medicamentosPdfSections = [$current];
        $this->medicamentosPdfColumns = self::PDF_DEFAULTS;
        $this->resetValidation();
        $this->showMedicamentosPdfModal = true;
    }

    public function downloadMedicamentosReport()
    {
        $this->authorizePermission('medicamentos', 'ver');

        $allowedSections = array_keys(self::PDF_SECTIONS);
        $rules = [
            'medicamentosPdfSections' => ['required', 'array', 'min:1'],
            'medicamentosPdfSections.*' => ['required', 'string', 'distinct', Rule::in($allowedSections)],
        ];
        foreach ($this->medicamentosPdfSections as $section) {
            if (! isset(self::PDF_COLUMNS[$section])) {
                continue;
            }
            $rules['medicamentosPdfColumns.'.$section] = ['required', 'array', 'min:1'];
            $rules['medicamentosPdfColumns.'.$section.'.*'] = [
                'required',
                'string',
                'distinct',
                Rule::in(array_keys(self::PDF_COLUMNS[$section])),
            ];
        }
        $this->validate($rules, [
            'medicamentosPdfSections.required' => 'Selecciona al menos una sección.',
            'medicamentosPdfSections.min' => 'Selecciona al menos una sección.',
            'medicamentosPdfSections.*.in' => 'La selección contiene una sección no válida.',
            'medicamentosPdfSections.*.distinct' => 'No se pueden repetir secciones.',
            'medicamentosPdfColumns.*.required' => 'Selecciona al menos un campo para esta sección.',
            'medicamentosPdfColumns.*.min' => 'Selecciona al menos un campo para esta sección.',
            'medicamentosPdfColumns.*.*.in' => 'La selección contiene un campo no válido.',
            'medicamentosPdfColumns.*.*.distinct' => 'No se pueden repetir campos.',
        ]);

        $selectedSections = array_keys(array_intersect_key(
            self::PDF_SECTIONS,
            array_flip(array_intersect($allowedSections, $this->medicamentosPdfSections))
        ));
        $selectedColumns = [];
        foreach ($selectedSections as $section) {
            $selectedColumns[$section] = array_values(array_intersect(
                array_keys(self::PDF_COLUMNS[$section]),
                $this->medicamentosPdfColumns[$section] ?? []
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
                'filterSummary' => $this->medicamentosFilterSummary($section),
            ];
        }

        $title = count($reportSections) > 1
            ? 'Reporte Integral de Botiquín y Fármacos'
            : 'Reporte de Botiquín: '.$reportSections[0]['label'];

        // Solo cerrar el modal de opciones la PRIMERA vez (no al regenerar desde preview).
        if ($this->exportStep !== 'preview') {
            $this->showMedicamentosPdfModal = false;
        }

        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.medicamentos', compact(
            'reportSections', 'fundo', 'generatedBy', 'generatedAt', 'administrators', 'title', 'includeSignatures', 'scale'
        ))->setPaper('a4', 'landscape');

        return $this->setPdfPreview(
            $pdf,
            Str::slug('reporte_botiquin_'.now()->format('Ymd_His'), '_').'.pdf',
            $title,
            count($reportSections)
        );
    }

    private function queryPdfData(int $fundoId, string $section): array
    {
        $today = today()->toDateString();
        $limit = today()->addDays(30)->toDateString();

        switch ($section) {
            case 'medicamentos':
                $query = Medicamento::query()
                    ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                    ->withCount(['lotes as lotes_activos_count' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)])
                    ->withSum(['lotes as stock_total' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)->whereDate('fecha_vencimiento', '>=', $today)], 'cantidad_disponible')
                    ->withMin(['lotes as proximo_vencimiento' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '>=', $today)], 'fecha_vencimiento');

                if ($this->search !== '') {
                    $search = '%'.trim($this->search).'%';
                    $query->where(fn ($q) => $q->where('nombre', 'like', $search)->orWhere('principio_activo', 'like', $search)->orWhere('laboratorio', 'like', $search));
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

                $stockSql = '(SELECT COALESCE(SUM(ml.cantidad_disponible), 0) FROM medicamento_lotes ml WHERE ml.medicamento_id = medicamentos.id AND ml.fundo_id = ? AND ml.activo = 1 AND ml.fecha_vencimiento >= ?)';
                match ($this->estado) {
                    'disponible' => $query->whereRaw($stockSql.' > 0', [$fundoId, $today]),
                    'stock_bajo' => $query->whereRaw($stockSql.' <= medicamentos.stock_minimo', [$fundoId, $today]),
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

                return $query->limit(1000)->get()->map(function (Medicamento $m) {
                    $stock = (float) $m->stock_total;
                    $stockMin = (float) $m->stock_minimo;
                    $exp = $m->proximo_vencimiento ? \Carbon\Carbon::parse($m->proximo_vencimiento)->format('d/m/Y') : 'Sin lote vigente';

                    $status = 'Disponible';
                    if (! $m->activo) {
                        $status = 'Archivado';
                    } elseif ($stock <= 0) {
                        $status = 'Agotado';
                    } elseif ($stock <= $stockMin) {
                        $status = 'Stock Bajo';
                    }

                    return [
                        'nombre' => $m->nombre,
                        'tipo' => $m->tipo_label,
                        'principio_activo' => $m->principio_activo ?: '-',
                        'concentracion' => $m->concentracion ?: '-',
                        'stock' => rtrim(rtrim(number_format($stock, 3, '.', ''), '0'), '.') ?: '0',
                        'unidad' => $m->unidad_label,
                        'proximo_vencimiento' => $exp,
                        'lotes_activos' => (string) $m->lotes_activos_count,
                        'almacenamiento' => Medicamento::STORAGE_CONDITIONS[$m->condicion_almacenamiento] ?? 'Ambiente',
                        'estado' => $status,
                    ];
                })->all();

            case 'insumos':
                $query = Insumo::query()
                    ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                    ->withCount(['lotes as lotes_activos_count' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)])
                    ->withSum(['lotes as stock_total' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)], 'cantidad_disponible')
                    ->withMin(['lotes as proximo_vencimiento' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)->where('cantidad_disponible', '>', 0)->where(fn ($q) => $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', $today))], 'fecha_vencimiento');

                if ($this->searchInsumo !== '') {
                    $search = '%'.trim($this->searchInsumo).'%';
                    $query->where(fn ($q) => $q->where('nombre', 'like', $search)->orWhere('marca_laboratorio', 'like', $search)->orWhere('presentacion', 'like', $search));
                }
                if ($this->tipoInsumo !== '') {
                    $query->where('tipo', $this->tipoInsumo);
                }

                if ($this->vencimientoDesdeInsumo !== '') {
                    $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '>=', $this->vencimientoDesdeInsumo));
                }
                if ($this->vencimientoHastaInsumo !== '') {
                    $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '<=', $this->vencimientoHastaInsumo));
                }

                $insStockSql = '(SELECT COALESCE(SUM(il.cantidad_disponible), 0) FROM insumo_lotes il WHERE il.insumo_id = insumos.id AND il.fundo_id = ? AND il.activo = 1 AND (il.fecha_vencimiento IS NULL OR il.fecha_vencimiento >= ?))';
                match ($this->estadoInsumo) {
                    'disponible' => $query->whereRaw($insStockSql.' > 0', [$fundoId, $today]),
                    'stock_bajo' => $query->whereRaw($insStockSql.' <= insumos.stock_minimo', [$fundoId, $today]),
                    'por_vencer' => $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereBetween('fecha_vencimiento', [$today, $limit])),
                    'vencido' => $query->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '<', $today)),
                    'archivado' => $query->where('activo', false),
                    default => $query->where('activo', true),
                };

                match ($this->ordenInsumo) {
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

            case 'aplicaciones':
                $query = MedicamentoMovimiento::where('fundo_id', $fundoId)
                    ->whereIn('tipo', ['aplicacion', 'aplicacion_animal'])
                    ->with(['medicamento', 'lote', 'animal', 'usuario', 'dosis.eventoSalud'])
                    ->latest('fecha_hora');

                if ($this->searchAplicacion !== '') {
                    $term = '%'.trim($this->searchAplicacion).'%';
                    $query->where(function (Builder $b) use ($term) {
                        $b->whereHas('medicamento', fn ($m) => $m->where('nombre', 'like', $term))
                            ->orWhereHas('lote', fn ($l) => $l->where('numero_lote', 'like', $term))
                            ->orWhereHas('animal', fn ($a) => $a->where('arete', 'like', $term)->orWhere('nombre', 'like', $term))
                            ->orWhere('detalle', 'like', $term);
                    });
                }
                if ($this->medicamentoAplicacionId !== '') {
                    $query->where('medicamento_id', (int) $this->medicamentoAplicacionId);
                }
                if ($this->fechaDesdeAplicacion !== '') {
                    $query->whereDate('fecha_hora', '>=', $this->fechaDesdeAplicacion);
                }
                if ($this->fechaHastaAplicacion !== '') {
                    $query->whereDate('fecha_hora', '<=', $this->fechaHastaAplicacion);
                }

                return $query->limit(1000)->get()->map(function (MedicamentoMovimiento $mov) {
                    return [
                        'fecha' => $mov->fecha_hora->format('d/m/Y H:i'),
                        'animal' => $mov->animal ? "{$mov->animal->arete} - {$mov->animal->nombre}" : 'Sin animal',
                        'medicamento' => ($mov->medicamento?->nombre ?? 'Medicamento') . ($mov->lote ? " ({$mov->lote->numero_lote})" : ''),
                        'dosis' => rtrim(rtrim(number_format(abs((float) $mov->cantidad), 3, '.', ''), '0'), '.') . ' ' . $mov->unidad,
                        'via' => $mov->dosis?->via_administracion ? TratamientoDosis::ADMIN_ROUTES[$mov->dosis->via_administracion] ?? $mov->dosis->via_administracion : '-',
                        'diagnostico' => $mov->dosis?->eventoSalud?->sintomas_diagnostico ?: ($mov->detalle ?: '-'),
                        'responsable' => $mov->dosis?->responsable ?: ($mov->usuario?->name ?? 'Sistema'),
                    ];
                })->all();

            default:
                return [];
        }
    }

    private function medicamentosFilterSummary(string $section): string
    {
        $parts = [];
        if ($section === 'medicamentos') {
            if ($this->search !== '') $parts[] = 'Búsqueda: "'.$this->search.'"';
            if ($this->tipo !== '') $parts[] = 'Tipo: '.(Medicamento::TYPES[$this->tipo] ?? $this->tipo);
            if ($this->estado !== 'todos') $parts[] = 'Estado: '.ucfirst(str_replace('_', ' ', $this->estado));
            if ($this->vencimientoDesde !== '' || $this->vencimientoHasta !== '') {
                $parts[] = 'Vencimiento: '.($this->vencimientoDesde ?: 'Inicio').' al '.($this->vencimientoHasta ?: 'Fin');
            }
        } elseif ($section === 'insumos') {
            if ($this->searchInsumo !== '') $parts[] = 'Búsqueda: "'.$this->searchInsumo.'"';
            if ($this->tipoInsumo !== '') $parts[] = 'Tipo: '.(Insumo::TYPES[$this->tipoInsumo] ?? $this->tipoInsumo);
            if ($this->estadoInsumo !== 'todos') $parts[] = 'Estado: '.ucfirst(str_replace('_', ' ', $this->estadoInsumo));
            if ($this->vencimientoDesdeInsumo !== '' || $this->vencimientoHastaInsumo !== '') {
                $parts[] = 'Vencimiento: '.($this->vencimientoDesdeInsumo ?: 'Inicio').' al '.($this->vencimientoHastaInsumo ?: 'Fin');
            }
        } elseif ($section === 'aplicaciones') {
            if ($this->searchAplicacion !== '') $parts[] = 'Búsqueda: "'.$this->searchAplicacion.'"';
            if ($this->fechaDesdeAplicacion !== '' || $this->fechaHastaAplicacion !== '') {
                $parts[] = 'Período: '.($this->fechaDesdeAplicacion ?: 'Inicio').' al '.($this->fechaHastaAplicacion ?: 'Fin');
            }
        }

        return count($parts) > 0 ? implode(' · ', $parts) : 'Sin filtros aplicados (todos los registros vigentes)';
    }

    public function openImportModal(): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $this->reset('importFile', 'importSummary', 'importSuccess');
        $this->resetValidation('importFile');
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset('importFile', 'importSummary', 'importSuccess');
        $this->resetValidation('importFile');
    }

    public function downloadImportTemplate()
    {
        $this->authorizePermission('medicamentos', 'ver');
        $fundoId = (int) session('fundo_id');
        $fileName = 'plantilla_importacion_medicamentos_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new MedicamentosTemplateExport($fundoId), $fileName);
    }

    public function processImport(MedicamentoImportService $importService): void
    {
        $this->authorizePermission('medicamentos', 'crear');
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'importFile.required' => 'Selecciona un archivo Excel (.xlsx) o CSV.',
            'importFile.mimes' => 'Formato no soportado. Debe ser .xlsx, .xls o .csv.',
            'importFile.max' => 'El archivo no puede exceder los 10MB.',
        ]);

        $fundoId = (int) session('fundo_id');

        try {
            $result = $importService->import($this->importFile->getRealPath(), $fundoId, false);
            $this->importSummary = $result;

            if ($result['imported'] > 0) {
                $this->importSuccess = true;
                session()->flash('swal', [
                    'icon' => 'success',
                    'title' => '¡Importación completada!',
                    'text' => "Se registraron {$result['imported']} medicamentos y lotes de inventario correctamente.",
                ]);
            }
        } catch (\Throwable $e) {
            $this->addError('importFile', 'Error al procesar archivo: '.$e->getMessage());
        }
    }

    private function findMedicine(int $id): Medicamento
    {
        $fundoId = (int) session('fundo_id');

        return Medicamento::query()
            ->where(fn ($query) => $query->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
            ->findOrFail($id);
    }

    public function render()
    {
        $fundoId = (int) session('fundo_id');
        $today = now()->toDateString();
        $limit = now()->addDays(30)->toDateString();

        $emptyPaginator = fn () => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        $medicamentos = $emptyPaginator();
        $insumos = $emptyPaginator();
        $aplicaciones = $emptyPaginator();
        $medicamentosLista = collect();
        $stats = ['productos' => 0, 'con_stock' => 0, 'por_vencer' => 0, 'vencidos' => 0];
        $statsInsumos = ['productos' => 0, 'con_stock' => 0, 'por_vencer' => 0, 'vencidos' => 0];

        if ($this->tab === 'inventario') {
            $query = Medicamento::query()
                ->where(fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->withSum(['lotes as stock_total' => fn (Builder $builder) => $builder
                    ->where('fundo_id', $fundoId)->where('activo', true)
                    ->whereDate('fecha_vencimiento', '>=', $today)], 'cantidad_disponible')
                ->withMin(['lotes as proximo_vencimiento' => fn (Builder $builder) => $builder
                    ->where('fundo_id', $fundoId)
                    ->where('activo', true)
                    ->where('cantidad_disponible', '>', 0)
                    ->whereDate('fecha_vencimiento', '>=', $today)], 'fecha_vencimiento')
                ->withSum(['lotes as stock_vencido' => fn (Builder $builder) => $builder
                    ->where('fundo_id', $fundoId)
                    ->where('activo', true)
                    ->where('cantidad_disponible', '>', 0)
                    ->whereDate('fecha_vencimiento', '<', $today)], 'cantidad_disponible')
                ->withCount(['lotes as lotes_activos_count' => fn (Builder $builder) => $builder
                    ->where('fundo_id', $fundoId)->where('activo', true)->where('cantidad_disponible', '>', 0)]);

            if ($this->search !== '') {
                $search = '%'.trim($this->search).'%';
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('nombre', 'like', $search)
                        ->orWhere('principio_activo', 'like', $search)
                        ->orWhere('laboratorio', 'like', $search)
                        ->orWhere('registro_sanitario', 'like', $search);
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

            $stockSql = '(SELECT COALESCE(SUM(ml.cantidad_disponible), 0) FROM medicamento_lotes ml WHERE ml.medicamento_id = medicamentos.id AND ml.fundo_id = ? AND ml.activo = 1 AND ml.fecha_vencimiento >= ?)';
            match ($this->estado) {
                'disponible' => $query->whereRaw($stockSql.' > 0', [$fundoId, $today]),
                'stock_bajo' => $query->whereRaw($stockSql.' <= medicamentos.stock_minimo', [$fundoId, $today]),
                'por_vencer' => $query->whereHas('lotes', fn (Builder $builder) => $builder
                    ->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)
                    ->whereBetween('fecha_vencimiento', [$today, $limit])),
                'vencido' => $query->whereHas('lotes', fn (Builder $builder) => $builder
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
            $medicamentos = $query->paginate($perPage);
            $lotes = MedicamentoLote::query()->where('fundo_id', $fundoId)->where('activo', true);
            $lotStats = (clone $lotes)->selectRaw(
                'COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND fecha_vencimiento >= ? THEN medicamento_id END) AS con_stock,
                 COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND fecha_vencimiento BETWEEN ? AND ? THEN medicamento_id END) AS por_vencer,
                 COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND fecha_vencimiento < ? THEN medicamento_id END) AS vencidos',
                [$today, $today, $limit, $today]
            )->first();
            $stats = [
                'productos' => Medicamento::query()
                    ->where(fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                    ->where('activo', true)->count(),
                'con_stock' => (int) ($lotStats?->con_stock ?? 0),
                'por_vencer' => (int) ($lotStats?->por_vencer ?? 0),
                'vencidos' => (int) ($lotStats?->vencidos ?? 0),
            ];
        }

        if ($this->tab === 'aplicaciones') {
            $aplicacionesQuery = MedicamentoMovimiento::query()
                ->where('fundo_id', $fundoId)
                ->whereIn('tipo', ['aplicacion', 'aplicacion_animal'])
                ->with([
                    'medicamento',
                    'lote',
                    'animal.especie',
                    'animal.raza',
                    'dosis.eventoSalud',
                    'usuario',
                ])
                ->latest('fecha_hora');

            if ($this->searchAplicacion !== '') {
                $term = '%'.trim($this->searchAplicacion).'%';
                $aplicacionesQuery->where(function (Builder $builder) use ($term) {
                    $builder->whereHas('animal', function (Builder $a) use ($term) {
                        $a->where('arete', 'like', $term)
                            ->orWhere('nombre', 'like', $term);
                    })
                    ->orWhereHas('medicamento', function (Builder $m) use ($term) {
                        $m->where('nombre', 'like', $term);
                    })
                    ->orWhereHas('lote', function (Builder $l) use ($term) {
                        $l->where('numero_lote', 'like', $term);
                    })
                    ->orWhere('detalle', 'like', $term);
                });
            }

            if ($this->medicamentoAplicacionId !== '') {
                $aplicacionesQuery->where('medicamento_id', (int) $this->medicamentoAplicacionId);
            }

            if ($this->fechaDesdeAplicacion !== '') {
                $aplicacionesQuery->whereDate('fecha_hora', '>=', $this->fechaDesdeAplicacion);
            }

            if ($this->fechaHastaAplicacion !== '') {
                $aplicacionesQuery->whereDate('fecha_hora', '<=', $this->fechaHastaAplicacion);
            }

            $perPageApp = in_array((int) $this->perPageAplicaciones, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPageAplicaciones : 10;
            $aplicaciones = $aplicacionesQuery->paginate($perPageApp, ['*'], 'aplicacionesPage');

            $medicamentosLista = Medicamento::query()
                ->where(fn (Builder $builder) => $builder->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'unidad_stock']);
        }

        if ($this->tab === 'insumos') {
            $insQuery = \App\Models\Insumo::query()
                ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                ->withCount(['lotes as lotes_activos_count' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)])
                ->withSum(['lotes as stock_total' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)], 'cantidad_disponible')
                ->withMin(['lotes as proximo_vencimiento' => fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('activo', true)->where('cantidad_disponible', '>', 0)->where(fn ($q) => $q->whereNull('fecha_vencimiento')->orWhereDate('fecha_vencimiento', '>=', $today))], 'fecha_vencimiento');

            if ($this->searchInsumo !== '') {
                $term = '%'.trim($this->searchInsumo).'%';
                $insQuery->where(function (Builder $b) use ($term) {
                    $b->where('nombre', 'like', $term)
                        ->orWhere('marca_laboratorio', 'like', $term)
                        ->orWhere('presentacion', 'like', $term);
                });
            }

            if ($this->tipoInsumo !== '') {
                $insQuery->where('tipo', $this->tipoInsumo);
            }

            if ($this->vencimientoDesdeInsumo !== '') {
                $insQuery->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '>=', $this->vencimientoDesdeInsumo));
            }
            if ($this->vencimientoHastaInsumo !== '') {
                $insQuery->whereHas('lotes', fn (Builder $b) => $b->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)->whereDate('fecha_vencimiento', '<=', $this->vencimientoHastaInsumo));
            }

            $insStockSql = '(SELECT COALESCE(SUM(il.cantidad_disponible), 0) FROM insumo_lotes il WHERE il.insumo_id = insumos.id AND il.fundo_id = ? AND il.activo = 1 AND (il.fecha_vencimiento IS NULL OR il.fecha_vencimiento >= ?))';
            match ($this->estadoInsumo) {
                'disponible' => $insQuery->whereRaw($insStockSql.' > 0', [$fundoId, $today]),
                'stock_bajo' => $insQuery->whereRaw($insStockSql.' <= insumos.stock_minimo', [$fundoId, $today]),
                'por_vencer' => $insQuery->whereHas('lotes', fn (Builder $b) => $b
                    ->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)
                    ->whereBetween('fecha_vencimiento', [$today, $limit])),
                'vencido' => $insQuery->whereHas('lotes', fn (Builder $b) => $b
                    ->where('fundo_id', $fundoId)->where('cantidad_disponible', '>', 0)
                    ->whereDate('fecha_vencimiento', '<', $today)),
                'archivado' => $insQuery->where('activo', false),
                default => $insQuery->where('activo', true),
            };

            match ($this->ordenInsumo) {
                'nombre_desc' => $insQuery->orderByDesc('nombre'),
                'nombre_asc' => $insQuery->orderBy('nombre', 'asc'),
                'stock_asc' => $insQuery->orderBy('stock_total', 'asc')->orderBy('nombre'),
                'stock_desc' => $insQuery->orderBy('stock_total', 'desc')->orderBy('nombre'),
                'vencimiento_asc' => $insQuery->orderByRaw('proximo_vencimiento IS NULL, proximo_vencimiento ASC'),
                default => $insQuery->orderByDesc('id'),
            };

            $perPageIns = in_array((int) $this->perPageInsumos, self::PER_PAGE_OPTIONS, true) ? (int) $this->perPageInsumos : 10;
            $insumos = $insQuery->paginate($perPageIns, ['*'], 'insumosPage');

            $insumoLotes = \App\Models\InsumoLote::query()->where('fundo_id', $fundoId)->where('activo', true);
            $insLotStats = (clone $insumoLotes)->selectRaw(
                'COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND (fecha_vencimiento IS NULL OR fecha_vencimiento >= ?) THEN insumo_id END) AS con_stock,
                 COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND fecha_vencimiento BETWEEN ? AND ? THEN insumo_id END) AS por_vencer,
                 COUNT(DISTINCT CASE WHEN cantidad_disponible > 0 AND fecha_vencimiento < ? THEN insumo_id END) AS vencidos',
                [$today, $today, $limit, $today]
            )->first();

            $statsInsumos = [
                'productos' => \App\Models\Insumo::query()
                    ->where(fn (Builder $b) => $b->where('fundo_id', $fundoId)->orWhereNull('fundo_id'))
                    ->where('activo', true)->count(),
                'con_stock' => (int) ($insLotStats?->con_stock ?? 0),
                'por_vencer' => (int) ($insLotStats?->por_vencer ?? 0),
                'vencidos' => (int) ($insLotStats?->vencidos ?? 0),
            ];
        }

        return view('livewire.medicamentos.index', [
            'medicamentos' => $medicamentos,
            'insumos' => $insumos,
            'aplicaciones' => $aplicaciones,
            'medicamentosLista' => $medicamentosLista,
            'stats' => $stats,
            'statsInsumos' => $statsInsumos,
            'tipos' => Medicamento::TYPES,
            'tiposInsumos' => \App\Models\Insumo::TYPES,
        ])->layout('layouts.app');
    }
}
