<?php

namespace App\Livewire\Animal;

use App\Models\Animal;
use App\Models\Fundo;
use App\Traits\AuthorizesPermissions;
use App\Traits\HasPdfPreviewModal;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesPermissions, HasPdfPreviewModal;

    private const REPORT_SECTIONS = [
        'identity' => ['label' => 'Identificación y fotografía', 'description' => 'Perfil, código, edad y peso'],
        'productive' => ['label' => 'Datos productivos y de alta', 'description' => 'Procedencia, estados y observaciones'],
        'clinical' => ['label' => 'Historial de salud', 'description' => 'Eventos, atenciones y planes de dosis'],
        'reproductive' => ['label' => 'Partos y crías', 'description' => 'Historial reproductivo'],
        'milk' => ['label' => 'Producción láctea', 'description' => 'Resumen y controles individuales'],
    ];

    private const REPORT_COLUMNS = [
        'identity' => [
            'photo' => 'Fotografía',
            'code' => 'Código',
            'name' => 'Nombre',
            'species' => 'Especie',
            'breed' => 'Raza',
            'sex' => 'Sexo',
            'status' => 'Estado',
            'birth_date' => 'Fecha de nacimiento',
            'classification' => 'Clasificación',
            'age' => 'Edad',
            'weight' => 'Peso registrado',
            'reproductive_status' => 'Estado reproductivo',
        ],
        'productive' => [
            'admission_type' => 'Procedencia',
            'admission_date' => 'Fecha de alta',
            'productive_status' => 'Estado productivo',
            'milking_eligible' => 'Apta para ordeño',
            'dentition' => 'Dentición estimada',
            'purchase_price' => 'Precio de compra',
            'observations' => 'Observaciones generales',
            'origin' => 'Registro de origen',
        ],
        'clinical' => [
            'date' => 'Fecha',
            'type' => 'Evento',
            'classification' => 'Tipo específico',
            'status' => 'Seguimiento',
            'diagnosis' => 'Hallazgo / motivo',
            'treatment' => 'Atención / indicaciones',
            'medication' => 'Plan de dosis',
            'dosage' => 'Severidad / zona',
            'evidence' => 'Evidencia adjunta',
        ],
        'reproductive' => [
            'date' => 'Fecha',
            'birth_type' => 'Tipo de parto',
            'maternal_condition' => 'Condición materna',
            'calf' => 'Cría',
            'calf_sex' => 'Sexo de cría',
            'calf_status' => 'Estado de cría',
            'birth_weight' => 'Peso al nacer',
            'observations' => 'Observaciones',
        ],
        'milk' => [
            'summary' => 'Resumen productivo',
            'date' => 'Fecha',
            'shift' => 'Turno',
            'liters' => 'Litros',
            'exception' => 'Causa de excepción',
            'justification' => 'Justificación',
        ],
    ];

    #[Locked]
    public $animalId;

    public $animal;

    public $timeline = [];

    public $estadoClinicoActual = 'sano';

    public $showReportModal = false;

    public $selectedReportSections = ['identity'];

    public $reportColumns = [];

    public function mount($id)
    {
        $this->animalId = $id;
        $this->animal = Animal::with([
            'especie',
            'raza',
            'movimientoVenta',
            'sanidadRegistros' => fn ($query) => $query->with(['medicamento', 'fotos', 'dosisPlan.medicamento'])->orderByDesc('fecha_evento')->orderByDesc('id'),
            'partosMadre' => fn ($query) => $query->with(['cria', 'fotos'])->orderByDesc('fecha_parto')->orderByDesc('id'),
            'ordenoDetalles.ordeno',
            'partosCria.madre',
        ])->where('fundo_id', session('fundo_id'))->findOrFail($id);
        $this->timeline = $this->buildTimeline();
        $this->estadoClinicoActual = $this->currentClinicalStatus();
        $this->selectedReportSections = ['identity'];
        $this->reportColumns = $this->defaultReportColumns();
    }

    private function buildTimeline(): array
    {
        $events = [];

        // Un evento de salud conserva su atencion y todas sus dosis.
        foreach ($this->animal->sanidadRegistros as $san) {
            $aplicadas = $san->dosisPlan->where('aplicada', true)->count();
            $pendientes = $san->dosisPlan->where('aplicada', false)->count();
            $events[] = [
                'id' => 'san-'.$san->id,
                'tipo' => 'salud',
                'categoria' => $san->categoria_salud,
                'fecha' => $san->fecha_evento,
                'titulo' => $san->categoria_salud_label,
                'subtitulo' => ucfirst(str_replace('_', ' ', $san->subtipo ?: 'otro')).($san->ubicacion_corporal ? ' · '.$san->ubicacion_corporal : ''),
                'detalle' => $san->sintomas_diagnostico ?: 'Sin detalle',
                'atencion' => $san->tratamiento ?: $san->producto_marca,
                'estado' => $san->estado_seguimiento,
                'estado_label' => $san->estado_seguimiento_label,
                'medicamento' => $san->dosisPlan->map(fn ($d) => 'D'.$d->numero.' '.($d->medicamento?->nombre ?? $d->medicamento_nombre ?? 'Producto').' · '.($d->aplicada ? 'Aplicada' : $d->fecha_programada->format('d/m/Y')))->join(' | '),
                'aplicadas' => $aplicadas,
                'pendientes' => $pendientes,
                'cierre' => $san->fecha_cierre,
                'fotos' => $san->fotos,
                'url' => route('monitoreo.sanidad.edit', $san->id),
            ];
        }

        usort($events, fn ($a, $b) => [$b['fecha']->format('Y-m-d'), $b['id']] <=> [$a['fecha']->format('Y-m-d'), $a['id']]);

        return $events;
    }

    private function currentClinicalStatus(): string
    {
        if (! $this->animal->activo) {
            return 'inactivo';
        }

        // El evento de salud mas reciente marca el estado actual.
        $ultimo = $this->animal->sanidadRegistros->first();
        if (! $ultimo) {
            return 'sano';
        }
        if ($ultimo->estado_seguimiento === 'completado') {
            return 'sano';
        }
        if (in_array($ultimo->estado_seguimiento, ['critico', 'cuarentena'], true)) {
            return 'alerta';
        }

        return 'tratamiento';
    }

    public static function reportSectionOptions(): array
    {
        return self::REPORT_SECTIONS;
    }

    public static function reportColumnOptions(): array
    {
        return self::REPORT_COLUMNS;
    }

    public function openAnimalReportModal(): void
    {
        $this->selectedReportSections = ['identity'];
        $this->reportColumns = $this->defaultReportColumns();
        $this->resetValidation();
        $this->showReportModal = true;
    }

    public function downloadAnimalReport()
    {
        $this->authorizePermission('animal', 'exportar');

        $availableSections = $this->availableReportSections();
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
        $fundo = Fundo::withoutGlobalScopes()->findOrFail($fundoId);

        $animal = Animal::with([
            'especie',
            'raza',
            'movimientoVenta',
            'sanidadRegistros' => fn ($q) => $q->orderByDesc('fecha_evento')->orderByDesc('id'),
            'sanidadRegistros.medicamento',
            'sanidadRegistros.fotos',
            'sanidadRegistros.dosisPlan.medicamento',
            'partosMadre' => fn ($q) => $q->orderByDesc('fecha_parto')->orderByDesc('id'),
            'partosMadre.cria',
            'partosMadre.fotos',
            'ordenoDetalles' => fn ($q) => $q->with('ordeno')->whereHas('ordeno', fn ($oq) => $oq->where('fundo_id', $fundoId)),
            'partosCria.madre',
        ])->where('fundo_id', $fundoId)->findOrFail($this->animalId);

        $generatedBy = auth()->user()->name;
        $generatedAt = now();
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $photoDataUri = $this->photoDataUri($animal->foto_ruta);
        $milkRecords = $animal->ordenoDetalles
            ->filter(fn ($detail) => $detail->ordeno !== null && $detail->ordeno->tipo_registro !== 'lote')
            ->sortByDesc(fn ($detail) => sprintf(
                '%s-%010d',
                $detail->ordeno->fecha?->format('Ymd') ?? '00000000',
                $detail->id
            ))
            ->values();
        $productiveMilkRecords = $milkRecords->filter(fn ($detail) => (float) $detail->litros > 0);
        $milkSummary = [
            'controls' => $milkRecords->count(),
            'productive' => $productiveMilkRecords->count(),
            'exceptions' => $milkRecords->count() - $productiveMilkRecords->count(),
            'liters' => (float) $productiveMilkRecords->sum('litros'),
            'average' => $productiveMilkRecords->isNotEmpty() ? (float) $productiveMilkRecords->avg('litros') : null,
            'last_date' => $milkRecords->first()?->ordeno?->fecha,
        ];
        $reportSummary = collect($selectedSections)
            ->map(fn ($section) => self::REPORT_SECTIONS[$section]['label'])
            ->join(', ');
        $reportSummary = 'Secciones incluidas: '.$reportSummary.'. Registros disponibles: '
            .$animal->sanidadRegistros->count().' eventos de salud, '
            .$animal->partosMadre->count().' partos y '
            .$milkRecords->count().' controles de ordeño.';
        // Solo cerrar el modal de opciones la PRIMERA vez (no al regenerar desde preview).
        if ($this->exportStep !== 'preview') {
            $this->showReportModal = false;
        }

        $includeSignatures = $this->pdfIncludeSignatures;
        $scale = $this->pdfScale;

        $pdf = Pdf::loadView('pdf.animal', compact(
            'animal', 'fundo', 'generatedBy', 'generatedAt', 'administrators',
            'photoDataUri', 'milkRecords', 'milkSummary', 'reportSummary', 'selectedSections', 'selectedColumns',
            'includeSignatures', 'scale'
        ))->setPaper('a4', 'landscape');

        return $this->setPdfPreview(
            $pdf,
            'ficha_animal_'.Str::slug($animal->arete, '_').'_'.now()->format('Ymd_His').'.pdf',
            'Ficha de '.($animal->nombre ?: $animal->arete),
            1
        );
    }

    public function render()
    {
        if ($this->animal instanceof Animal) {
            $this->animal->load([
                'especie',
                'raza',
                'movimientoVenta',
                'sanidadRegistros' => fn ($query) => $query->with(['medicamento', 'fotos', 'dosisPlan.medicamento'])->orderByDesc('fecha_evento')->orderByDesc('id'),
                'partosMadre' => fn ($query) => $query->with(['cria', 'fotos'])->orderByDesc('fecha_parto')->orderByDesc('id'),
                'ordenoDetalles.ordeno',
                'partosCria.madre',
            ]);
        }

        return view('livewire.animal.show')
            ->layout('layouts.app');
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

    private function availableReportSections(): array
    {
        $sections = array_keys(self::REPORT_SECTIONS);

        if ($this->animal->genero !== 'hembra') {
            $sections = array_values(array_diff($sections, ['reproductive']));
        }
        if (! $this->animal->apta_ordeno && $this->animal->ordenoDetalles->isEmpty()) {
            $sections = array_values(array_diff($sections, ['milk']));
        }

        return $sections;
    }

    private function defaultReportColumns(): array
    {
        return collect(self::REPORT_COLUMNS)
            ->map(fn ($columns) => array_keys($columns))
            ->all();
    }
}
