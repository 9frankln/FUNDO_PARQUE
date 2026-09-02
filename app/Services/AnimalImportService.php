<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Especie;
use App\Models\Raza;
use App\Support\AnimalCodeAllocator;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AnimalImportService
{
    private const GENDER_MAP = [
        'h' => 'hembra',
        'hembra' => 'hembra',
        'female' => 'hembra',
        'vaca' => 'hembra',
        'vaquilla' => 'hembra',
        'ternera' => 'hembra',
        'm' => 'macho',
        'macho' => 'macho',
        'male' => 'macho',
        'toro' => 'macho',
        'torete' => 'macho',
        'ternero' => 'macho',
    ];

    private const REPRODUCTIVE_STATE_MAP = [
        'vacia' => 'vacia',
        'vacía' => 'vacia',
        'gestante' => 'gestante',
        'preñada' => 'gestante',
        'prenada' => 'gestante',
        'lactante' => 'lactante',
        'en lactancia' => 'lactante',
        'seca' => 'seca',
        'en_produccion' => 'en_produccion',
        'en producción' => 'en_produccion',
        'en produccion' => 'en_produccion',
    ];

    private const ADMISSION_TYPE_MAP = [
        'compra' => 'compra',
        'comprado' => 'compra',
        'parto' => 'parto',
        'nacimiento' => 'parto',
        'nacido' => 'parto',
        'donacion' => 'donacion',
        'donación' => 'donacion',
        'traslado' => 'traslado',
        'prestamo' => 'prestamo',
        'préstamo' => 'prestamo',
    ];

    /**
     * Parse and validate file, optionally inserting valid records.
     *
     * @return array{success: bool, total: int, valid: int, invalid: int, imported: int, rows: array, errors: array}
     */
    public function import(UploadedFile|string $filePath, int $fundoId, bool $dryRun = false): array
    {
        $realPath = is_string($filePath) ? $filePath : $filePath->getRealPath();
        $spreadsheet = IOFactory::load($realPath);

        // Prefer "Animales a Registrar" sheet if it exists, otherwise first sheet
        $worksheet = $spreadsheet->getSheetByName('Animales a Registrar') ?? $spreadsheet->getActiveSheet();
        $rawRows = $worksheet->toArray(null, true, true, false);

        if (empty($rawRows)) {
            return [
                'success' => false,
                'total' => 0,
                'valid' => 0,
                'invalid' => 0,
                'imported' => 0,
                'rows' => [],
                'errors' => [['row' => 0, 'arete' => '-', 'messages' => ['El archivo o la hoja de datos está vacía.']]],
            ];
        }

        // Detect column positions by matching header row
        $headerRowIndex = null;
        $colMap = [
            'tipo_animal' => null,
            'raza' => null,
            'arete' => null,
            'nombre' => null,
            'genero' => null,
            'fecha_nacimiento' => null,
            'edad_meses' => null,
            'peso' => null,
            'estado_reproductivo' => null,
            'tipo_alta' => null,
            'precio_compra' => null,
            'fecha_alta' => null,
            'apta_ordeno' => null,
            'observaciones' => null,
        ];

        foreach ($rawRows as $idx => $row) {
            foreach ($row as $colIdx => $cell) {
                $c = mb_strtolower(trim((string) $cell));
                if (str_contains($c, 'tipo de animal') || str_contains($c, 'especie')) {
                    $colMap['tipo_animal'] = $colIdx;
                } elseif (str_contains($c, 'raza')) {
                    $colMap['raza'] = $colIdx;
                } elseif (str_contains($c, 'arete') || str_contains($c, 'código') || str_contains($c, 'codigo')) {
                    $colMap['arete'] = $colIdx;
                } elseif (str_contains($c, 'nombre')) {
                    $colMap['nombre'] = $colIdx;
                } elseif (str_contains($c, 'género') || str_contains($c, 'genero') || str_contains($c, 'sexo')) {
                    $colMap['genero'] = $colIdx;
                } elseif (str_contains($c, 'nacimiento')) {
                    $colMap['fecha_nacimiento'] = $colIdx;
                } elseif (str_contains($c, 'edad')) {
                    $colMap['edad_meses'] = $colIdx;
                } elseif (str_contains($c, 'peso')) {
                    $colMap['peso'] = $colIdx;
                } elseif (str_contains($c, 'reproductivo')) {
                    $colMap['estado_reproductivo'] = $colIdx;
                } elseif (str_contains($c, 'procedencia') || str_contains($c, 'alta')) {
                    $colMap['tipo_alta'] = $colIdx;
                } elseif (str_contains($c, 'precio') || str_contains($c, 'costo')) {
                    $colMap['precio_compra'] = $colIdx;
                } elseif (str_contains($c, 'ingreso') || str_contains($c, 'fecha de alta')) {
                    $colMap['fecha_alta'] = $colIdx;
                } elseif (str_contains($c, 'ordeño') || str_contains($c, 'ordeno')) {
                    $colMap['apta_ordeno'] = $colIdx;
                } elseif (str_contains($c, 'observacion') || str_contains($c, 'nota')) {
                    $colMap['observaciones'] = $colIdx;
                }
            }

            if ($colMap['arete'] !== null || $colMap['tipo_animal'] !== null || $colMap['genero'] !== null) {
                $headerRowIndex = $idx;
                break;
            }
        }

        // Fallback default index map if header not detected
        if ($headerRowIndex === null) {
            $headerRowIndex = 0;
            $colMap = [
                'tipo_animal' => 0,
                'raza' => 1,
                'arete' => 2,
                'nombre' => 3,
                'genero' => 4,
                'fecha_nacimiento' => 5,
                'edad_meses' => 6,
                'peso' => 7,
                'estado_reproductivo' => 8,
                'tipo_alta' => 9,
                'precio_compra' => 10,
                'fecha_alta' => 11,
                'apta_ordeno' => 12,
                'observaciones' => 13,
            ];
        }

        $dataRows = array_slice($rawRows, $headerRowIndex + 1);

        $especies = Especie::where('activo', true)->get()->keyBy(fn ($e) => mb_strtolower(trim($e->nombre)));
        $razas = Raza::where('activo', true)->get()->keyBy(fn ($r) => mb_strtolower(trim($r->nombre)));
        $defaultEspecie = Especie::where('codigo_animal', 'BOV')->first() ?? Especie::first();

        $existingAretes = Animal::where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->pluck('arete')
            ->map(fn ($a) => mb_strtoupper(trim($a)))
            ->flip()
            ->all();

        $seenInFileAretes = [];
        $validRecords = [];
        $validationErrors = [];
        $processedRows = [];

        foreach ($dataRows as $offset => $row) {
            $rowNum = $headerRowIndex + $offset + 2;

            // Check if row is completely empty
            $nonEmptyCells = array_filter($row, fn ($c) => $c !== null && trim((string) $c) !== '');
            if (empty($nonEmptyCells)) {
                continue;
            }

            // Extract values using column map
            $getVal = fn ($key) => $colMap[$key] !== null && isset($row[$colMap[$key]]) ? trim((string) $row[$colMap[$key]]) : '';

            $rawEspecie = $getVal('tipo_animal');
            $rawRaza = $getVal('raza');
            $rawArete = $getVal('arete');
            $rawNombre = $getVal('nombre');
            $rawGenero = $getVal('genero');
            $rawFechaNac = $getVal('fecha_nacimiento');
            $rawEdadMeses = $getVal('edad_meses');
            $rawPeso = $getVal('peso');
            $rawEstadoRepro = $getVal('estado_reproductivo');
            $rawTipoAlta = $getVal('tipo_alta');
            $rawPrecioCompra = $getVal('precio_compra');
            $rawFechaAlta = $getVal('fecha_alta');
            $rawAptaOrdeno = $getVal('apta_ordeno');
            $rawObservaciones = $getVal('observaciones');

            // Skip guide title lines if user imported guide sheet by mistake
            $rowString = mb_strtolower(implode(' ', $row));
            if (str_contains($rowString, 'guía oficial') || str_contains($rowString, 'códigos automáticos') || str_contains($rowString, 'valores válidos')) {
                continue;
            }

            $rowErrors = [];

            // 1. Especie
            $especie = null;
            if ($rawEspecie !== '') {
                $especie = $especies->get(mb_strtolower($rawEspecie));
                if (! $especie) {
                    $rowErrors[] = "Tipo de animal '{$rawEspecie}' no encontrado (válidos: Bovino, Porcino, Ovino, Caprino, Equino, Cuy, Ave, Camélido).";
                }
            } else {
                $especie = $defaultEspecie;
            }

            // 2. Raza
            $razaId = null;
            if ($rawRaza !== '') {
                $raza = $razas->get(mb_strtolower($rawRaza));
                if ($raza) {
                    $razaId = $raza->id;
                } else {
                    // Fallback create or search by first available breed of that species
                    $razaId = $especie ? Raza::where('especie_id', $especie->id)->value('id') : null;
                }
            } elseif ($especie) {
                $razaId = Raza::where('especie_id', $especie->id)->value('id');
            }

            // 3. Género Validation
            $generoNorm = mb_strtolower($rawGenero);
            $genero = self::GENDER_MAP[$generoNorm] ?? null;
            if (! $genero) {
                $rowErrors[] = 'El género debe ser Hembra o Macho.';
            }

            // 4. Fechas y Alta
            $fechaAlta = now()->format('Y-m-d');
            if ($rawFechaAlta !== '') {
                $parsedAlta = $this->parseDate($rawFechaAlta);
                if ($parsedAlta && ! $parsedAlta->isFuture()) {
                    $fechaAlta = $parsedAlta->format('Y-m-d');
                } else {
                    $rowErrors[] = 'Fecha de ingreso no puede ser futura.';
                }
            }

            $fechaNacimiento = null;
            if ($rawFechaNac !== '') {
                $parsedNac = $this->parseDate($rawFechaNac);
                if ($parsedNac && $parsedNac->isPast()) {
                    $fechaNacimiento = $parsedNac->format('Y-m-d');
                } else {
                    $rowErrors[] = 'Fecha de nacimiento inválida o futura (use AAAA-MM-DD).';
                }
            }

            $edadMeses = null;
            if ($rawEdadMeses !== '') {
                if (is_numeric($rawEdadMeses) && (int) $rawEdadMeses >= 0 && (int) $rawEdadMeses <= 360) {
                    $edadMeses = (int) $rawEdadMeses;
                } else {
                    $rowErrors[] = 'Edad estimada debe ser un número entero entre 0 y 360 meses.';
                }
            }

            // 5. Arete / Código Allocation
            $year = $fechaAlta ? Carbon::parse($fechaAlta)->year : now()->year;
            $prefix = $especie?->codigo_animal ?: 'BOV';
            $structuredArete = null;
            $codigoPrefijo = $prefix;
            $codigoAnio = $year;
            $codigoSecuencia = null;

            if ($rawArete === '') {
                // Will be auto-allocated during insertion
                $structuredArete = null;
            } else {
                $areteUpper = mb_strtoupper($rawArete);
                // Check if user provided standard format: PREFIX26-001
                if (preg_match('/^([A-Z]{2,4})(\d{2})-(\d{1,3})$/', $areteUpper, $matches)) {
                    $codigoPrefijo = $matches[1];
                    $codigoAnio = (int) ('20'.$matches[2]);
                    $codigoSecuencia = (int) $matches[3];
                    $structuredArete = AnimalCodeAllocator::format($codigoPrefijo, $codigoAnio, $codigoSecuencia);
                } elseif (is_numeric($rawArete) && (int) $rawArete >= 1 && (int) $rawArete <= 999) {
                    $codigoSecuencia = (int) $rawArete;
                    $structuredArete = AnimalCodeAllocator::format($prefix, $year, $codigoSecuencia);
                } else {
                    $structuredArete = $areteUpper;
                }

                if ($structuredArete !== null) {
                    if (isset($existingAretes[$structuredArete])) {
                        $rowErrors[] = "El arete '{$structuredArete}' ya existe en el fundo.";
                    } elseif (isset($seenInFileAretes[$structuredArete])) {
                        $rowErrors[] = "El arete '{$structuredArete}' está duplicado en la fila {$seenInFileAretes[$structuredArete]}.";
                    } else {
                        $seenInFileAretes[$structuredArete] = $rowNum;
                    }
                }
            }

            // 6. Peso
            $peso = null;
            if ($rawPeso !== '') {
                $pesoClean = str_replace(',', '.', $rawPeso);
                if (is_numeric($pesoClean) && (float) $pesoClean > 0 && (float) $pesoClean <= 2000) {
                    $peso = round((float) $pesoClean, 2);
                } else {
                    $rowErrors[] = 'Peso debe ser un número positivo en kg (ej. 450.5).';
                }
            }

            // 7. Estado Reproductivo
            $estadoReproductivo = null;
            if ($genero === 'hembra') {
                if ($rawEstadoRepro !== '') {
                    $estadoReproKey = self::REPRODUCTIVE_STATE_MAP[mb_strtolower($rawEstadoRepro)] ?? null;
                    if ($estadoReproKey) {
                        $estadoReproductivo = $estadoReproKey;
                    } else {
                        $rowErrors[] = "Estado reproductivo '{$rawEstadoRepro}' no reconocido (opciones: Vacía, Gestante, Lactante, Seca).";
                    }
                } else {
                    $estadoReproductivo = 'vacia';
                }
            }

            // 8. Procedencia / Tipo Alta
            $tipoAlta = 'compra';
            if ($rawTipoAlta !== '') {
                $tipoAlta = self::ADMISSION_TYPE_MAP[mb_strtolower($rawTipoAlta)] ?? 'compra';
            }

            // 9. Precio Compra
            $precioCompra = null;
            if ($rawPrecioCompra !== '') {
                $precioClean = str_replace([',', 'S/', 's/', '$', ' '], ['', '', '', '', ''], $rawPrecioCompra);
                if (is_numeric($precioClean) && (float) $precioClean >= 0) {
                    $precioCompra = round((float) $precioClean, 2);
                }
            }

            // 10. Apta Ordeño
            $aptaOrdeno = false;
            if ($genero === 'hembra' && $prefix === 'BOV') {
                $aptaNorm = mb_strtolower($rawAptaOrdeno);
                $aptaOrdeno = in_array($aptaNorm, ['si', 'sí', '1', 'true', 'yes'], true)
                    || $estadoReproductivo === 'lactante'
                    || $estadoReproductivo === 'en_produccion';
            }

            $recordData = [
                'fundo_id' => $fundoId,
                'especie' => $especie,
                'especie_id' => $especie?->id ?? $defaultEspecie?->id,
                'raza_id' => $razaId,
                'arete' => $structuredArete,
                'codigo_prefijo' => $codigoPrefijo,
                'codigo_anio' => $codigoAnio,
                'codigo_secuencia' => $codigoSecuencia,
                'nombre' => $rawNombre !== '' ? mb_strtoupper($rawNombre) : null,
                'genero' => $genero ?? 'hembra',
                'fecha_nacimiento' => $fechaNacimiento,
                'edad_estimada_meses_alta' => $edadMeses,
                'peso' => $peso,
                'estado_reproductivo' => $estadoReproductivo,
                'tipo_alta' => $tipoAlta,
                'precio_compra' => $precioCompra,
                'fecha_alta' => $fechaAlta,
                'apta_ordeno' => $aptaOrdeno,
                'activo' => true,
                'observaciones' => $rawObservaciones !== '' ? mb_strtoupper($rawObservaciones) : null,
            ];

            if (! empty($rowErrors)) {
                $validationErrors[] = [
                    'row' => $rowNum,
                    'arete' => $structuredArete ?: ($rawNombre ?: "(Fila {$rowNum})"),
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
            $allocator = app(AnimalCodeAllocator::class);

            DB::transaction(function () use ($validRecords, $fundoId, $allocator, &$importedCount) {
                foreach ($validRecords as $item) {
                    $especie = $item['especie'] ?? Especie::find($item['especie_id']);
                    unset($item['especie']);

                    // If arete wasn't specified, allocate automatically
                    if (empty($item['arete'])) {
                        $allocated = $allocator->allocate(
                            new Animal,
                            $fundoId,
                            $especie,
                            $item['codigo_anio'] ?? now()->year,
                            $item['codigo_secuencia']
                        );
                        $item['arete'] = $allocated['arete'];
                        $item['codigo_prefijo'] = $allocated['codigo_prefijo'];
                        $item['codigo_anio'] = $allocated['codigo_anio'];
                        $item['codigo_secuencia'] = $allocated['codigo_secuencia'];
                    }

                    Animal::create($item);
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
