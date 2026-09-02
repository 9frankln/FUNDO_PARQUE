<?php

namespace App\Livewire\Monitoreo;

use App\Models\AlertaProgramada;
use App\Models\Fundo;
use App\Models\Parto;
use App\Models\SanidadRegistro;
use App\Services\AuditLogger;
use App\Services\MedicamentoInventoryService;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasPdfPreviewModal;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal, HasRecentRecord, WithPagination;

    public $tab = 'sanidad';

    public $perPage = 10;

    // Sanidad filters
    public $searchSanidad = '';

    public $sanidadCategoria = '';

    public $sanidadEstado = '';

    public $sanidadFechaDesde = '';

    public $sanidadFechaHasta = '';

    // Partos filters
    public $searchParto = '';

    public $partoTipo = '';

    public $partoCondicionMadre = '';

    public $partoCriaEstado = '';

    public $partoCriaSexo = '';

    public $partoFechaDesde = '';

    public $partoFechaHasta = '';

    // Alertas filters
    public $searchAlerta = '';

    public $alertaTipo = '';

    public $alertaFiltroLeida = '0';

    public $alertaFechaDesde = '';

    public $alertaFechaHasta = '';

    public $showMonitoreoPdfModal = false;

    public $monitoreoPdfSections = ['sanidad'];

    public $monitoreoPdfColumns = [];

    // Modal de borrado de alertas (solo admin)
    public bool $showDeleteAlertaModal = false;

    public ?int $deleteAlertaId = null;

    public array $deleteAlertaData = [];

    // Selección masiva de alertas (solo admin)
    public array $selectedAlertas = [];

    public bool $showDeleteAlertasMasivoModal = false;

    public ?int $deleteAlertasMasivoCount = null;

    public string $deleteAlertasMasivoMode = 'seleccion'; // seleccion | filtradas

    // Seguimiento de casos clínicos: modal de recuperación
    public bool $showRecuperarCasoModal = false;

    public ?int $recuperarCasoId = null;

    public array $recuperarCasoData = [];

    public string $recuperarCasoFecha = '';

    public string $recuperarCasoObservaciones = '';

    protected $queryString = ['tab', 'perPage'];

    protected $listeners = [
        'confirmarEliminacionSanidad' => 'deleteSanidad',
        'confirmarEliminacionParto' => 'deleteParto',
    ];

    public function updatedSearchSanidad(): void
    {
        $this->resetPage('sanPage');
    }

    public function updatedSanidadCategoria(): void
    {
        $this->resetPage('sanPage');
    }

    public function updatedSanidadEstado(): void
    {
        $this->resetPage('sanPage');
    }

    public function updatedSanidadFechaDesde(): void
    {
        $this->resetPage('sanPage');
    }

    public function updatedSanidadFechaHasta(): void
    {
        $this->resetPage('sanPage');
    }

    public function updatedSearchParto(): void
    {
        $this->resetPage('partoPage');
    }

    public function updatedPartoTipo(): void
    {
        $this->resetPage('partoPage');
    }

    public function updatedPartoCondicionMadre(): void
    {
        $this->resetPage('partoPage');
    }

    public function updatedPartoCriaEstado(): void
    {
        $this->resetPage('partoPage');
    }

    public function updatedPartoCriaSexo(): void
    {
        $this->resetPage('partoPage');
    }

    public function updatedPartoFechaDesde(): void
    {
        $this->resetPage('partoPage');
    }

    public function updatedPartoFechaHasta(): void
    {
        $this->resetPage('partoPage');
    }

    public function updatedSearchAlerta(): void
    {
        $this->resetPage('alertaPage');
    }

    public function updatedAlertaTipo(): void
    {
        $this->resetPage('alertaPage');
    }

    public function updatedAlertaFiltroLeida(): void
    {
        $this->resetPage('alertaPage');
    }

    public function updatedAlertaFechaDesde(): void
    {
        $this->resetPage('alertaPage');
    }

    public function updatedAlertaFechaHasta(): void
    {
        $this->resetPage('alertaPage');
    }

    public function resetSanidadFilters(): void
    {
        $this->reset(['searchSanidad', 'sanidadCategoria', 'sanidadEstado', 'sanidadFechaDesde', 'sanidadFechaHasta']);
        $this->resetPage('sanPage');
    }

    public function resetPartoFilters(): void
    {
        $this->reset(['searchParto', 'partoTipo', 'partoCondicionMadre', 'partoCriaEstado', 'partoCriaSexo', 'partoFechaDesde', 'partoFechaHasta']);
        $this->resetPage('partoPage');
    }

    public function resetAlertaFilters(): void
    {
        $this->reset(['searchAlerta', 'alertaTipo', 'alertaFiltroLeida', 'alertaFechaDesde', 'alertaFechaHasta']);
        $this->alertaFiltroLeida = '0';
        $this->resetPage('alertaPage');
    }

    public function marcarAlertaLeida($id)
    {
        $this->authorizePermission('monitoreo', 'actualizar');

        $alerta = AlertaProgramada::find($id);
        if ($alerta) {
            $alerta->leida = true;
            $alerta->save();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Alerta Leída',
                'text' => 'La notificación ha sido marcada como leída.',
            ]);
        }
    }

    public function marcarDosisAplicada(int $dosisId): void
    {
        $this->authorizePermission('monitoreo', 'actualizar');

        try {
            $dosis = app(MedicamentoInventoryService::class)
                ->markDoseApplied($dosisId, (int) session('fundo_id'));
        } catch (ValidationException $exception) {
            $this->dispatch('swal:modal', [
                'title' => 'No se pudo aplicar la dosis',
                'text' => collect($exception->errors())->flatten()->first() ?: 'Revisa las existencias vigentes.',
                'icon' => 'warning',
            ]);

            return;
        }

        // Si era la última dosis pendiente, sugerimos marcar recuperado (no automático)
        $pendientes = $dosis->eventoSalud->dosisPendientes()->count();
        $this->dispatch('swal:toast', [
            'title' => 'Dosis aplicada',
            'text' => $pendientes > 0
                ? 'Aplicación registrada. Quedan '.$pendientes.' aplicación(es) pendiente(s).'
                : 'Plan completo. Ya puedes finalizar el seguimiento.',
            'icon' => 'success',
        ]);
    }

    public function openRecuperarCasoModal(int $id): void
    {
        $this->authorizePermission('monitoreo', 'actualizar');

        $san = SanidadRegistro::with('animal', 'dosisPlan')
            ->where('fundo_id', session('fundo_id'))
            ->find($id);
        if (! $san || $san->estado_seguimiento === 'completado') {
            $this->dispatch('swal:toast', [
                'title' => 'Sin cambios',
                'text' => 'El seguimiento ya esta completado.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->recuperarCasoId = $san->id;
        $this->recuperarCasoFecha = now()->toDateString();
        $this->recuperarCasoObservaciones = '';
        $this->recuperarCasoData = [
            'arete' => $san->animal?->arete ?? 'Archivado',
            'nombre' => $san->animal?->nombre ?? 'Sin nombre',
            'diagnostico' => $san->sintomas_diagnostico ?? 'Sin detalle',
            'categoria' => $san->categoria_salud_label,
            'fecha' => $san->fecha_evento?->format('d/m/Y') ?? '-',
            'aplicadas' => $san->dosisPlan->where('aplicada', true)->count(),
            'pendientes' => $san->dosisPlan->where('aplicada', false)->count(),
        ];
        $this->resetValidation();
        $this->showRecuperarCasoModal = true;
    }

    public function closeRecuperarCasoModal(): void
    {
        $this->showRecuperarCasoModal = false;
        $this->recuperarCasoId = null;
        $this->recuperarCasoData = [];
        $this->recuperarCasoFecha = '';
        $this->recuperarCasoObservaciones = '';
    }

    public function confirmarRecuperacion(): void
    {
        $this->authorizePermission('monitoreo', 'actualizar');

        $this->validate([
            'recuperarCasoFecha' => ['required', 'date', 'before_or_equal:today'],
            'recuperarCasoObservaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'recuperarCasoFecha.required' => 'Indica la fecha de recuperación.',
            'recuperarCasoFecha.before_or_equal' => 'La fecha no puede ser futura.',
        ]);

        $san = SanidadRegistro::with('animal', 'dosisPlan')
            ->where('fundo_id', session('fundo_id'))
            ->find($this->recuperarCasoId);
        if (! $san) {
            $this->closeRecuperarCasoModal();

            return;
        }

        $san->update([
            'estado_seguimiento' => 'completado',
            'estado_clinico' => 'recuperada',
            'fecha_cierre' => $this->recuperarCasoFecha,
            'observaciones_cierre' => mb_strtolower(trim($this->recuperarCasoObservaciones), 'UTF-8') ?: null,
        ]);

        // Cerrar alertas de cuarentena del animal abiertas (leída = true)
        if ($san->animal) {
            AlertaProgramada::where('fundo_id', session('fundo_id'))
                ->where('animal_id', $san->animal->id)
                ->where('tipo', 'cuarentena')
                ->where('leida', false)
                ->update(['leida' => true]);
        }

        $this->closeRecuperarCasoModal();
        $this->resetPage('sanPage');

        $this->dispatch('swal:toast', [
            'title' => 'Seguimiento finalizado',
            'text' => ($san->animal?->arete ?? 'El animal').' quedo con el evento completado.',
            'icon' => 'success',
        ]);
    }

    public function solicitarEliminacionSanidad($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar evento de salud?',
            'text' => 'Se borraran el evento y su plan de dosis.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionSanidad',
            'id' => $id,
        ]);
    }

    public function deleteSanidad($id)
    {
        $this->authorizePermission('monitoreo', 'eliminar');
        $fundoId = (int) session('fundo_id');

        $san = SanidadRegistro::with('dosisPlan')->where('fundo_id', $fundoId)->find($id);
        if ($san) {
            DB::transaction(function () use ($san) {
                app(MedicamentoInventoryService::class)->revertDoses($san->dosisPlan);
                $san->dosisPlan()->delete();
                $san->delete();
            });

            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Registro Eliminado',
                'text' => 'Evento de salud eliminado y existencias revertidas.',
            ]);
        }
    }

    public function solicitarEliminacionParto($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar parto?',
            'text' => 'Se borrará el registro de nacimiento.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionParto',
            'id' => $id,
        ]);
    }

    public function deleteParto($id)
    {
        $this->authorizePermission('monitoreo', 'eliminar');

        $part = Parto::find($id);
        if ($part) {
            $part->delete();
            $this->dispatch('swal:toast', [
                'icon' => 'success',
                'title' => 'Parto Eliminado',
                'text' => 'Registro de parto eliminado.',
            ]);
        }
    }

    // Modal de detalle de un caso clínico / preventivo
    public bool $showVerCasoModal = false;

    public ?int $verCasoId = null;

    public array $verCasoData = [];

    public function openVerCasoModal(int $id): void
    {
        $this->authorizePermission('monitoreo', 'leer');

        $san = SanidadRegistro::with(['animal', 'medicamento', 'fotos', 'dosisPlan.medicamento'])
            ->where('fundo_id', session('fundo_id'))
            ->find($id);
        if (! $san) {
            $this->dispatch('swal:toast', [
                'title' => 'No encontrado',
                'text' => 'El caso ya no existe o fue eliminado.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->verCasoId = $san->id;
        $this->verCasoData = [
            'categoria' => $san->categoria_salud,
            'categoria_label' => $san->categoria_salud_label,
            'subtipo' => $san->subtipo,
            'severidad' => $san->severidad,
            'ubicacion_corporal' => $san->ubicacion_corporal,
            'arete' => $san->animal?->arete ?? 'Archivado',
            'nombre' => $san->animal?->nombre ?? 'Sin nombre',
            'animal_url' => $san->animal ? route('animal.show', $san->animal->id) : null,
            'fecha_evento' => $san->fecha_evento?->format('d/m/Y') ?? '-',
            'sintomas' => $san->sintomas_diagnostico ?? '-',
            'tratamiento' => $san->tratamiento ?? '-',
            'medicamento' => $san->medicamento?->nombre ?? $san->medicamento_nombre ?? '-',
            'dosis_via' => $san->dosis_via ?? '-',
            'estado' => $san->estado_seguimiento,
            'estado_label' => $san->estado_seguimiento_label,
            'fecha_cierre' => $san->fecha_cierre?->format('d/m/Y') ?? null,
            'observaciones_cierre' => $san->observaciones_cierre ?? null,
            'producto_marca' => $san->producto_marca ?? '-',
            'proposito' => $san->proposito ?? '-',
            'responsable' => $san->responsable ?? '-',
            'proxima_dosis' => $san->proxima_dosis?->format('d/m/Y') ?? null,
            'dosisPlan' => $san->dosisPlan->map(fn ($d) => [
                'numero' => $d->numero,
                'nombre' => $d->medicamento?->nombre ?? $d->medicamento_nombre ?? 'Medicamento',
                'dosis' => $d->dosis,
                'via' => $d->via,
                'fecha_programada' => $d->fecha_programada?->format('d/m/Y') ?? '-',
                'fecha_aplicada' => $d->fecha_aplicada?->format('d/m/Y') ?? null,
                'aplicada' => (bool) $d->aplicada,
            ])->all(),
            'fotos' => $san->fotos->map(fn ($f) => [
                'url' => route('record-photo.show', $f),
                'encuadre' => $f->encuadre,
            ])->all(),
        ];
        $this->showVerCasoModal = true;
    }

    public function closeVerCasoModal(): void
    {
        $this->showVerCasoModal = false;
        $this->verCasoId = null;
        $this->verCasoData = [];
    }

    public function getPuedeBorrarAlertasProperty(): bool
    {
        return $this->currentUserIsFundoAdmin();
    }

    public function openDeleteAlertaModal(int $id): void
    {
        $this->authorizeFundoAdmin();

        $alerta = AlertaProgramada::with('animal')->find($id);
        if (! $alerta) {
            $this->dispatch('swal:toast', [
                'title' => 'No encontrada',
                'text' => 'La alerta ya no existe o fue eliminada.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->deleteAlertaId = $alerta->id;
        $this->deleteAlertaData = [
            'fecha' => $alerta->fecha_alerta?->format('d/m/Y') ?? '-',
            'animal' => $alerta->animal
                ? trim(($alerta->animal->arete ?? '').' '.($alerta->animal->nombre ?? ''))
                : 'Sin animal',
            'tipo' => $alerta->tipo,
            'mensaje' => $alerta->mensaje ?? 'Sin mensaje',
            'leida' => (bool) $alerta->leida,
        ];
        $this->showDeleteAlertaModal = true;
    }

    public function closeDeleteAlertaModal(): void
    {
        $this->showDeleteAlertaModal = false;
        $this->deleteAlertaId = null;
        $this->deleteAlertaData = [];
    }

    public function deleteAlerta(): void
    {
        $this->authorizeFundoAdmin();

        $alerta = AlertaProgramada::where('fundo_id', session('fundo_id'))->find($this->deleteAlertaId);
        if (! $alerta) {
            $this->closeDeleteAlertaModal();
            $this->dispatch('swal:toast', [
                'title' => 'No encontrada',
                'text' => 'La alerta ya no existe o fue eliminada.',
                'icon' => 'warning',
            ]);

            return;
        }

        $detalle = [
            'alerta_id' => $alerta->id,
            'tipo' => $alerta->tipo,
            'fecha_alerta' => $alerta->fecha_alerta?->toDateString(),
            'leida' => (bool) $alerta->leida,
        ];

        // La alerta es una fila independiente: borrarla NO toca ni al animal
        // (FK nullOnDelete) ni a las dosis del caso clínico.
        $alerta->delete();

        $this->closeDeleteAlertaModal();
        $this->resetPage('alertaPage');

        app(AuditLogger::class)->record(
            'alerta.eliminada',
            'monitoreo',
            'Eliminó la alerta programada #'.$alerta->id.' ('.($detalle['tipo'] ?? 'n/a').') del '.($detalle['fecha_alerta'] ?? 'n/a').'.',
            metadata: $detalle,
        );

        $this->dispatch('swal:toast', [
            'title' => '¡Eliminada!',
            'text' => 'Alerta programada eliminada correctamente.',
            'icon' => 'success',
        ]);
    }

    public function toggleAlertaSeleccion(int $id): void
    {
        if (! $this->currentUserIsFundoAdmin()) {
            return;
        }

        $key = array_search($id, $this->selectedAlertas, true);
        if ($key === false) {
            $this->selectedAlertas[] = $id;
        } else {
            unset($this->selectedAlertas[$key]);
            $this->selectedAlertas = array_values($this->selectedAlertas);
        }
    }

    public function toggleSelectAllAlertas(): void
    {
        $this->authorizeFundoAdmin();

        $ids = $this->filteredAlertaQuery()->pluck('id')->all();
        if ($ids === []) {
            return;
        }

        $allSelected = count(array_diff($ids, $this->selectedAlertas)) === 0;
        $this->selectedAlertas = $allSelected ? [] : $ids;
    }

    public function clearAlertasSeleccion(): void
    {
        $this->selectedAlertas = [];
    }

    public function openDeleteAlertasMasivoModal(string $mode = 'seleccion'): void
    {
        $this->authorizeFundoAdmin();

        $mode = in_array($mode, ['seleccion', 'filtradas'], true) ? $mode : 'seleccion';
        $query = $mode === 'seleccion'
            ? AlertaProgramada::where('fundo_id', session('fundo_id'))->whereIn('id', $this->selectedAlertas)
            : $this->filteredAlertaQuery();

        $count = (clone $query)->count();
        if ($count === 0) {
            $this->dispatch('swal:toast', [
                'title' => 'Nada que eliminar',
                'text' => $mode === 'seleccion'
                    ? 'Selecciona al menos una alerta para eliminar.'
                    : 'No hay alertas que coincidan con los filtros activos.',
                'icon' => 'warning',
            ]);

            return;
        }

        $this->deleteAlertasMasivoMode = $mode;
        $this->deleteAlertasMasivoCount = $count;
        $this->showDeleteAlertasMasivoModal = true;
    }

    public function closeDeleteAlertasMasivoModal(): void
    {
        $this->showDeleteAlertasMasivoModal = false;
        $this->deleteAlertasMasivoCount = null;
    }

    public function deleteAlertasMasivo(): void
    {
        $this->authorizeFundoAdmin();

        $query = $this->deleteAlertasMasivoMode === 'seleccion'
            ? AlertaProgramada::where('fundo_id', session('fundo_id'))->whereIn('id', $this->selectedAlertas)
            : $this->filteredAlertaQuery();

        $count = (clone $query)->count();
        if ($count === 0) {
            $this->closeDeleteAlertasMasivoModal();
            $this->dispatch('swal:toast', [
                'title' => 'Nada que eliminar',
                'text' => 'Las alertas ya no existen o fueron eliminadas.',
                'icon' => 'warning',
            ]);

            return;
        }

        $resumen = (clone $query)
            ->select('tipo')
            ->distinct()
            ->pluck('tipo')
            ->all();

        // Borrado masivo en transacción. Las alertas son filas independientes:
        // no se tocan animales (FK nullOnDelete) ni dosis de casos clínicos.
        DB::transaction(function () use ($query): void {
            $query->delete();
        });

        $this->selectedAlertas = [];
        $this->closeDeleteAlertasMasivoModal();
        $this->resetPage('alertaPage');

        app(AuditLogger::class)->record(
            'alertas.eliminadas_masivo',
            'monitoreo',
            "Eliminó {$count} alerta(s) programada(s) de forma masiva.",
            metadata: [
                'cantidad' => $count,
                'modo' => $this->deleteAlertasMasivoMode,
                'tipos' => $resumen,
            ],
        );

        $this->dispatch('swal:toast', [
            'title' => '¡Eliminadas!',
            'text' => "Se eliminaron {$count} alerta(s) programada(s) correctamente.",
            'icon' => 'success',
        ]);
    }

    private function filteredAlertaQuery()
    {
        $query = AlertaProgramada::where('fundo_id', session('fundo_id'));
        $this->applyAlertaFilters($query);

        return $query;
    }

    private function currentUserIsFundoAdmin(): bool
    {
        auth()->user()?->loadMissing('fundos');
        $membership = auth()->user()?->fundos->firstWhere('id', (int) session('fundo_id'));

        return (bool) $membership?->pivot?->es_administrador;
    }

    private function authorizeFundoAdmin(): void
    {
        abort_unless($this->currentUserIsFundoAdmin(), 403, 'Solo administradores del fundo pueden realizar esta acción.');
    }

    private const PDF_SECTIONS = [
        'sanidad' => ['label' => 'Historial de salud', 'description' => 'Eventos, atenciones y planes de dosis'],
        'partos' => ['label' => 'Reproducción', 'description' => 'Madres, crías y resultados'],
        'alertas' => ['label' => 'Alertas', 'description' => 'Pendientes y archivadas'],
    ];

    private const PDF_COLUMNS = [
        'sanidad' => [
            'fecha' => 'Fecha',
            'animal' => 'Animal',
            'categoria' => 'Evento',
            'subtipo' => 'Tipo específico',
            'hallazgo' => 'Hallazgo / Motivo',
            'atencion' => 'Atención / Indicaciones',
            'dosis' => 'Plan de dosis',
            'estado' => 'Seguimiento',
            'evidencia' => 'Evidencia adjunta',
        ],
        'partos' => [
            'fecha' => 'Fecha parto',
            'madre' => 'Madre',
            'condicion' => 'Condición madre',
            'tipo' => 'Tipo parto',
            'cria' => 'Cría',
            'sexo' => 'Sexo cría',
            'estado_cria' => 'Estado cría',
            'peso' => 'Peso cría',
            'observaciones' => 'Observaciones',
        ],
        'alertas' => [
            'fecha' => 'Fecha programada',
            'animal' => 'Animal',
            'tipo' => 'Tipo',
            'mensaje' => 'Mensaje',
            'estado' => 'Estado',
        ],
    ];

    public static function pdfColumnLabels(): array
    {
        return self::PDF_COLUMNS;
    }

    public static function pdfSectionOptions(): array
    {
        return self::PDF_SECTIONS;
    }

    private const PDF_DEFAULTS = [
        'sanidad' => ['fecha', 'animal', 'categoria', 'subtipo', 'hallazgo', 'atencion', 'dosis', 'estado'],
        'partos' => ['fecha', 'madre', 'tipo', 'cria', 'sexo', 'peso'],
        'alertas' => ['fecha', 'animal', 'tipo', 'mensaje', 'estado'],
    ];

    public function openMonitoreoPdfModal(): void
    {
        $section = array_key_exists($this->tab, self::PDF_COLUMNS) ? $this->tab : 'sanidad';
        $this->monitoreoPdfSections = [$section];
        $this->monitoreoPdfColumns = self::PDF_DEFAULTS;
        $this->resetValidation();
        $this->showMonitoreoPdfModal = true;
    }

    public function downloadMonitoreoReport()
    {
        $this->authorizePermission('monitoreo', 'exportar');

        $allowedSections = array_keys(self::PDF_SECTIONS);
        $rules = [
            'monitoreoPdfSections' => ['required', 'array', 'min:1'],
            'monitoreoPdfSections.*' => ['required', 'string', 'distinct', Rule::in($allowedSections)],
        ];
        foreach ($this->monitoreoPdfSections as $section) {
            if (! isset(self::PDF_COLUMNS[$section])) {
                continue;
            }
            $rules['monitoreoPdfColumns.'.$section] = ['required', 'array', 'min:1'];
            $rules['monitoreoPdfColumns.'.$section.'.*'] = [
                'required',
                'string',
                'distinct',
                Rule::in(array_keys(self::PDF_COLUMNS[$section])),
            ];
        }
        $this->validate($rules, [
            'monitoreoPdfSections.required' => 'Selecciona al menos una sección.',
            'monitoreoPdfSections.min' => 'Selecciona al menos una sección.',
            'monitoreoPdfSections.*.in' => 'La selección contiene una sección no válida.',
            'monitoreoPdfSections.*.distinct' => 'No se pueden repetir secciones.',
            'monitoreoPdfColumns.*.required' => 'Selecciona al menos un campo para esta sección.',
            'monitoreoPdfColumns.*.min' => 'Selecciona al menos un campo para esta sección.',
            'monitoreoPdfColumns.*.*.in' => 'La selección contiene un campo no válido.',
            'monitoreoPdfColumns.*.*.distinct' => 'No se pueden repetir campos.',
        ]);
        $selectedSections = array_keys(array_intersect_key(
            self::PDF_SECTIONS,
            array_flip(array_intersect($allowedSections, $this->monitoreoPdfSections))
        ));
        $selectedColumns = [];
        foreach ($selectedSections as $section) {
            $selectedColumns[$section] = array_values(array_intersect(
                array_keys(self::PDF_COLUMNS[$section]),
                $this->monitoreoPdfColumns[$section] ?? []
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
                'filterSummary' => $this->monitoreoFilterSummary($section),
            ];
        }
        $title = count($reportSections) > 1
            ? 'Reporte integral de Monitoreo'
            : 'Reporte de Monitoreo: '.$reportSections[0]['label'];
        $reportSummary = collect($reportSections)
            ->map(fn ($section) => $section['label'].': '.count($section['rows']).' registro(s)')
            ->join(' · ');
        // Solo cerrar el modal de opciones la PRIMERA vez (no al regenerar desde preview).
        if ($this->exportStep !== 'preview') {
            $this->showMonitoreoPdfModal = false;
        }

        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.monitoreo', compact(
            'reportSections', 'fundo', 'generatedBy', 'generatedAt', 'administrators', 'reportSummary', 'title',
            'includeSignatures', 'scale'
        ))->setPaper('a4', 'landscape');

        return $this->setPdfPreview(
            $pdf,
            Str::slug('monitoreo_'.now()->format('Ymd_His'), '_').'.pdf',
            $title,
            collect($reportSections)->sum(fn ($s) => count($s['rows']))
        );
    }

    private function queryPdfData(int $fundoId, string $section): array
    {
        switch ($section) {
            case 'sanidad':
                $query = SanidadRegistro::where('fundo_id', $fundoId)
                    ->with(['animal', 'medicamento', 'fotos', 'dosisPlan']);
                $this->applySanidadFilters($query);
                $results = $query->orderByDesc('fecha_evento')->orderByDesc('id')->limit(1000)->get();

                return $results->map(fn ($s) => [
                    'fecha' => $s->fecha_evento?->format('d/m/Y') ?? '-',
                    'animal' => $s->animal?->arete ?? 'Archivado',
                    'categoria' => $s->categoria_salud_label,
                    'subtipo' => ucfirst(str_replace('_', ' ', $s->subtipo ?? 'otro')),
                    'hallazgo' => $s->sintomas_diagnostico,
                    'atencion' => $s->tratamiento ?: $s->producto_marca,
                    'dosis' => $s->dosisPlan->map(fn ($d) => 'D'.$d->numero.' '.($d->aplicada ? 'aplicada' : $d->fecha_programada?->format('d/m/Y')))->join(' · '),
                    'estado' => $s->estado_seguimiento_label,
                    'evidencia' => $s->fotos->isNotEmpty()
                        ? $s->fotos->count().' foto(s)'
                        : ($s->evidencia_ruta ? 'Adjunto anterior' : 'No'),
                ])->all();

            case 'partos':
                $query = Parto::where('fundo_id', $fundoId)
                    ->with(['madre', 'cria']);
                $this->applyPartoFilters($query);
                $results = $query->orderByDesc('fecha_parto')->orderByDesc('id')->limit(1000)->get();

                return $results->map(fn ($p) => [
                    'fecha' => $p->fecha_parto?->format('d/m/Y') ?? '-',
                    'madre' => $p->madre?->arete ?? 'Archivada',
                    'condicion' => $p->condicion_madre,
                    'tipo' => $p->tipo_parto,
                    'cria' => $p->cria
                        ? ($p->cria->nombre ? $p->cria->nombre.' · ' : '').$p->cria->arete
                        : 'Aborto / Muerto',
                    'sexo' => $p->cria?->genero ?? $p->cria_sexo,
                    'estado_cria' => $p->cria_estado,
                    'peso' => $p->cria_peso_nacer ? number_format($p->cria_peso_nacer, 2).' Kg' : '-',
                    'observaciones' => $p->observaciones,
                ])->all();

            case 'alertas':
                $query = AlertaProgramada::where('fundo_id', $fundoId)
                    ->with('animal');
                $this->applyAlertaFilters($query);
                $results = $query->orderBy('fecha_alerta', 'asc')->limit(1000)->get();

                return $results->map(fn ($a) => [
                    'fecha' => $a->fecha_alerta?->format('d/m/Y') ?? '-',
                    'animal' => $a->animal?->arete ?? 'Sin animal',
                    'tipo' => $a->tipo,
                    'mensaje' => $a->mensaje,
                    'estado' => $a->leida ? 'Leída' : 'Pendiente',
                ])->all();

            default:
                return [];
        }
    }

    private function monitoreoFilterSummary(string $section): string
    {
        switch ($section) {
            case 'sanidad':
                $parts = [];
                if ($this->searchSanidad) {
                    $parts[] = 'Búsqueda: '.$this->searchSanidad;
                }
                if ($this->sanidadCategoria) {
                    $parts[] = 'Evento: '.(SanidadRegistro::CATEGORIAS[$this->sanidadCategoria] ?? $this->sanidadCategoria);
                }
                if ($this->sanidadEstado) {
                    $parts[] = 'Estado: '.(SanidadRegistro::ESTADOS_SEGUIMIENTO[$this->sanidadEstado] ?? $this->sanidadEstado);
                }
                if ($this->sanidadFechaDesde) {
                    $parts[] = 'Desde: '.$this->sanidadFechaDesde;
                }
                if ($this->sanidadFechaHasta) {
                    $parts[] = 'Hasta: '.$this->sanidadFechaHasta;
                }

                return implode(' | ', $parts) ?: 'Sin filtros activos';

            case 'partos':
                $parts = [];
                if ($this->searchParto) {
                    $parts[] = 'Búsqueda: '.$this->searchParto;
                }
                if ($this->partoTipo) {
                    $parts[] = 'Tipo: '.$this->partoTipo;
                }
                if ($this->partoCondicionMadre) {
                    $parts[] = 'Condición madre: '.$this->partoCondicionMadre;
                }
                if ($this->partoCriaEstado) {
                    $parts[] = 'Estado cría: '.$this->partoCriaEstado;
                }
                if ($this->partoCriaSexo) {
                    $parts[] = 'Sexo: '.$this->partoCriaSexo;
                }
                if ($this->partoFechaDesde) {
                    $parts[] = 'Desde: '.$this->partoFechaDesde;
                }
                if ($this->partoFechaHasta) {
                    $parts[] = 'Hasta: '.$this->partoFechaHasta;
                }

                return implode(' | ', $parts) ?: 'Sin filtros activos';

            case 'alertas':
                $parts = [];
                if ($this->searchAlerta) {
                    $parts[] = 'Búsqueda: '.$this->searchAlerta;
                }
                if ($this->alertaTipo) {
                    $parts[] = 'Tipo: '.$this->alertaTipo;
                }
                if ($this->alertaFiltroLeida !== '') {
                    $parts[] = 'Estado: '.($this->alertaFiltroLeida === '1' ? 'Leídas' : 'Pendientes');
                }
                if ($this->alertaFechaDesde) {
                    $parts[] = 'Desde: '.$this->alertaFechaDesde;
                }
                if ($this->alertaFechaHasta) {
                    $parts[] = 'Hasta: '.$this->alertaFechaHasta;
                }

                return implode(' | ', $parts) ?: 'Sin filtros activos';

            default:
                return 'Sin filtros activos';
        }
    }

    public function render()
    {
        $fundoId = session('fundo_id');

        $alertasActivasCount = AlertaProgramada::where('fundo_id', $fundoId)->where('leida', false)->count();
        $animalesEnfermosCount = SanidadRegistro::where('fundo_id', $fundoId)
            ->whereIn('estado_seguimiento', ['en_seguimiento', 'cuarentena', 'critico'])
            ->distinct('animal_id')
            ->count('animal_id');
        $partosMesCount = Parto::where('fundo_id', $fundoId)
            ->where('fecha_parto', '>=', now()->startOfMonth())
            ->count();

        $perPage = $this->validatePerPage();

        $emptyPaginator = new LengthAwarePaginator([], 0, $perPage);

        $sanidades = $emptyPaginator;
        $partos = $emptyPaginator;
        $alertas = $emptyPaginator;

        if ($this->tab === 'sanidad') {
            $querySanidad = SanidadRegistro::where('fundo_id', $fundoId)->with(['animal', 'medicamento', 'dosisPlan.medicamento']);
            $this->applySanidadFilters($querySanidad);
            $sanidades = $this->pinRecent($querySanidad, 'monitoreo.sanidad')
                ->orderByDesc('fecha_evento')
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'sanPage');
        } elseif ($this->tab === 'partos') {
            $queryParto = Parto::where('fundo_id', $fundoId)->with(['madre', 'cria']);
            $this->applyPartoFilters($queryParto);
            $partos = $this->pinRecent($queryParto, 'monitoreo.partos')
                ->orderByDesc('fecha_parto')
                ->orderByDesc('id')
                ->paginate($perPage, ['*'], 'partoPage');
        } elseif ($this->tab === 'alertas') {
            $queryAlerta = AlertaProgramada::where('fundo_id', $fundoId)->with('animal');
            $this->applyAlertaFilters($queryAlerta);
            $alertas = $queryAlerta->orderBy('fecha_alerta', 'asc')->paginate($perPage, ['*'], 'alertaPage');
        }

        // OPTIMIZED DASHBOARD QUERIES WITH CACHING (2 min TTL)
        $dashboardStats = Cache::remember("monitoreo.dashboard.{$fundoId}", now()->addMinutes(2), function () use ($fundoId) {
            $totalSanidadHistorico = DB::table('sanidad_registros')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->count();

            // 12 months trend (using DB groupBy) for Sanidad
            $monthsList = collect(range(11, 0))->map(function ($i) {
                return now()->subMonths($i)->format('Y-m');
            });
            $minMonthStr = now()->subMonths(12)->startOfMonth()->toDateString();

            $monthlyRaw = DB::table('sanidad_registros')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->where('fecha_evento', '>=', $minMonthStr)
                ->selectRaw('substr(fecha_evento, 1, 7) as month_period, COUNT(*) as count')
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
                    'count' => $data ? (float) $data->count : 0,
                ];
            })->values()->all();

            // Categorias de salud
            $clasificacionData = DB::table('sanidad_registros')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->selectRaw('categoria_salud, COUNT(*) as count')
                ->groupBy('categoria_salud')
                ->get()
                ->map(function ($item) use ($totalSanidadHistorico) {
                    return [
                        'label' => SanidadRegistro::CATEGORIAS[$item->categoria_salud] ?? 'Otro evento',
                        'count' => (int) $item->count,
                        'percentage' => $totalSanidadHistorico > 0 ? round(($item->count / $totalSanidadHistorico) * 100, 1) : 0,
                    ];
                })->all();

            // Tipos de parto breakdowns
            $totalPartos = DB::table('partos')->where('fundo_id', $fundoId)->whereNull('deleted_at')->count();
            $partosData = DB::table('partos')
                ->where('fundo_id', $fundoId)
                ->whereNull('deleted_at')
                ->selectRaw('tipo_parto, COUNT(*) as count')
                ->groupBy('tipo_parto')
                ->get()
                ->map(function ($item) use ($totalPartos) {
                    return [
                        'label' => ucfirst(str_replace('_', ' ', $item->tipo_parto)),
                        'count' => (int) $item->count,
                        'percentage' => $totalPartos > 0 ? round(($item->count / $totalPartos) * 100, 1) : 0,
                    ];
                })->all();

            return [
                'totalSanidad' => $totalSanidadHistorico,
                'monthly' => $monthlyData,
                'clasificaciones' => $clasificacionData,
                'partos' => $partosData,
            ];
        });

        $dashboardData = [
            'generatedAt' => now()->format('H:i'),
            'alertasActivas' => $alertasActivasCount,
            'animalesEnfermos' => $animalesEnfermosCount,
            'partosMes' => $partosMesCount,
            'totalSanidad' => $dashboardStats['totalSanidad'],
            'monthly' => $dashboardStats['monthly'],
            'clasificaciones' => $dashboardStats['clasificaciones'],
            'partos' => $dashboardStats['partos'],
        ];

        return view('livewire.monitoreo.index', compact(
            'alertasActivasCount',
            'animalesEnfermosCount',
            'partosMesCount',
            'sanidades',
            'partos',
            'alertas',
            'dashboardData'
        ) + ['puedeBorrarAlertas' => $this->currentUserIsFundoAdmin()])->layout('layouts.app');
    }

    private function validatePerPage(): int
    {
        $value = (int) $this->perPage;
        if (! in_array($value, [10, 25, 50, 100], true)) {
            $this->perPage = 10;
            $value = 10;
        }

        return $value;
    }

    private function applySanidadFilters($query): void
    {
        if ($this->searchSanidad) {
            $query->where(function ($q) {
                $q->whereHas('animal', function ($animalQ) {
                    $animalQ->where('arete', 'like', '%'.$this->searchSanidad.'%')
                        ->orWhere('nombre', 'like', '%'.$this->searchSanidad.'%');
                })->orWhere('sintomas_diagnostico', 'like', '%'.$this->searchSanidad.'%')
                    ->orWhere('tratamiento', 'like', '%'.$this->searchSanidad.'%')
                    ->orWhere('subtipo', 'like', '%'.$this->searchSanidad.'%')
                    ->orWhere('ubicacion_corporal', 'like', '%'.$this->searchSanidad.'%')
                    ->orWhere('producto_marca', 'like', '%'.$this->searchSanidad.'%')
                    ->orWhere('proposito', 'like', '%'.$this->searchSanidad.'%');
            });
        }
        if ($this->sanidadCategoria) {
            $query->where('categoria_salud', $this->sanidadCategoria);
        }
        if ($this->sanidadEstado) {
            $query->where('estado_seguimiento', $this->sanidadEstado);
        }
        if ($this->sanidadFechaDesde) {
            $query->where('fecha_evento', '>=', $this->sanidadFechaDesde);
        }
        if ($this->sanidadFechaHasta) {
            $query->where('fecha_evento', '<', date('Y-m-d', strtotime($this->sanidadFechaHasta.' +1 day')));
        }
    }

    private function applyPartoFilters($query): void
    {
        if ($this->searchParto) {
            $query->where(function ($q) {
                $q->whereHas('madre', function ($madreQ) {
                    $madreQ->where('arete', 'like', '%'.$this->searchParto.'%')
                        ->orWhere('nombre', 'like', '%'.$this->searchParto.'%');
                })->orWhereHas('cria', function ($criaQ) {
                    $criaQ->where('arete', 'like', '%'.$this->searchParto.'%')
                        ->orWhere('nombre', 'like', '%'.$this->searchParto.'%');
                });
            });
        }
        if ($this->partoTipo) {
            $query->where('tipo_parto', $this->partoTipo);
        }
        if ($this->partoCondicionMadre) {
            $query->where('condicion_madre', $this->partoCondicionMadre);
        }
        if ($this->partoCriaEstado) {
            $query->where('cria_estado', $this->partoCriaEstado);
        }
        if ($this->partoCriaSexo) {
            $query->where('cria_sexo', $this->partoCriaSexo);
        }
        if ($this->partoFechaDesde) {
            $query->where('fecha_parto', '>=', $this->partoFechaDesde);
        }
        if ($this->partoFechaHasta) {
            $query->where('fecha_parto', '<', date('Y-m-d', strtotime($this->partoFechaHasta.' +1 day')));
        }
    }

    private function applyAlertaFilters($query): void
    {
        if ($this->searchAlerta) {
            $query->where(function ($q) {
                $q->where('mensaje', 'like', '%'.$this->searchAlerta.'%')
                    ->orWhereHas('animal', function ($animalQ) {
                        $animalQ->where('arete', 'like', '%'.$this->searchAlerta.'%')
                            ->orWhere('nombre', 'like', '%'.$this->searchAlerta.'%');
                    });
            });
        }
        if ($this->alertaTipo) {
            $query->where('tipo', $this->alertaTipo);
        }
        if ($this->alertaFiltroLeida !== '') {
            $query->where('leida', $this->alertaFiltroLeida === '1');
        }
        if ($this->alertaFechaDesde) {
            $query->where('fecha_alerta', '>=', $this->alertaFechaDesde);
        }
        if ($this->alertaFechaHasta) {
            $query->where('fecha_alerta', '<', date('Y-m-d', strtotime($this->alertaFechaHasta.' +1 day')));
        }
    }

    protected function recentRecordScopes(): array
    {
        return [
            'monitoreo.sanidad' => ['model' => SanidadRegistro::class, 'tab' => 'sanidad'],
            'monitoreo.partos' => ['model' => Parto::class, 'tab' => 'partos'],
        ];
    }
}
