<?php

namespace App\Exports;

use App\Models\Fundo;
use App\Models\Ordeno;
use App\Models\OrdenoFotoDiaria;
use App\Support\SystemBranding;
use Illuminate\Database\Eloquent\Builder;
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

class OrdenosExport implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    use Exportable;

    private const COLUMN_LABELS = [
        'fecha' => 'Fecha',
        'turno' => 'Turno',
        'tipo_registro' => 'Tipo de registro',
        'litros_total' => 'Litros totales',
        'cantidad_vacas' => 'Cantidad de vacas',
        'promedio' => 'Promedio (L/vaca)',
        'foto' => 'Foto diaria',
        'observaciones' => 'Observaciones',
        'created_at' => 'Registrado el',
    ];

    private array $columns;

    private array $headers;

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
        $ordenos = $this->query()->get();
        $photosByDate = OrdenoFotoDiaria::query()
            ->where('fundo_id', $this->fundoId)
            ->whereIn('fecha', $ordenos->pluck('fecha')->filter()->map->toDateString()->unique())
            ->get()
            ->keyBy(fn (OrdenoFotoDiaria $photo) => $photo->fecha?->toDateString());

        $ordenos->each(function (Ordeno $ordeno) use ($photosByDate): void {
            $ordeno->setAttribute('tiene_foto', $photosByDate->has($ordeno->fecha?->toDateString()));
        });

        $columnSummary = collect($this->headers)->join(', ', ' y ') ?: 'Ninguna';
        $generatedAt = now('America/Lima')->format('d/m/Y H:i');
        $rows = collect([
            [mb_strtoupper(app(SystemBranding::class)->name()).' - REPORTE DE PRODUCCION DE LECHE'],
            ['Fundo:', $this->excelText($fundo->nombre), 'Administrador(es):', $this->excelText($administrators)],
            ['Generado por:', $this->excelText($this->generatedBy), 'Fecha y hora (Perú):', $generatedAt],
            ['Resumen:', $ordenos->count().' registros. Columnas: '.$columnSummary.'.'],
            ['Filtros aplicados:', $this->filterSummary()],
            [],
            $this->headers ?: ['Sin columnas seleccionadas'],
        ]);

        return $rows->merge($ordenos->map(fn (Ordeno $ordeno) => $this->map($ordeno)));
    }

    public function title(): string
    {
        return 'Producción de leche';
    }

    public function columnWidths(): array
    {
        $widthByColumn = [
            'fecha' => 14,
            'turno' => 14,
            'tipo_registro' => 19,
            'litros_total' => 16,
            'cantidad_vacas' => 18,
            'promedio' => 19,
            'foto' => 13,
            'observaciones' => 40,
            'created_at' => 20,
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
                    'font' => ['bold' => true, 'size' => 17, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '075985']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle("A2:{$metaLastColumn}5")->applyFromArray([
                    'font' => ['color' => ['rgb' => '0C4A6E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2FE']],
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'BAE6FD']],
                    ],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getStyle('A2:A5')->getFont()->setBold(true)->getColor()->setRGB('0369A1');
                $sheet->getStyle('C2:C3')->getFont()->setBold(true)->getColor()->setRGB('0369A1');
                $sheet->getStyle("A7:{$tableLastColumn}7")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '075985']],
                    ],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(25);

                if ($lastRow > 7) {
                    $sheet->getStyle("A8:{$tableLastColumn}{$lastRow}")->applyFromArray([
                        'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'BAE6FD']],
                        ],
                    ]);

                    for ($row = 8; $row <= $lastRow; $row += 2) {
                        $sheet->getStyle("A{$row}:{$tableLastColumn}{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F0F9FF');
                    }
                }

                $sheet->freezePane('A8');
                $sheet->setAutoFilter("A7:{$tableLastColumn}{$lastRow}");
                $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
            },
        ];
    }

    private function query(): Builder
    {
        $sortBy = $this->filters['sortBy'] ?? 'fecha';
        $sortDir = strtolower((string) ($this->filters['sortDir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortableColumns = ['id', 'fecha', 'turno', 'tipo_registro', 'litros_total', 'cantidad_vacas', 'observaciones', 'created_at'];

        if (! in_array($sortBy, $sortableColumns, true)) {
            $sortBy = 'fecha';
        }

        return Ordeno::query()
            ->where('fundo_id', $this->fundoId)
            ->applyFilters($this->filters)
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir);
    }

    private function map(Ordeno $ordeno): array
    {
        $average = (int) $ordeno->cantidad_vacas > 0
            ? round((float) $ordeno->litros_total / (int) $ordeno->cantidad_vacas, 2)
            : 0;
        $values = [
            'fecha' => $ordeno->fecha?->format('d/m/Y') ?: 'Sin dato',
            'turno' => $ordeno->turno ? Ordeno::turnoLabel($ordeno->turno) : 'Sin dato',
            'tipo_registro' => $ordeno->tipo_registro ? Ordeno::tipoLabel($ordeno->tipo_registro) : 'Sin dato',
            'litros_total' => (float) ($ordeno->litros_total ?? 0),
            'cantidad_vacas' => (int) ($ordeno->cantidad_vacas ?? 0),
            'promedio' => $average,
            'foto' => $ordeno->tiene_foto ? 'Sí' : 'No',
            'observaciones' => $this->excelText($ordeno->observaciones ?: 'Sin observaciones'),
            'created_at' => $ordeno->created_at?->copy()->timezone('America/Lima')->format('d/m/Y H:i') ?: 'Sin dato',
        ];

        return array_values(array_intersect_key($values, array_flip($this->columns)));
    }

    private function filterSummary(): string
    {
        $filters = collect([
            'Desde' => $this->filters['fechaDesde'] ?? null,
            'Hasta' => $this->filters['fechaHasta'] ?? null,
            'Turno' => ($this->filters['turno'] ?? null)
                ? Ordeno::turnoLabel((string) $this->filters['turno'])
                : null,
            'Tipo' => ($this->filters['tipoRegistro'] ?? null)
                ? Ordeno::tipoLabel((string) $this->filters['tipoRegistro'])
                : null,
            'Litros mín.' => ($this->filters['litrosMin'] ?? '') !== '' ? $this->filters['litrosMin'] : null,
            'Litros máx.' => ($this->filters['litrosMax'] ?? '') !== '' ? $this->filters['litrosMax'] : null,
            'Observación' => $this->filters['observacion'] ?? null,
            'Foto' => ($this->filters['conFoto'] ?? '') === ''
                ? null
                : (($this->filters['conFoto'] ?? '') === '1' ? 'Sí' : 'No'),
        ])->filter(fn ($value) => $value !== null && $value !== '');

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
