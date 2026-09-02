<?php

namespace App\Exports;

use App\Models\Especie;
use App\Models\Fundo;
use App\Models\Raza;
use App\Support\AnimalCodeAllocator;
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

class AnimalesTemplateExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly int $fundoId
    ) {}

    public function sheets(): array
    {
        return [
            new AnimalesDataSheet($this->fundoId),
            new AnimalesGuideSheet($this->fundoId),
        ];
    }
}

class AnimalesDataSheet implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    public const HEADERS = [
        'tipo_animal' => 'Tipo de animal (*)',
        'raza' => 'Raza (*)',
        'codigo_arete' => 'Código / Arete (Opcional - se genera ej. BOV26-001 si está vacío)',
        'nombre' => 'Nombre del animal',
        'genero' => 'Género (Hembra / Macho) (*)',
        'fecha_nacimiento' => 'Fecha Nacimiento (AAAA-MM-DD)',
        'edad_meses' => 'Edad Estimada (Meses)',
        'peso' => 'Peso Actual (kg)',
        'estado_reproductivo' => 'Estado Reproductivo (Vacia, Gestante, Lactante, Seca)',
        'tipo_alta' => 'Procedencia (Compra, Parto, Donacion, Traslado)',
        'precio_compra' => 'Precio Compra (S/)',
        'fecha_alta' => 'Fecha de Ingreso (AAAA-MM-DD)',
        'apta_ordeno' => 'Apta Ordeño (SI / NO)',
        'observaciones' => 'Observaciones',
    ];

    public function __construct(
        private readonly int $fundoId
    ) {}

    public function collection(): Collection
    {
        // Sheet 1 contains ONLY headers ready for user data - NO fake example rows that could pollute DB
        return collect([
            array_values(self::HEADERS),
        ]);
    }

    public function title(): string
    {
        return 'Animales a Registrar';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 22,
            'C' => 32,
            'D' => 22,
            'E' => 24,
            'F' => 26,
            'G' => 22,
            'H' => 18,
            'I' => 26,
            'J' => 22,
            'K' => 18,
            'L' => 24,
            'M' => 18,
            'N' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                // Header Row (Row 1)
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

class AnimalesGuideSheet implements FromCollection, WithColumnWidths, WithEvents, WithTitle
{
    public function __construct(
        private readonly int $fundoId
    ) {}

    public function collection(): Collection
    {
        $fundo = Fundo::find($this->fundoId);
        $especies = Especie::with('razas')->where('activo', true)->get();
        $yearShort = now()->format('y');

        $rows = collect([
            [mb_strtoupper(app(SystemBranding::class)->name()).' - GUÍA OFICIAL DE CÓDIGOS Y FORMATOS POR ESPECIE'],
            ['Fundo:', $fundo?->nombre ?? 'Mi Fundo', 'Año activo:', now()->year],
            [],
            ['1. CÓDIGOS AUTOMÁTICOS POR TIPO DE ANIMAL:'],
            ['Tipo de Animal', 'Prefijo Código', 'Ejemplo de Arete Generado', 'Razas disponibles en el fundo'],
        ]);

        foreach ($especies as $esp) {
            $prefijo = $esp->codigo_animal ?: 'BOV';
            $ejemploCodigo = "{$prefijo}{$yearShort}-001";
            $razasStr = $esp->razas->pluck('nombre')->implode(', ') ?: 'General';
            $rows->push([$esp->nombre, $prefijo, $ejemploCodigo, $razasStr]);
        }

        $rows->push([]);
        $rows->push(['2. EJEMPLOS DE REGISTRO CORRECTO (Solo como referencia, no ingresar en la hoja 1):']);
        $rows->push([
            'Tipo de animal', 'Raza', 'Código / Arete', 'Nombre', 'Género', 'Fecha Nacimiento',
            'Edad Meses', 'Peso (kg)', 'Estado Reproductivo', 'Procedencia', 'Precio (S/)',
            'Fecha Ingreso', 'Apta Ordeño', 'Observaciones',
        ]);
        $rows->push([
            'Bovino', 'Holstein', "BOV{$yearShort}-001", 'PALOMA', 'Hembra', '2023-05-10',
            '', '450.50', 'Lactante', 'Compra', '3500.00', '2024-01-15', 'SI', 'Vaca lechera en producción',
        ]);
        $rows->push([
            'Bovino', 'Brown Swiss', '', 'TORITO', 'Macho', '',
            '18', '380.00', '', 'Parto', '0.00', '2024-02-01', 'NO', 'Arete se autogenera si se deja vacío',
        ]);
        $rows->push([
            'Porcino', 'Landrace', "POR{$yearShort}-001", 'CERDA 1', 'Hembra', '2024-01-01',
            '', '110.00', 'Gestante', 'Compra', '800.00', '2024-03-01', 'NO', 'Marrana de cría',
        ]);
        $rows->push([
            'Ovino', 'Dorper', "OVI{$yearShort}-001", 'CARNERO', 'Macho', '2023-11-20',
            '', '65.00', '', 'Donacion', '0.00', '2024-01-10', 'NO', 'Reproductor ovino',
        ]);

        $rows->push([]);
        $rows->push(['3. VALORES VÁLIDOS PARA CADA CAMPO:']);
        $rows->push(['Campo', 'Valores Aceptados / Formato']);
        $rows->push(['Tipo de animal', 'Bovino, Equino, Ovino, Porcino, Caprino, Cuy, Ave, Camélido']);
        $rows->push(['Género', 'Hembra (o H) | Macho (o M)']);
        $rows->push(['Fecha Nacimiento / Ingreso', 'Formato AAAA-MM-DD (ejemplo: 2024-03-15)']);
        $rows->push(['Estado Reproductivo', 'Vacia, Gestante, Lactante, Seca, En produccion (solo aplica a hembras)']);
        $rows->push(['Procedencia', 'Compra, Parto, Donacion, Traslado, Prestamo']);
        $rows->push(['Apta Ordeño', 'SI o NO (solo hembras bovinas en producción)']);

        return $rows;
    }

    public function title(): string
    {
        return 'Guía y Códigos';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24,
            'B' => 22,
            'C' => 28,
            'D' => 38,
            'E' => 18,
            'F' => 20,
            'G' => 16,
            'H' => 16,
            'I' => 22,
            'J' => 18,
            'K' => 16,
            'L' => 18,
            'M' => 16,
            'N' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('064E3B');
                $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('047857');
                $sheet->getStyle('A5:D5')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']],
                ]);
            },
        ];
    }
}
