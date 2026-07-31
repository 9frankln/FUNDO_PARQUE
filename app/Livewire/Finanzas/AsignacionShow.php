<?php

namespace App\Livewire\Finanzas;

use App\Models\AsignacionFamiliar;
use App\Models\Fundo;
use App\Traits\AuthorizesPermissions;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AsignacionShow extends Component
{
    use AuthorizesPermissions;

    private const REPORT_SECTIONS = [
        'summary' => ['label' => 'Resumen de la entrega', 'description' => 'Beneficiario, propósito, monto y fecha'],
        'detail' => ['label' => 'Detalle y trazabilidad', 'description' => 'Descripción y fechas de registro'],
        'photo' => ['label' => 'Evidencia fotográfica', 'description' => 'Fotografía y datos del archivo'],
    ];

    private const REPORT_COLUMNS = [
        'summary' => [
            'reference' => 'Código de referencia',
            'beneficiary' => 'Beneficiario',
            'purpose' => 'Propósito',
            'amount' => 'Monto',
            'date' => 'Fecha de entrega',
            'currency' => 'Moneda',
        ],
        'detail' => [
            'description' => 'Descripción',
            'registered_at' => 'Fecha de registro',
            'updated_at' => 'Última actualización',
        ],
        'photo' => [
            'preview' => 'Fotografía',
            'file_name' => 'Nombre del archivo',
            'file_type' => 'Tipo de archivo',
            'file_size' => 'Tamaño optimizado',
        ],
    ];

    #[Locked]
    public int $asigId;

    public bool $showReportModal = false;

    public array $selectedReportSections = ['summary'];

    public array $reportColumns = [];

    public function mount($id): void
    {
        $this->asigId = (int) $id;
        $this->assignment();
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

    public function openReportModal(): void
    {
        $this->selectedReportSections = ['summary'];
        $this->reportColumns = $this->defaultReportColumns();
        $this->resetValidation();
        $this->showReportModal = true;
    }

    public function downloadReport(?array $selectedSections = null, ?array $columns = null)
    {
        if ($selectedSections !== null) {
            $this->selectedReportSections = $selectedSections;
        }
        if ($columns !== null) {
            $this->reportColumns = $columns;
        }

        $this->authorizePermission('finanzas', 'exportar');
        [$selectedSections, $selectedColumns] = $this->validatedSelection();
        $asignacion = $this->assignment();
        $fundo = Fundo::findOrFail((int) session('fundo_id'));
        $fileMeta = $this->fileMeta($asignacion->foto_ruta);
        $reportSections = $this->buildReportSections($asignacion, $selectedSections, $selectedColumns, $fileMeta);
        $generatedAt = now();
        $generatedBy = auth()->user()->name;
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $reportTitle = 'Ficha de asignación familiar';
        $reportSubtitle = $asignacion->beneficiario.' · '.$asignacion->fecha->format('d/m/Y');
        $accent = '#6d28d9';
        $accentSoft = '#f5f3ff';
        $this->showReportModal = false;

        $pdf = Pdf::loadView('pdf.finance-record', compact(
            'reportTitle', 'reportSubtitle', 'reportSections', 'fundo', 'generatedAt',
            'generatedBy', 'administrators', 'accent', 'accentSoft'
        ))->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'asignacion_'.Str::slug($asignacion->beneficiario, '_').'_'.now()->format('Ymd_His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        $asignacion = $this->assignment();

        return view('livewire.finanzas.asignacion-show', compact('asignacion'))
            ->layout('layouts.app');
    }

    private function assignment(): AsignacionFamiliar
    {
        return AsignacionFamiliar::query()
            ->where('fundo_id', session('fundo_id'))
            ->findOrFail($this->asigId);
    }

    private function validatedSelection(): array
    {
        $rules = [
            'selectedReportSections' => ['required', 'array', 'min:1'],
            'selectedReportSections.*' => ['required', 'string', 'distinct', Rule::in(array_keys(self::REPORT_SECTIONS))],
        ];
        foreach ($this->selectedReportSections as $section) {
            if (! isset(self::REPORT_COLUMNS[$section])) {
                continue;
            }
            $rules['reportColumns.'.$section] = ['required', 'array', 'min:1'];
            $rules['reportColumns.'.$section.'.*'] = [
                'required', 'string', 'distinct', Rule::in(array_keys(self::REPORT_COLUMNS[$section])),
            ];
        }
        $this->validate($rules, [
            'selectedReportSections.required' => 'Selecciona al menos una sección.',
            'selectedReportSections.min' => 'Selecciona al menos una sección.',
            'selectedReportSections.*.in' => 'La selección contiene una sección no válida.',
            'reportColumns.*.required' => 'Selecciona al menos un campo para esta sección.',
            'reportColumns.*.min' => 'Selecciona al menos un campo para esta sección.',
            'reportColumns.*.*.in' => 'La selección contiene un campo no válido.',
        ]);

        $sections = array_values(array_intersect(array_keys(self::REPORT_SECTIONS), $this->selectedReportSections));
        $columns = [];
        foreach ($sections as $section) {
            $columns[$section] = array_values(array_intersect(
                array_keys(self::REPORT_COLUMNS[$section]),
                $this->reportColumns[$section] ?? []
            ));
        }

        return [$sections, $columns];
    }

    private function buildReportSections(AsignacionFamiliar $assignment, array $sections, array $columns, array $fileMeta): array
    {
        $values = [
            'summary' => [
                'reference' => 'ASF-'.str_pad((string) $assignment->id, 6, '0', STR_PAD_LEFT),
                'beneficiary' => $assignment->beneficiario,
                'purpose' => ucfirst(str_replace('_', ' ', $assignment->proposito)),
                'amount' => 'S/. '.number_format((float) $assignment->monto, 2),
                'date' => $assignment->fecha->format('d/m/Y'),
                'currency' => $assignment->moneda,
            ],
            'detail' => [
                'description' => $assignment->descripcion ?: 'Sin descripción adicional.',
                'registered_at' => $assignment->created_at?->timezone('America/Lima')->format('d/m/Y H:i') ?? '-',
                'updated_at' => $assignment->updated_at?->timezone('America/Lima')->format('d/m/Y H:i') ?? '-',
            ],
            'photo' => [
                'preview' => $fileMeta['image'],
                'file_name' => $fileMeta['name'],
                'file_type' => $fileMeta['type'],
                'file_size' => $fileMeta['size'],
            ],
        ];

        return collect($sections)->map(function ($section) use ($columns, $values) {
            $fields = collect(self::REPORT_COLUMNS[$section])
                ->only($columns[$section] ?? [])
                ->map(fn ($label, $key) => [
                    'key' => $key,
                    'label' => $label,
                    'value' => $values[$section][$key] ?? '-',
                    'kind' => $key === 'preview' ? 'image' : 'text',
                ])->values()->all();

            return [
                'label' => self::REPORT_SECTIONS[$section]['label'],
                'description' => self::REPORT_SECTIONS[$section]['description'],
                'fields' => $fields,
            ];
        })->all();
    }

    private function fileMeta(?string $path): array
    {
        $path = ltrim(str_replace('\\', '/', (string) $path), '/');
        $empty = ['name' => 'Sin fotografía', 'type' => 'No disponible', 'size' => '-', 'image' => null];
        if ($path === '' || str_contains($path, '../') || ! Storage::disk('local')->exists($path)) {
            return $empty;
        }

        $contents = Storage::disk('local')->get($path);
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($contents) ?: 'application/octet-stream';
        $image = in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)
            ? 'data:'.$mime.';base64,'.base64_encode($contents)
            : null;

        return [
            'name' => basename($path),
            'type' => match ($mime) {
                'image/jpeg' => 'Imagen JPEG',
                'image/png' => 'Imagen PNG',
                'image/webp' => 'Imagen WebP optimizada',
                default => $mime,
            },
            'size' => $this->formatBytes(strlen($contents)),
            'image' => $image,
        ];
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1024 * 1024
            ? number_format($bytes / (1024 * 1024), 2).' MB'
            : number_format(max(1, $bytes / 1024), 0).' KB';
    }

    private function defaultReportColumns(): array
    {
        return collect(self::REPORT_COLUMNS)->map(fn ($columns) => array_keys($columns))->all();
    }
}
