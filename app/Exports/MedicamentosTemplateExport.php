<?php

namespace App\Exports;

use App\Models\Fundo;
use App\Models\Medicamento;
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

class MedicamentosTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly int $fundoId
    ) {}

    public function sheets(): array
    {
        return [
            new MedicamentosDataSheet($this->fundoId),
            new MedicamentosGuideSheet($this->fundoId),
        ];
    }
}

class MedicamentosDataSheet implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    public const HEADERS = [
        'nombre' => 'Nombre Comercial (*)',
        'tipo' => 'Categoría / Tipo (*)',
        'principio_activo' => 'Principio Activo',
        'concentracion' => 'Concentración (ej. 200 mg/ml)',
        'presentacion' => 'Presentación (ej. Frasco 100ml, Blister 12 tab)',
        'unidad_stock' => 'Unidad de Dosificación (*) (ml, dosis, tableta, sobre, g, kg, unidad)',
        'stock_minimo' => 'Alerta Stock Bajo (Mínimo)',
        'numero_lote' => 'N° de Lote (*)',
        'fecha_ingreso' => 'Fecha de Ingreso (AAAA-MM-DD)',
        'fecha_vencimiento' => 'Fecha de Vencimiento (*) (AAAA-MM-DD)',
        'cantidad_inicial' => 'Cantidad Inicial en Stock (*)',
        'costo_total' => 'Costo Total de Compra (S/)',
        'proveedor' => 'Proveedor / Veterinaria',
        'laboratorio' => 'Laboratorio Fabricante',
        'ubicacion' => 'Ubicación en Botiquín (ej. Refrigerador, Estante A)',
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
        return 'Medicamentos a Registrar';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 26,
            'B' => 24,
            'C' => 24,
            'D' => 24,
            'E' => 26,
            'F' => 22,
            'G' => 18,
            'H' => 18,
            'I' => 22,
            'J' => 24,
            'K' => 22,
            'L' => 20,
            'M' => 22,
            'N' => 20,
            'O' => 22,
            'P' => 28,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:P1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0284C7'], // Sky-600
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '0369A1'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(32);
                $sheet->freezePane('A2');
            },
        ];
    }
}

class MedicamentosGuideSheet implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    public function __construct(
        private readonly int $fundoId
    ) {}

    public function collection(): Collection
    {
        $fundo = Fundo::find($this->fundoId);

        $rows = collect([
            [mb_strtoupper(app(SystemBranding::class)->name()).' - GUÍA DE IMPORTACIÓN DE MEDICAMENTOS Y BOTIQUÍN'],
            ['Fundo:', $fundo?->nombre ?? 'Mi Fundo', 'Fecha:', now('America/Lima')->format('d/m/Y')],
            [],
            ['1. EJEMPLOS DE REGISTRO CORRECTO (Solo referencia, no ingresar en la hoja 1):'],
            [
                'Nombre Comercial', 'Categoría / Tipo', 'Principio Activo', 'Concentración',
                'Presentación', 'Unidad de Dosificación', 'Alerta Stock', 'N° Lote',
                'Fecha Ingreso', 'Fecha Vencimiento', 'Cantidad en Stock', 'Costo Total',
                'Proveedor', 'Laboratorio', 'Ubicación', 'Observaciones',
            ],
            [
                'OXITETRACICLINA 200', 'Antibiótico', 'Oxitetraciclina L.A.', '200 mg/ml',
                'Frasco 250ml', 'ml', '50', 'L-84920',
                now()->subMonth()->format('Y-m-d'), now()->addYear()->format('Y-m-d'), '250', '65.00',
                'Agroveterinaria El Campo', 'Agrovet Market', 'Estante Antibióticos', 'Antibiótico de amplio espectro',
            ],
            [
                'IVERMECTINA 1%', 'Antiparasitario', 'Ivermectina', '10 mg/ml',
                'Frasco 500ml', 'ml', '100', 'IV-2025',
                now()->subMonths(2)->format('Y-m-d'), now()->addYears(2)->format('Y-m-d'), '500', '85.00',
                'Veterinaria San Martín', 'Montana', 'Estante Parasitarios', 'Antiparasitario interno y externo',
            ],
            [
                'ALBENDAZOL 10% TABLETAS', 'Antiparasitario', 'Albendazol', '1000 mg',
                'Caja de 50 tabletas', 'tableta', '10', 'TAB-9901',
                now()->subMonths(3)->format('Y-m-d'), now()->addMonths(18)->format('Y-m-d'), '50', '45.00',
                'Distribuidora Ganadera', 'Biomont', 'Caja 3', 'Dosis: 1 tableta por cada 100kg',
            ],
            [],
            ['2. CATEGORÍAS VÁLIDAS:'],
            ['Categoría', 'Términos aceptados en el Excel'],
        ]);

        foreach (Medicamento::TYPES as $key => $name) {
            $rows->push([$name, "{$key} | {$name}"]);
        }

        $rows->push([]);
        $rows->push(['3. UNIDADES DE STOCK DISPONIBLES:']);
        $rows->push(['Unidad', 'Descripción y uso']);
        $rows->push(['ml', 'Para medicamentos líquidos inyectables o suspensiones orales (ej. 250 ml)']);
        $rows->push(['tableta', 'Para pastillas, bolos o comprimidos (ej. 12 tabletas)']);
        $rows->push(['dosis', 'Para vacunas o tratamientos por dosis completas (ej. 50 dosis)']);
        $rows->push(['sobre', 'Para sobres en polvo o sales rehidratantes (ej. 10 sobres)']);
        $rows->push(['g / kg', 'Para polvos solubles o mezclas a granel']);
        $rows->push(['frasco / unidad', 'Para unidades selladas']);

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
            'D' => 24,
            'E' => 26,
            'F' => 22,
            'G' => 16,
            'H' => 16,
            'I' => 18,
            'J' => 20,
            'K' => 18,
            'L' => 16,
            'M' => 22,
            'N' => 20,
            'O' => 20,
            'P' => 28,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0369A1');
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('0284C7');
                $sheet->getStyle('A5:P5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']],
                ]);
            },
        ];
    }
}
