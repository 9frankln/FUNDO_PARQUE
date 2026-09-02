<?php

namespace App\Exports;

use App\Models\Animal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Support\SystemBranding;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AnimalesExport implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    use Exportable;

    private const COLUMN_LABELS = [
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

    private array $columns = [];

    private array $headers = [];

    public function __construct(
        private readonly int $fundoId,
        array $selectedColumns,
        private readonly array $filters,
        private readonly string $generatedBy,
    ) {
        $this->columns = array_keys(array_intersect_key(self::COLUMN_LABELS, array_flip($selectedColumns)));
        $this->headers = array_values(array_intersect_key(self::COLUMN_LABELS, array_flip($this->columns)));
    }

    public function collection(): Collection
    {
        $fundo = Fundo::findOrFail($this->fundoId);
        $administrators = $fundo->usuarios()
            ->wherePivot('es_administrador', true)
            ->orderBy('users.name')
            ->pluck('users.name')
            ->filter()
            ->implode(', ') ?: 'No asignado';
        $animals = $this->query()->get();
        $summary = $animals->count().' registros. Columnas: '.collect($this->headers)->join(', ', ' y ').'.';
        $rows = collect([
            [mb_strtoupper(app(SystemBranding::class)->name()).' - REPORTE DE INVENTARIO ANIMAL'],
            ['Fundo:', $this->excelText($fundo->nombre), 'Administrador(es):', $this->excelText($administrators)],
            ['Generado por:', $this->excelText($this->generatedBy), 'Fecha (hora de Perú):', now('America/Lima')->format('d/m/Y H:i')],
            ['Resumen:', $summary],
            ['Filtros aplicados:', $this->filterSummary()],
            [],
            $this->headers,
        ]);

        return $rows->merge($animals->map(fn (Animal $animal) => $this->map($animal)));
    }

    public function title(): string
    {
        return 'Inventario animal';
    }

    public function columnWidths(): array
    {
        $widthByColumn = [
            'arete' => 17,
            'nombre' => 23,
            'especie' => 16,
            'raza' => 22,
            'genero' => 12,
            'edad' => 22,
            'peso' => 19,
            'estado_reproductivo' => 22,
            'tipo_alta' => 20,
            'precio_compra' => 20,
            'activo' => 12,
            'fecha_alta' => 15,
        ];
        $widths = ['A' => 19, 'B' => 32, 'C' => 22, 'D' => 30];

        foreach ($this->columns as $index => $column) {
            $widths[Coordinate::stringFromColumnIndex($index + 1)] = $widthByColumn[$column];
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $tableLastColumn = Coordinate::stringFromColumnIndex(max(count($this->headers), 1));
                $metaLastColumn = Coordinate::stringFromColumnIndex(max(count($this->headers), 4));
                $lastRow = max($sheet->getHighestRow(), 7);

                $sheet->mergeCells("A1:{$metaLastColumn}1");
                $sheet->mergeCells("B4:{$metaLastColumn}4");
                $sheet->mergeCells("B5:{$metaLastColumn}5");
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getStyle("A1:{$metaLastColumn}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle("A2:{$metaLastColumn}5")->applyFromArray([
                    'font' => ['color' => ['rgb' => '315744']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ECFDF5']],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A7D7B5']],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getStyle('A2:A5')->getFont()->setBold(true)->getColor()->setRGB('047857');
                $sheet->getStyle('C2:C3')->getFont()->setBold(true)->getColor()->setRGB('047857');
                $sheet->getStyle("A7:{$tableLastColumn}7")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '047857']],
                    ],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(28);

                if ($lastRow > 7) {
                    $sheet->getStyle("A8:{$tableLastColumn}{$lastRow}")->applyFromArray([
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'B7DEC2']],
                        ],
                    ]);

                    for ($row = 8; $row <= $lastRow; $row += 2) {
                        $sheet->getStyle("A{$row}:{$tableLastColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F0FDF4');
                    }

                    foreach ($this->columns as $index => $column) {
                        $letter = Coordinate::stringFromColumnIndex($index + 1);
                        if ($column === 'peso') {
                            $sheet->getStyle("{$letter}8:{$letter}{$lastRow}")
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
                        }
                        if ($column === 'precio_compra') {
                            $sheet->getStyle("{$letter}8:{$letter}{$lastRow}")
                                ->getNumberFormat()
                                ->setFormatCode('"S/ "#,##0.00');
                        }
                    }
                }

                $sheet->freezePane('A8');
                $sheet->setAutoFilter("A7:{$tableLastColumn}{$lastRow}");
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0);
            },
        ];
    }

    private function query()
    {
        $query = Animal::query()->where('fundo_id', $this->fundoId)->with(['especie', 'raza']);

        if ($this->filters['search'] ?? false) {
            $query->where(fn ($builder) => $builder->where('arete', 'like', '%'.$this->filters['search'].'%')
                ->orWhere('nombre', 'like', '%'.$this->filters['search'].'%')
                ->orWhereExists(fn ($history) => $history
                    ->selectRaw('1')
                    ->from('animal_identifiers')
                    ->whereColumn('animal_identifiers.animal_id', 'animales.id')
                    ->where('animal_identifiers.arete', 'like', '%'.$this->filters['search'].'%')));
        }

        foreach (['especie_id' => 'especieId', 'raza_id' => 'razaId', 'genero' => 'genero', 'estado_productivo' => 'estadoProductivo', 'estado_reproductivo' => 'estadoReproductivo', 'tipo_alta' => 'tipoAlta'] as $column => $filter) {
            if ($this->filters[$filter] ?? false) {
                $query->where($column, $this->filters[$filter]);
            }
        }

        if (($this->filters['activo'] ?? '') !== '') {
            $query->where('activo', (bool) $this->filters['activo']);
        }
        if ($this->filters['motivoBaja'] ?? null) {
            $query->where('motivo_baja', $this->filters['motivoBaja']);
        }

        if ($this->filters['fechaDesde'] ?? false) {
            $query->where('fecha_alta', '>=', $this->filters['fechaDesde']);
        }
        if ($this->filters['fechaHasta'] ?? false) {
            $query->where(
                'fecha_alta',
                '<',
                CarbonImmutable::parse($this->filters['fechaHasta'])->addDay()->toDateString()
            );
        }

        $sortBy = in_array($this->filters['sortBy'] ?? null, ['id', 'arete', 'nombre', 'fecha_alta', 'peso'], true)
            ? $this->filters['sortBy']
            : 'id';
        $sortDir = ($this->filters['sortDir'] ?? null) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDir);
    }

    private function map(Animal $animal): array
    {
        $values = [
            'arete' => $this->excelText($animal->arete),
            'nombre' => $this->excelText($animal->nombre ?: '-'),
            'especie' => $this->excelText($animal->especie?->nombre ?: '-'),
            'raza' => $this->excelText($animal->raza?->nombre ?: '-'),
            'genero' => $this->excelText(ucfirst($animal->genero)),
            'edad' => $this->excelText($animal->edad_texto),
            'peso' => $animal->peso !== null ? (float) $animal->peso : '-',
            'estado_reproductivo' => $this->excelText($animal->estado_reproductivo_label),
            'tipo_alta' => $this->excelText($animal->tipo_alta_label),
            'precio_compra' => $animal->precio_compra !== null ? (float) $animal->precio_compra : '-',
            'activo' => $this->excelText($animal->activo ? 'Activo' : 'Inactivo'),
            'fecha_alta' => $animal->fecha_alta?->format('d/m/Y') ?: '-',
        ];

        return array_values(array_intersect_key($values, array_flip($this->columns)));
    }

    private function filterSummary(): string
    {
        $filters = collect([
            'Búsqueda' => $this->filters['search'] ?? null,
            'Especie' => ($this->filters['especieId'] ?? null)
                ? Especie::find($this->filters['especieId'])?->nombre
                : null,
            'Raza' => ($this->filters['razaId'] ?? null)
                ? Raza::find($this->filters['razaId'])?->nombre
                : null,
            'Género' => match ($this->filters['genero'] ?? null) {
                'macho' => 'Macho',
                'hembra' => 'Hembra',
                default => null,
            },
            'Estado reproductivo' => Animal::REPRODUCTIVE_STATES[$this->filters['estadoReproductivo'] ?? ''] ?? null,
            'Procedencia' => Animal::ADMISSION_TYPES[$this->filters['tipoAlta'] ?? ''] ?? null,
            'Estado' => ($this->filters['activo'] ?? '') === ''
                ? null
                : ($this->filters['activo'] ? 'Activo' : 'Inactivo'),
            'Motivo de baja' => Animal::INACTIVE_REASONS[$this->filters['motivoBaja'] ?? ''] ?? null,
            'Desde' => $this->filters['fechaDesde'] ?? null,
            'Hasta' => $this->filters['fechaHasta'] ?? null,
        ])->filter();

        $summary = $filters->isEmpty()
            ? 'Sin filtros adicionales'
            : $filters->map(fn ($value, $name) => "{$name}: {$value}")->implode(' | ');

        return $this->excelText($summary);
    }

    private function excelText(mixed $value): string
    {
        $value = (string) ($value ?? '');

        return preg_match('/^\s*[=+\-@]/u', $value) ? "'{$value}" : $value;
    }
}
