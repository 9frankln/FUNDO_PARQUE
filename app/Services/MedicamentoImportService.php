<?php

namespace App\Services;

use App\Models\Medicamento;
use App\Models\MedicamentoLote;
use App\Models\MedicamentoMovimiento;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MedicamentoImportService
{
    private const TYPE_MAP = [
        'antibiotico' => 'antibiotico',
        'antibiótico' => 'antibiotico',
        'antiparasitario' => 'antiparasitario',
        'antiinflamatorio' => 'antiinflamatorio',
        'analgesico' => 'analgesico_anestesico',
        'analgésico' => 'analgesico_anestesico',
        'anestesico' => 'analgesico_anestesico',
        'anestésico' => 'analgesico_anestesico',
        'analgesico_anestesico' => 'analgesico_anestesico',
        'vitamina' => 'vitamina_mineral',
        'mineral' => 'vitamina_mineral',
        'vitamina_mineral' => 'vitamina_mineral',
        'reconstituyente' => 'vitamina_mineral',
        'antiseptico' => 'antiseptico',
        'antiséptico' => 'antiseptico',
        'topico' => 'antiseptico',
        'tópico' => 'antiseptico',
        'cicatrizante' => 'antiseptico',
        'suero' => 'suero_rehidratante',
        'rehidratante' => 'suero_rehidratante',
        'suero_rehidratante' => 'suero_rehidratante',
        'hormonal' => 'hormonal_reproductivo',
        'hormonal_reproductivo' => 'hormonal_reproductivo',
        'vacuna' => 'vacuna',
        'biologico' => 'vacuna',
        'biológico' => 'vacuna',
        'otro' => 'otro',
    ];

    private const UNIT_MAP = [
        'ml' => 'ml',
        'mililitros' => 'ml',
        'cc' => 'ml',
        'dosis' => 'dosis',
        'tableta' => 'tableta',
        'tabletas' => 'tableta',
        'pastilla' => 'tableta',
        'pastillas' => 'tableta',
        'comprimido' => 'tableta',
        'comprimidos' => 'tableta',
        'sobre' => 'sobre',
        'sobres' => 'sobre',
        'g' => 'g',
        'gramos' => 'g',
        'gr' => 'g',
        'kg' => 'kg',
        'kilos' => 'kg',
        'unidad' => 'unidad',
        'unidades' => 'unidad',
        'und' => 'unidad',
        'frasco' => 'frasco',
        'frascos' => 'frasco',
    ];

    /**
     * @return array{success: bool, total: int, valid: int, invalid: int, imported: int, rows: array, errors: array}
     */
    public function import(UploadedFile|string $filePath, int $fundoId, bool $dryRun = false): array
    {
        $realPath = is_string($filePath) ? $filePath : $filePath->getRealPath();
        $spreadsheet = IOFactory::load($realPath);

        $worksheet = $spreadsheet->getSheetByName('Medicamentos a Registrar') ?? $spreadsheet->getActiveSheet();
        $rawRows = $worksheet->toArray(null, true, true, false);

        if (empty($rawRows)) {
            return [
                'success' => false,
                'total' => 0,
                'valid' => 0,
                'invalid' => 0,
                'imported' => 0,
                'rows' => [],
                'errors' => [['row' => 0, 'producto' => '-', 'messages' => ['El archivo o la hoja de datos está vacía.']]],
            ];
        }

        $headerRowIndex = null;
        $colMap = [
            'nombre' => null,
            'tipo' => null,
            'principio_activo' => null,
            'concentracion' => null,
            'presentacion' => null,
            'unidad_stock' => null,
            'stock_minimo' => null,
            'numero_lote' => null,
            'fecha_ingreso' => null,
            'fecha_vencimiento' => null,
            'cantidad_inicial' => null,
            'costo_total' => null,
            'proveedor' => null,
            'laboratorio' => null,
            'ubicacion' => null,
            'observaciones' => null,
        ];

        foreach ($rawRows as $idx => $row) {
            foreach ($row as $colIdx => $cell) {
                $c = mb_strtolower(trim((string) $cell));
                if (str_contains($c, 'nombre comercial') || str_contains($c, 'medicamento') || str_contains($c, 'producto')) {
                    $colMap['nombre'] = $colIdx;
                } elseif (str_contains($c, 'categoría') || str_contains($c, 'categoria') || str_contains($c, 'tipo')) {
                    $colMap['tipo'] = $colIdx;
                } elseif (str_contains($c, 'principio')) {
                    $colMap['principio_activo'] = $colIdx;
                } elseif (str_contains($c, 'concentraci')) {
                    $colMap['concentracion'] = $colIdx;
                } elseif (str_contains($c, 'presentaci')) {
                    $colMap['presentacion'] = $colIdx;
                } elseif (str_contains($c, 'unidad')) {
                    $colMap['unidad_stock'] = $colIdx;
                } elseif (str_contains($c, 'mínimo') || str_contains($c, 'minimo') || str_contains($c, 'alerta')) {
                    $colMap['stock_minimo'] = $colIdx;
                } elseif (str_contains($c, 'lote')) {
                    $colMap['numero_lote'] = $colIdx;
                } elseif (str_contains($c, 'ingreso')) {
                    $colMap['fecha_ingreso'] = $colIdx;
                } elseif (str_contains($c, 'vencimiento')) {
                    $colMap['fecha_vencimiento'] = $colIdx;
                } elseif (str_contains($c, 'cantidad') || str_contains($c, 'stock')) {
                    $colMap['cantidad_inicial'] = $colIdx;
                } elseif (str_contains($c, 'costo') || str_contains($c, 'precio')) {
                    $colMap['costo_total'] = $colIdx;
                } elseif (str_contains($c, 'proveedor') || str_contains($c, 'veterinaria')) {
                    $colMap['proveedor'] = $colIdx;
                } elseif (str_contains($c, 'laboratorio')) {
                    $colMap['laboratorio'] = $colIdx;
                } elseif (str_contains($c, 'ubicaci')) {
                    $colMap['ubicacion'] = $colIdx;
                } elseif (str_contains($c, 'observaci') || str_contains($c, 'nota')) {
                    $colMap['observaciones'] = $colIdx;
                }
            }

            if ($colMap['nombre'] !== null || $colMap['numero_lote'] !== null) {
                $headerRowIndex = $idx;
                break;
            }
        }

        if ($headerRowIndex === null) {
            $headerRowIndex = 0;
            $colMap = [
                'nombre' => 0,
                'tipo' => 1,
                'principio_activo' => 2,
                'concentracion' => 3,
                'presentacion' => 4,
                'unidad_stock' => 5,
                'stock_minimo' => 6,
                'numero_lote' => 7,
                'fecha_ingreso' => 8,
                'fecha_vencimiento' => 9,
                'cantidad_inicial' => 10,
                'costo_total' => 11,
                'proveedor' => 12,
                'laboratorio' => 13,
                'ubicacion' => 14,
                'observaciones' => 15,
            ];
        }

        $dataRows = array_slice($rawRows, $headerRowIndex + 1);

        $validRecords = [];
        $validationErrors = [];
        $processedRows = [];

        foreach ($dataRows as $offset => $row) {
            $rowNum = $headerRowIndex + $offset + 2;

            $nonEmptyCells = array_filter($row, fn ($c) => $c !== null && trim((string) $c) !== '');
            if (empty($nonEmptyCells)) {
                continue;
            }

            $getVal = fn ($key) => $colMap[$key] !== null && isset($row[$colMap[$key]]) ? trim((string) $row[$colMap[$key]]) : '';

            $rawNombre = $getVal('nombre');
            $rawTipo = $getVal('tipo');
            $rawPrincipio = $getVal('principio_activo');
            $rawConcentracion = $getVal('concentracion');
            $rawPresentacion = $getVal('presentacion');
            $rawUnidad = $getVal('unidad_stock');
            $rawStockMin = $getVal('stock_minimo');
            $rawLote = $getVal('numero_lote');
            $rawFechaIngreso = $getVal('fecha_ingreso');
            $rawFechaVenc = $getVal('fecha_vencimiento');
            $rawCantidad = $getVal('cantidad_inicial');
            $rawCosto = $getVal('costo_total');
            $rawProveedor = $getVal('proveedor');
            $rawLaboratorio = $getVal('laboratorio');
            $rawUbicacion = $getVal('ubicacion');
            $rawObservaciones = $getVal('observaciones');

            // Skip guide title lines
            $rowString = mb_strtolower(implode(' ', $row));
            if (str_contains($rowString, 'guía de importación') || str_contains($rowString, 'categorías válidas')) {
                continue;
            }

            $rowErrors = [];

            // 1. Nombre
            if ($rawNombre === '') {
                $rowErrors[] = 'El nombre comercial del medicamento es obligatorio.';
            }

            // 2. Tipo / Categoría
            $tipoKey = 'otro';
            if ($rawTipo !== '') {
                $tipoKey = self::TYPE_MAP[mb_strtolower($rawTipo)] ?? 'otro';
            }

            // 3. Unidad Stock
            $unidadKey = 'ml';
            if ($rawUnidad !== '') {
                $unidadKey = self::UNIT_MAP[mb_strtolower($rawUnidad)] ?? 'ml';
            }

            // 4. Número Lote
            $numeroLote = $rawLote !== '' ? mb_strtoupper($rawLote) : 'LOTE-'.now()->format('Ymd').'-'.($offset + 1);

            // 5. Fecha Ingreso & Vencimiento
            $fechaIngreso = now()->format('Y-m-d');
            if ($rawFechaIngreso !== '') {
                $parsedIng = $this->parseDate($rawFechaIngreso);
                if ($parsedIng) {
                    $fechaIngreso = $parsedIng->format('Y-m-d');
                }
            }

            $fechaVencimiento = null;
            if ($rawFechaVenc === '') {
                $rowErrors[] = 'La fecha de vencimiento es obligatoria (AAAA-MM-DD).';
            } else {
                $parsedVenc = $this->parseDate($rawFechaVenc);
                if ($parsedVenc) {
                    $fechaVencimiento = $parsedVenc->format('Y-m-d');
                } else {
                    $rowErrors[] = 'Fecha de vencimiento inválida (use AAAA-MM-DD).';
                }
            }

            // 6. Cantidad Inicial
            $cantidadInicial = null;
            if ($rawCantidad === '') {
                $rowErrors[] = 'La cantidad inicial de stock es obligatoria.';
            } else {
                $cantClean = str_replace(',', '.', $rawCantidad);
                if (is_numeric($cantClean) && (float) $cantClean > 0) {
                    $cantidadInicial = round((float) $cantClean, 3);
                } else {
                    $rowErrors[] = 'La cantidad inicial debe ser un número mayor a 0.';
                }
            }

            // 7. Costo Total
            $costoTotal = null;
            if ($rawCosto !== '') {
                $costoClean = str_replace([',', 'S/', 's/', '$', ' '], ['', '', '', '', ''], $rawCosto);
                if (is_numeric($costoClean) && (float) $costoClean >= 0) {
                    $costoTotal = round((float) $costoClean, 2);
                }
            }

            // 8. Stock Mínimo
            $stockMinimo = null;
            if ($rawStockMin !== '') {
                $stockMinClean = str_replace(',', '.', $rawStockMin);
                if (is_numeric($stockMinClean) && (float) $stockMinClean >= 0) {
                    $stockMinimo = round((float) $stockMinClean, 2);
                }
            }

            $recordData = [
                'fundo_id' => $fundoId,
                'nombre' => mb_strtoupper($rawNombre),
                'tipo' => $tipoKey,
                'principio_activo' => $rawPrincipio !== '' ? mb_strtoupper($rawPrincipio) : null,
                'concentracion' => $rawConcentracion !== '' ? $rawConcentracion : null,
                'presentacion' => $rawPresentacion !== '' ? $rawPresentacion : null,
                'unidad_stock' => $unidadKey,
                'stock_minimo' => $stockMinimo,
                'numero_lote' => $numeroLote,
                'fecha_ingreso' => $fechaIngreso,
                'fecha_vencimiento' => $fechaVencimiento,
                'cantidad_inicial' => $cantidadInicial,
                'costo_total' => $costoTotal,
                'proveedor' => $rawProveedor !== '' ? mb_strtoupper($rawProveedor) : null,
                'laboratorio' => $rawLaboratorio !== '' ? mb_strtoupper($rawLaboratorio) : null,
                'ubicacion' => $rawUbicacion !== '' ? $rawUbicacion : null,
                'observaciones' => $rawObservaciones !== '' ? $rawObservaciones : null,
            ];

            if (! empty($rowErrors)) {
                $validationErrors[] = [
                    'row' => $rowNum,
                    'producto' => $rawNombre ?: "(Fila {$rowNum})",
                    'messages' => $rowErrors,
                ];
            } else {
                $validRecords[] = $recordData;
            }

            $processedRows[] = [
                'row' => $rowNum,
                'data' => $recordData,
                'valid' => empty($rowErrors),
                'errors' => $rowErrors,
            ];
        }

        $importedCount = 0;

        if (! $dryRun && ! empty($validRecords)) {
            DB::transaction(function () use ($validRecords, $fundoId, &$importedCount) {
                foreach ($validRecords as $item) {
                    // Find or create Medicamento for this fundo
                    $medicamento = Medicamento::firstOrCreate(
                        [
                            'fundo_id' => $fundoId,
                            'nombre' => $item['nombre'],
                        ],
                        [
                            'tipo' => $item['tipo'],
                            'principio_activo' => $item['principio_activo'],
                            'concentracion' => $item['concentracion'],
                            'presentacion' => $item['presentacion'],
                            'unidad_stock' => $item['unidad_stock'],
                            'stock_minimo' => $item['stock_minimo'] ?? 0,
                            'laboratorio' => $item['laboratorio'],
                            'activo' => true,
                        ]
                    );

                    // Create MedicamentoLote
                    $lote = MedicamentoLote::create([
                        'fundo_id' => $fundoId,
                        'medicamento_id' => $medicamento->id,
                        'numero_lote' => $item['numero_lote'],
                        'fecha_ingreso' => $item['fecha_ingreso'],
                        'fecha_vencimiento' => $item['fecha_vencimiento'],
                        'cantidad_inicial' => $item['cantidad_inicial'],
                        'cantidad_disponible' => $item['cantidad_inicial'],
                        'costo_total' => $item['costo_total'],
                        'proveedor' => $item['proveedor'],
                        'ubicacion' => $item['ubicacion'],
                        'observaciones' => $item['observaciones'],
                        'activo' => true,
                    ]);

                    // Register initial stock movement
                    MedicamentoMovimiento::create([
                        'fundo_id' => $fundoId,
                        'medicamento_id' => $medicamento->id,
                        'medicamento_lote_id' => $lote->id,
                        'tipo' => 'ingreso',
                        'cantidad' => $item['cantidad_inicial'],
                        'unidad' => $item['unidad_stock'],
                        'saldo_lote' => $item['cantidad_inicial'],
                        'detalle' => 'Carga inicial masiva de inventario',
                        'fecha_hora' => Carbon::parse($item['fecha_ingreso'])->startOfDay(),
                    ]);

                    $importedCount++;
                }
            });
        }

        return [
            'success' => empty($validationErrors),
            'total' => count($processedRows),
            'valid' => count($validRecords),
            'invalid' => count($validationErrors),
            'imported' => $importedCount,
            'rows' => $processedRows,
            'errors' => $validationErrors,
        ];
    }

    private function parseDate(string $dateStr): ?Carbon
    {
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y', 'Y-m-d H:i:s'];
        foreach ($formats as $fmt) {
            try {
                $c = Carbon::createFromFormat($fmt, $dateStr);
                if ($c !== false) {
                    return $c;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        $timestamp = strtotime($dateStr);
        if ($timestamp !== false && $timestamp > 0) {
            return Carbon::createFromTimestamp($timestamp);
        }

        return null;
    }
}
