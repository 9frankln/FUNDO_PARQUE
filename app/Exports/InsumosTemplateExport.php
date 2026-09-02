<?php

namespace App\Exports;

use App\Models\Fundo;
use App\Models\Insumo;
use App\Support\SystemBranding;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InsumosTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly int $fundoId
    ) {}

    public function sheets(): array
    {
        return [
            new InsumosDataSheet($this->fundoId),
            new InsumosGuideSheet($this->fundoId),
        ];
    }
}

class InsumosDataSheet implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    public const HEADERS = [
        'nombre' => 'Nombre del Insumo / Material (*)',
        'tipo' => 'Categoría (*)',
        'presentacion' => 'Presentación (ej. Caja x 100, Rollo 50m)',
        'marca_laboratorio' => 'Marca / Fabricante',
        'unidad_stock' => 'Unidad de Conteo (*) (unidad, par, frasco, paquete, caja, rollo, litro, ml, g, kg)',
        'stock_minimo' => 'Alerta Stock Bajo (Mínimo)',
        'numero_lote' => 'N° Lote / Código Ingreso',
        'fecha_ingreso' => 'Fecha de Ingreso (AAAA-MM-DD)',
        'fecha_vencimiento' => 'Fecha Vencimiento (Opcional - AAAA-MM-DD)',
        'cantidad_inicial' => 'Cantidad Inicial en Stock (*)',
        'costo_total' => 'Costo Total de Compra (S/)',
        'proveedor' => 'Proveedor / Tienda',
        'ubicacion' => 'Ubicación en Almacén',
        'observaciones' => 'Observaciones',
    ];

    public function __construct(
        private readonly int $fundoId
    ) {}

    public function collection(): Collection
    {
        return collect([
            array_values(self::HEADERS),
        ]);
    }

    public function title(): string
    {
        return 'Insumos a Registrar';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 26,
            'C' => 26,
            'D' => 22,
            'E' => 24,
            'F' => 18,
            'G' => 20,
            'H' => 22,
            'I' => 24,
            'J' => 22,
            'K' => 20,
            'L' => 22,
            'M' => 22,
            'N' => 28,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:N1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '059669'], // Emerald-600
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '047857'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->freezePane('A2');
            },
        ];
    }
}

class InsumosGuideSheet implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    public function __construct(
        private readonly int $fundoId
    ) {}

    public function collection(): Collection
    {
        $fundo = Fundo::find($this->fundoId);

        $rows = collect([
            [mb_strtoupper(app(SystemBranding::class)->name()).' - GUÍA DE IMPORTACIÓN DE INSUMOS Y MATERIALES'],
            ['Fundo:', $fundo?->nombre ?? 'Mi Fundo', 'Fecha:', now('America/Lima')->format('d/m/Y')],
            [],
            ['1. EJEMPLOS DE REGISTRO CORRECTO (Solo referencia, no ingresar en la hoja 1):'],
            [
                'Nombre del Insumo', 'Categoría', 'Presentación', 'Marca',
                'Unidad', 'Alerta Stock', 'N° Lote', 'Fecha Ingreso', 'Fecha Vencimiento',
                'Cantidad Stock', 'Costo Total', 'Proveedor', 'Ubicación', 'Observaciones',
            ],
            [
                'JERINGAS DESCARTABLES 20ML', 'Material descartable', 'Caja x 50 unidades', 'Nipro',
                'unidad', '20', 'J-2026', now()->subMonth()->format('Y-m-d'), '',
                '100', '45.00', 'Agroveterinaria', 'Estante Materiales', 'Para aplicación de vacunas',
            ],
            [
                'ALCOHOL YODADO 10%', 'Antiséptico y desinfectante', 'Galón x 3.8 Litros', 'Alkofarma',
                'litro', '2', 'AY-99', now()->subMonths(2)->format('Y-m-d'), now()->addYears(2)->format('Y-m-d'),
                '4', '75.00', 'Farmacia Central', 'Gabinete Desinfección', 'Desinfección de ombligos y heridas',
            ],
            [
                'ALGODÓN HIDRÓFILO 500G', 'Material de curación', 'Paquete x 500g', 'Zodiac',
                'paquete', '3', 'ALG-01', now()->subMonths(1)->format('Y-m-d'), '',
                '10', '30.00', 'Distribuidora Médica', 'Caja Curación', 'Uso en curaciones y desinfección',
            ],
            [],
            ['2. CATEGORÍAS VÁLIDAS:'],
            ['Categoría', 'Términos aceptados en el Excel'],
        ]);

        foreach (Insumo::TYPES as $key => $name) {
            $rows->push([$name, "{$key} | {$name}"]);
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Guía y Ejemplos';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 26,
            'C' => 24,
            'D' => 22,
            'E' => 16,
            'F' => 16,
            'G' => 16,
            'H' => 18,
            'I' => 18,
            'J' => 16,
            'K' => 16,
            'L' => 22,
            'M' => 22,
            'N' => 28,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('065F46');
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('059669');
                $sheet->getStyle('A5:N5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                ]);
            },
        ];
    }
}
