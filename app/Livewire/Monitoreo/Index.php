<?php

namespace App\Livewire\Monitoreo;

use App\Models\AlertaProgramada;
use App\Models\Fundo;
use App\Models\Parto;
use App\Models\ProfilaxisRegistro;
use App\Models\SanidadRegistro;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasRecentRecord;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesPermissions, HasRecentRecord, WithPagination;

    public $tab = 'sanidad';

    public $perPage = 10;

    // Sanidad filters
    public $searchSanidad = '';

    public $sanidadClasificacion = '';

    public $sanidadEstadoClinico = '';

    public $sanidadFechaDesde = '';

    public $sanidadFechaHasta = '';

    // Profilaxis filters
    public $searchProfilaxis = '';

    public $profilaxisTipo = '';

    public $profilaxisAlcance = '';

    public $profilaxisFechaDesde = '';

    public $profilaxisFechaHasta = '';

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

    protected $queryString = ['tab', 'perPage'];

    protected $listeners = [
        'confirmarEliminacionSanidad' => 'deleteSanidad',
        'confirmarEliminacionProfilaxis' => 'deleteProfilaxis',
        'confirmarEliminacionParto' => 'deleteParto',
    ];

    public function updatedSearchSanidad(): void
    {
        $this->resetPage('sanPage');
    }

    public function updatedSanidadClasificacion(): void
    {
        $this->resetPage('sanPage');
    }

    public function updatedSanidadEstadoClinico(): void
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

    public function updatedSearchProfilaxis(): void
    {
        $this->resetPage('profPage');
    }

    public function updatedProfilaxisTipo(): void
    {
        $this->resetPage('profPage');
    }

    public function updatedProfilaxisAlcance(): void
    {
        $this->resetPage('profPage');
    }

    public function updatedProfilaxisFechaDesde(): void
    {
        $this->resetPage('profPage');
    }

    public function updatedProfilaxisFechaHasta(): void
    {
        $this->resetPage('profPage');
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
        $this->reset(['searchSanidad', 'sanidadClasificacion', 'sanidadEstadoClinico', 'sanidadFechaDesde', 'sanidadFechaHasta']);
        $this->resetPage('sanPage');
    }

    public function resetProfilaxisFilters(): void
    {
        $this->reset(['searchProfilaxis', 'profilaxisTipo', 'profilaxisAlcance', 'profilaxisFechaDesde', 'profilaxisFechaHasta']);
        $this->resetPage('profPage');
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
            $this->dispatch('swal:modal', [
                'title' => 'Alerta Leída',
                'text' => 'La notificación ha sido marcada como leída.',
                'icon' => 'success',
            ]);
        }
    }

    public function solicitarEliminacionSanidad($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar registro clínico?',
            'text' => 'Se borrará la ficha médica del animal.',
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

        $san = SanidadRegistro::find($id);
        if ($san) {
            $san->delete();
            $this->dispatch('swal:modal', [
                'title' => 'Eliminado',
                'text' => 'Registro clínico eliminado correctamente.',
                'icon' => 'success',
            ]);
        }
    }

    public function solicitarEliminacionProfilaxis($id)
    {
        $this->dispatch('swal:confirm', [
            'title' => '¿Eliminar profilaxis?',
            'text' => 'Se borrará el registro preventivo.',
            'icon' => 'warning',
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'event' => 'confirmarEliminacionProfilaxis',
            'id' => $id,
        ]);
    }

    public function deleteProfilaxis($id)
    {
        $this->authorizePermission('monitoreo', 'eliminar');

        $prof = ProfilaxisRegistro::where('fundo_id', session('fundo_id'))->find($id);
        if ($prof) {
            DB::transaction(function () use ($prof): void {
                $doseIds = $prof->dosisProgramadas()->pluck('id');
                if ($doseIds->isNotEmpty()) {
                    AlertaProgramada::withoutGlobalScopes()->whereIn('profilaxis_dosis_id', $doseIds)->delete();
                }
                $prof->delete();
            });
            $this->dispatch('swal:modal', [
                'title' => 'Eliminado',
                'text' => 'Registro profiláctico eliminado correctamente.',
                'icon' => 'success',
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
            $this->dispatch('swal:modal', [
                'title' => 'Eliminado',
                'text' => 'Registro de parto eliminado.',
                'icon' => 'success',
            ]);
        }
    }

    private const PDF_SECTIONS = [
        'sanidad' => ['label' => 'Historial clínico', 'description' => 'Diagnósticos y tratamientos'],
        'profilaxis' => ['label' => 'Profilaxis y vacunas', 'description' => 'Vacunas e intervenciones'],
        'partos' => ['label' => 'Partos', 'description' => 'Madres, crías y resultados'],
        'alertas' => ['label' => 'Alertas programadas', 'description' => 'Pendientes y archivadas'],
    ];

    private const PDF_COLUMNS = [
        'sanidad' => [
            'fecha' => 'Fecha',
            'animal' => 'Animal',
            'clasificacion' => 'Clasificación',
            'sintomas' => 'Síntomas / Diagnóstico',
            'tratamiento' => 'Tratamiento',
            'medicamento' => 'Medicamento',
            'dosis' => 'Dosis / Vía',
            'estado' => 'Estado clínico',
            'evidencia' => 'Evidencia adjunta',
        ],
        'profilaxis' => [
            'fecha' => 'Fecha aplicación',
            'tipo' => 'Tipo intervención',
            'producto' => 'Producto / Marca',
            'animales' => 'Animales',
            'dosis' => 'Dosis',
            'proxima' => 'Calendario de dosis',
            'responsable' => 'Responsable',
            'observaciones' => 'Observaciones',
            'evidencia' => 'Evidencia',
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
        'sanidad' => ['fecha', 'animal', 'sintomas', 'tratamiento', 'medicamento', 'estado'],
        'profilaxis' => ['fecha', 'tipo', 'producto', 'animales', 'dosis', 'proxima', 'evidencia'],
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
        $this->showMonitoreoPdfModal = false;

        $pdf = Pdf::loadView('pdf.monitoreo', compact(
            'reportSections', 'fundo', 'generatedBy', 'generatedAt', 'administrators', 'reportSummary', 'title'
        ))->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            Str::slug('monitoreo_'.now()->format('Ymd_His'), '_').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function queryPdfData(int $fundoId, string $section): array
    {
        switch ($section) {
            case 'sanidad':
                $query = SanidadRegistro::where('fundo_id', $fundoId)
                    ->with(['animal', 'medicamento', 'fotos']);
                $this->applySanidadFilters($query);
                $results = $query->orderByDesc('fecha_evento')->orderByDesc('id')->limit(1000)->get();

                return $results->map(fn ($s) => [
                    'fecha' => $s->fecha_evento?->format('d/m/Y') ?? '-',
                    'animal' => $s->animal?->arete ?? 'Archivado',
                    'clasificacion' => $s->clasificacion,
                    'sintomas' => $s->sintomas_diagnostico,
                    'tratamiento' => $s->tratamiento,
                    'medicamento' => $s->medicamento?->nombre.' '.($s->dosis_via ?? ''),
                    'dosis' => $s->dosis_via,
                    'estado' => $s->estado_clinico,
                    'evidencia' => $s->fotos->isNotEmpty()
                        ? $s->fotos->count().' foto(s)'
                        : ($s->evidencia_ruta ? 'Adjunto anterior' : 'No'),
                ])->all();

            case 'profilaxis':
                $query = ProfilaxisRegistro::where('fundo_id', $fundoId)
                    ->with(['animales', 'dosisProgramadas', 'fotos']);
                $this->applyProfilaxisFilters($query);
                $results = $query->orderByDesc('fecha_aplicacion')->orderByDesc('id')->limit(1000)->get();

                return $results->map(fn ($p) => [
                    'fecha' => $p->fecha_aplicacion?->format('d/m/Y') ?? '-',
                    'tipo' => $p->tipo_intervencion,
                    'producto' => $p->producto_marca,
                    'animales' => $p->animales->pluck('arete')->join(', '),
                    'dosis' => $p->dosis,
                    'proxima' => $p->fechasDosisProgramadas()->isEmpty()
                        ? 'Única dosis'
                        : $p->fechasDosisProgramadas()->map(fn ($date, $index) => 'Dosis '.($index + 2).': '.$date->format('d/m/Y'))->join(' | '),
                    'responsable' => $p->responsable,
                    'observaciones' => $p->observaciones,
                    'evidencia' => $p->fotos->isNotEmpty() ? $p->fotos->count().' foto(s)' : 'No',
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
                if ($this->sanidadClasificacion) {
                    $parts[] = 'Clasificación: '.$this->sanidadClasificacion;
                }
                if ($this->sanidadEstadoClinico) {
                    $parts[] = 'Estado: '.$this->sanidadEstadoClinico;
                }
                if ($this->sanidadFechaDesde) {
                    $parts[] = 'Desde: '.$this->sanidadFechaDesde;
                }
                if ($this->sanidadFechaHasta) {
                    $parts[] = 'Hasta: '.$this->sanidadFechaHasta;
                }

                return implode(' | ', $parts) ?: 'Sin filtros activos';

            case 'profilaxis':
                $parts = [];
                if ($this->searchProfilaxis) {
                    $parts[] = 'Búsqueda: '.$this->searchProfilaxis;
                }
                if ($this->profilaxisTipo) {
                    $parts[] = 'Tipo: '.$this->profilaxisTipo;
                }
                if ($this->profilaxisAlcance) {
                    $parts[] = 'Alcance: '.$this->profilaxisAlcance;
                }
                if ($this->profilaxisFechaDesde) {
                    $parts[] = 'Desde: '.$this->profilaxisFechaDesde;
                }
                if ($this->profilaxisFechaHasta) {
                    $parts[] = 'Hasta: '.$this->profilaxisFechaHasta;
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
            ->whereIn('estado_clinico', ['en_tratamiento', 'cuarentena', 'critico'])
            ->distinct('animal_id')
            ->count('animal_id');
        $partosMesCount = Parto::where('fundo_id', $fundoId)
            ->where('fecha_parto', '>=', now()->startOfMonth())
            ->count();

        $perPage = $this->validatePerPage();

        // 1. SANIDAD
        $querySanidad = SanidadRegistro::where('fundo_id', $fundoId)->with(['animal', 'medicamento']);
        $this->applySanidadFilters($querySanidad);
        $sanidades = $this->pinRecent($querySanidad, 'monitoreo.sanidad')
            ->orderByDesc('fecha_evento')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'sanPage');

        // 2. PROFILAXIS
        $queryProfilaxis = ProfilaxisRegistro::where('fundo_id', $fundoId)->with(['animales', 'dosisProgramadas', 'fotos']);
        $this->applyProfilaxisFilters($queryProfilaxis);
        $profilaxis = $this->pinRecent($queryProfilaxis, 'monitoreo.profilaxis')
            ->orderByDesc('fecha_aplicacion')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'profPage');

        // 3. PARTOS
        $queryParto = Parto::where('fundo_id', $fundoId)->with(['madre', 'cria']);
        $this->applyPartoFilters($queryParto);
        $partos = $this->pinRecent($queryParto, 'monitoreo.partos')
            ->orderByDesc('fecha_parto')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'partoPage');

        // 4. ALERTAS
        $queryAlerta = AlertaProgramada::where('fundo_id', $fundoId)->with('animal');
        $this->applyAlertaFilters($queryAlerta);
        $alertas = $queryAlerta->orderBy('fecha_alerta', 'asc')->paginate($perPage, ['*'], 'alertaPage');

        // OPTIMIZED DASHBOARD QUERIES
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
            ->selectRaw("substr(fecha_evento, 1, 7) as month_period, COUNT(*) as count")
            ->groupBy('month_period')
            ->get()
            ->keyBy('month_period');

        $monthlyData = $monthsList->map(function ($period) use ($monthlyRaw) {
            $dt = \Carbon\CarbonImmutable::createFromFormat('Y-m', $period);
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

        // Clasificacion de sanidad breakdowns
        $clasificacionData = DB::table('sanidad_registros')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->selectRaw('clasificacion, COUNT(*) as count')
            ->groupBy('clasificacion')
            ->get()
            ->map(function ($item) use ($totalSanidadHistorico) {
                return [
                    'label' => ucfirst(str_replace('_', ' ', $item->clasificacion)),
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

        $dashboardData = [
            'generatedAt' => now()->format('H:i'),
            'alertasActivas' => $alertasActivasCount,
            'animalesEnfermos' => $animalesEnfermosCount,
            'partosMes' => $partosMesCount,
            'totalSanidad' => $totalSanidadHistorico,
            'monthly' => $monthlyData,
            'clasificaciones' => $clasificacionData,
            'partos' => $partosData,
        ];

        return view('livewire.monitoreo.index', compact(
            'alertasActivasCount',
            'animalesEnfermosCount',
            'partosMesCount',
            'sanidades',
            'profilaxis',
            'partos',
            'alertas',
            'dashboardData'
        ))->layout('layouts.app');
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
                    ->orWhere('tratamiento', 'like', '%'.$this->searchSanidad.'%');
            });
        }
        if ($this->sanidadClasificacion) {
            $query->where('clasificacion', $this->sanidadClasificacion);
        }
        if ($this->sanidadEstadoClinico) {
            $query->where('estado_clinico', $this->sanidadEstadoClinico);
        }
        if ($this->sanidadFechaDesde) {
            $query->where('fecha_evento', '>=', $this->sanidadFechaDesde);
        }
        if ($this->sanidadFechaHasta) {
            $query->where('fecha_evento', '<', date('Y-m-d', strtotime($this->sanidadFechaHasta.' +1 day')));
        }
    }

    private function applyProfilaxisFilters($query): void
    {
        if ($this->searchProfilaxis) {
            $query->where(function ($q) {
                $q->where('producto_marca', 'like', '%'.$this->searchProfilaxis.'%')
                    ->orWhere('proposito', 'like', '%'.$this->searchProfilaxis.'%')
                    ->orWhere('responsable', 'like', '%'.$this->searchProfilaxis.'%');
            });
        }
        if ($this->profilaxisTipo) {
            $query->where('tipo_intervencion', $this->profilaxisTipo);
        }
        if ($this->profilaxisAlcance) {
            $query->where('alcance', $this->profilaxisAlcance);
        }
        if ($this->profilaxisFechaDesde) {
            $query->where('fecha_aplicacion', '>=', $this->profilaxisFechaDesde);
        }
        if ($this->profilaxisFechaHasta) {
            $query->where('fecha_aplicacion', '<', date('Y-m-d', strtotime($this->profilaxisFechaHasta.' +1 day')));
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
            'monitoreo.profilaxis' => ['model' => ProfilaxisRegistro::class, 'tab' => 'profilaxis'],
            'monitoreo.partos' => ['model' => Parto::class, 'tab' => 'partos'],
        ];
    }
}
