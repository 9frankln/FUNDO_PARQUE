<?php

namespace App\Livewire\Animal;

use App\Models\Animal;
use App\Models\Fundo;
use App\Models\ProfilaxisRegistro;
use App\Traits\AuthorizesPermissions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesPermissions;

    private const REPORT_SECTIONS = [
        'identity' => ['label' => 'Identificación y fotografía', 'description' => 'Perfil, código, edad y peso'],
        'productive' => ['label' => 'Datos productivos y de alta', 'description' => 'Procedencia, estados y observaciones'],
        'clinical' => ['label' => 'Historial clínico', 'description' => 'Diagnósticos y tratamientos'],
        'preventive' => ['label' => 'Profilaxis y vacunas', 'description' => 'Intervenciones preventivas'],
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
            'classification' => 'Clasificación',
            'status' => 'Estado clínico',
            'diagnosis' => 'Síntomas / diagnóstico',
            'treatment' => 'Tratamiento',
            'medication' => 'Medicamento',
            'dosage' => 'Dosis / vía',
            'evidence' => 'Evidencia adjunta',
        ],
        'preventive' => [
            'date' => 'Fecha',
            'intervention' => 'Tipo de intervención',
            'product' => 'Producto / marca',
            'purpose' => 'Propósito',
            'dose' => 'Dosis',
            'next_dose' => 'Calendario de dosis',
            'responsible' => 'Responsable',
            'observations' => 'Observaciones',
            'evidence' => 'Evidencia',
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
            'sanidadRegistros' => fn ($query) => $query->with(['medicamento', 'fotos'])->orderByDesc('fecha_evento')->orderByDesc('id'),
            'partosMadre' => fn ($query) => $query->with(['cria', 'fotos'])->orderByDesc('fecha_parto')->orderByDesc('id'),
            'ordenoDetalles.ordeno',
            'partosCria.madre',
        ])->where('fundo_id', session('fundo_id'))->findOrFail($id);
        $this->selectedReportSections = ['identity'];
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
            'partosMadre' => fn ($q) => $q->orderByDesc('fecha_parto')->orderByDesc('id'),
            'partosMadre.cria',
            'partosMadre.fotos',
            'ordenoDetalles' => fn ($q) => $q->with('ordeno')->whereHas('ordeno', fn ($oq) => $oq->where('fundo_id', $fundoId)),
            'partosCria.madre',
        ])->where('fundo_id', $fundoId)->findOrFail($this->animalId);

        $profilaxis = ProfilaxisRegistro::where('fundo_id', $fundoId)
            ->whereHas('animales', fn ($q) => $q->where('animal_id', $animal->id))
            ->with(['animales', 'dosisProgramadas', 'fotos'])
            ->orderByDesc('fecha_aplicacion')
            ->orderByDesc('id')
            ->get();

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
            .$animal->sanidadRegistros->count().' clínicos, '
            .$profilaxis->count().' profilácticos, '
            .$animal->partosMadre->count().' partos y '
            .$milkRecords->count().' controles de ordeño.';
        $this->showReportModal = false;

        $pdf = Pdf::loadView('pdf.animal', compact(
            'animal', 'profilaxis', 'fundo', 'generatedBy', 'generatedAt', 'administrators',
            'photoDataUri', 'milkRecords', 'milkSummary', 'reportSummary', 'selectedSections', 'selectedColumns'
        ))->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'ficha_animal_'.Str::slug($animal->arete, '_').'_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
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
