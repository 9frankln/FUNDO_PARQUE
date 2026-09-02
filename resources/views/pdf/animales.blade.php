<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Animal | {{ $branding->name }} - {{ $fundo->nombre }}</title>
    @php
        $scale = $scale ?? '85';
        $tableFontSize = match($scale) {
            '40', '45' => '4.5px',
            '50', '55' => '5.0px',
            '65' => '5.6px',
            '75' => '6.4px',
            '85' => '7.2px',
            default => '8.0px',
        };
        $thFontSize = match($scale) {
            '40', '45' => '4.2px',
            '50', '55' => '4.8px',
            '65' => '5.4px',
            '75' => '6.2px',
            '85' => '6.9px',
            default => '7.5px',
        };
        $tableCellPad = match($scale) {
            '40', '45' => '0.8px 1.5px',
            '50', '55' => '1.1px 2px',
            '65' => '1.5px 2.5px',
            '75' => '2px 3px',
            '85' => '2.8px 4px',
            default => '4px 5px',
        };
        $reportSummary = $reportSummary ?? '';
        $filterSummary = $filterSummary ?? '';
    @endphp
    <style>
        @page { size: A4 landscape; margin: 6mm 7mm 12mm 7mm; }
        body {
            margin: 0;
            color: #243229;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5px;
            line-height: 1.15;
        }
        .header {
            margin-bottom: 5px;
            padding-bottom: 4px;
            border-bottom: 3.5px solid {{ $pdfConfig->accentColor() }};
        }
        .eyebrow {
            margin: 0 0 2px;
            color: {{ $pdfConfig->accentColor() }};
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        h1 {
            margin: 0 0 2px;
            color: {{ $pdfConfig->accentDark() }};
            font-size: 16px;
            line-height: 1.1;
            font-weight: 900;
        }
        .subtitle { margin: 0; color: #52645a; font-size: 7.2px; }
        .meta-table, .data-table { width: 100%; border-collapse: collapse; }
        .meta-table { margin-bottom: 5px; }
        .meta-table td {
            padding: 3px 5px;
            border: 1px solid {{ $pdfConfig->accentBorder() }};
            background: {{ $pdfConfig->accentSoft() }};
            vertical-align: top;
            font-size: 7px;
        }
        .meta-label { color: {{ $pdfConfig->accentDark() }}; font-weight: bold; }
        .data-table {
            table-layout: fixed;
            margin-top: 2px;
            font-size: {{ $tableFontSize }};
        }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th {
            padding: {{ $tableCellPad }};
            border: 1px solid {{ $pdfConfig->accentDark() }};
            background: {{ $pdfConfig->accentColor() }};
            color: #fff;
            font-size: {{ $thFontSize }};
            line-height: 1.1;
            text-align: left;
            text-transform: uppercase;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .data-table td {
            padding: {{ $tableCellPad }};
            border: 1px solid {{ $pdfConfig->accentBorder() }};
            line-height: 1.15;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .data-table tbody tr:nth-child(even) { background: {{ $pdfConfig->accentRowEven() }}; }
        .code { font-weight: bold; white-space: nowrap; }
        .center { text-align: center; }
        .number { text-align: right; white-space: nowrap; }
        .date { text-align: center; white-space: nowrap; }
        .col-edad {
            white-space: nowrap !important;
            font-size: 0.90em !important;
            letter-spacing: -0.2px;
        }
        .badge {
            display: inline-block;
            min-width: 28px;
            padding: 1.5px 3px;
            border-radius: 2.5px;
            font-size: 6px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .badge-active { background: {{ $pdfConfig->accentSoft() }}; color: {{ $pdfConfig->accentDark() }}; border: 1px solid {{ $pdfConfig->accentBorder() }}; }
        .badge-inactive { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .empty { padding: 10px !important; color: #64748b; text-align: center; }
    </style>
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.watermark')
    @php
        $columnLabels = [
            'arete' => 'Código',
            'nombre' => 'Nombre',
            'especie' => 'Especie',
            'raza' => 'Raza',
            'genero' => 'Género',
            'edad' => 'Edad',
            'peso' => 'Peso (kg)',
            'estado_reproductivo' => 'Estado Reprod.',
            'tipo_alta' => 'Procedencia',
            'precio_compra' => 'Precio (S/)',
            'activo' => 'Estado',
            'fecha_alta' => 'Fecha Alta',
        ];
        $columns = array_keys(array_intersect_key($columnLabels, array_flip($selectedColumns ?? [])));

        // CÁLCULO 100% DINÁMICO DE ANCHOS DE COLUMNA BASADO EN EL CONTENIDO REAL DE LOS DATOS
        $columnWeights = [];
        foreach ($columns as $column) {
            $headerLen = mb_strlen($columnLabels[$column] ?? '');
            $maxContentLen = 0;

            foreach ($animales as $a) {
                $val = match($column) {
                    'arete' => (string) $a->arete,
                    'nombre' => (string) ($a->nombre ?? ''),
                    'especie' => (string) ($a->especie?->nombre ?? ''),
                    'raza' => (string) ($a->raza?->nombre ?? ''),
                    'genero' => (string) $a->genero,
                    'edad' => (string) $a->edad_texto,
                    'peso' => $a->peso !== null ? number_format((float) $a->peso, 2) : '',
                    'estado_reproductivo' => (string) $a->estado_reproductivo_label,
                    'tipo_alta' => (string) $a->tipo_alta_label,
                    'precio_compra' => $a->precio_compra !== null ? 'S/ '.number_format((float) $a->precio_compra, 2) : '',
                    'activo' => $a->activo ? 'Activo' : 'Inactivo',
                    'fecha_alta' => $a->fecha_alta ? $a->fecha_alta->format('d/m/Y') : '',
                    default => '',
                };
                $len = mb_strlen(trim($val));
                if ($len > $maxContentLen) {
                    $maxContentLen = $len;
                }
            }

            $effectiveLen = max($headerLen, $maxContentLen);

            // Factor de ajuste elástico según tipo de dato (máximo espacio para Nombre y Edad)
            $weight = match($column) {
                'edad' => max(48.0, $effectiveLen * 4.0),
                'nombre' => max(18.0, $effectiveLen * 1.30),
                'raza' => max(3.5, $effectiveLen * 0.35),
                'especie' => max(1.8, $effectiveLen * 0.20),
                'genero' => max(1.8, $effectiveLen * 0.20),
                'peso' => max(1.8, $effectiveLen * 0.20),
                'activo' => max(2.4, $effectiveLen * 0.30),
                'arete' => max(3.5, $effectiveLen * 0.45),
                'fecha_alta' => max(3.2, $effectiveLen * 0.35),
                'precio_compra' => max(3.0, $effectiveLen * 0.35),
                'tipo_alta' => max(4.5, $effectiveLen * 0.45),
                'estado_reproductivo' => max(4.5, $effectiveLen * 0.45),
                default => max(3.5, (float) $effectiveLen * 0.35),
            };

            $columnWeights[$column] = $weight;
        }

        $totalWeight = max(array_sum($columnWeights), 1);
        $density = count($columns) >= 11 ? 'dense' : (count($columns) >= 9 ? 'compact' : '');
        $peruGeneratedAt = $generatedAt
            ? $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i')
            : 'Sin dato';
    @endphp

    <div class="header" style="border-bottom-color: {{ $pdfConfig->accentColor() }};">
        @if($pdfConfig->showHeaderLogo())
            <x-brand-logo pdf style="float: right; width: 24px; height: 24px; color: {{ $pdfConfig->accentColor() }}; object-fit: contain" />
        @endif
        <p class="eyebrow" style="color: {{ $pdfConfig->accentColor() }};">{{ $branding->tagline }} | INVENTARIO ANIMAL</p>
        <h1 style="color: {{ $pdfConfig->accentDark() }};">{{ $branding->name }} &mdash; Reporte de Inventario Animal</h1>
        <p class="subtitle">
            Fundo: <strong>{{ $fundo->nombre }}</strong> &bull;
            Generado el {{ $peruGeneratedAt }} (hora de Perú)
        </p>
    </div>

    <div class="summary-card" style="border: 1px solid {{ $pdfConfig->accentBorder() }}; border-radius: {{ $pdfConfig->tableBorderRadius() }}; overflow: hidden; background-color: {{ $pdfConfig->accentSoft() }}; margin-bottom: 9pt;">
        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; background: transparent;">
            <tr>
                <td style="width: 50%; border: none; border-right: 1px solid {{ $pdfConfig->accentBorder() }}; border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Generado por:</strong> {{ $generatedBy ?: 'Sin dato' }}</td>
                <td style="width: 50%; border: none; border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Usuario / Documento:</strong> &#64;{{ auth()->user()?->username ?: 'admin' }} | DNI: {{ auth()->user()?->dni ?: '00000000' }}</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; @if(!empty($filterSummary) && $filterSummary !== 'Sin filtros adicionales') border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; @endif color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Resumen:</strong> {{ $reportSummary ?: 'Sin resumen' }}</td>
            </tr>
            @if(!empty($filterSummary) && $filterSummary !== 'Sin filtros adicionales')
                <tr>
                    <td colspan="2" style="border: none; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Filtros aplicados:</strong> {{ $filterSummary }}</td>
                </tr>
            @endif
        </table>
    </div>

    <table class="data-table {{ $density }}">
        <colgroup>
            @foreach($columns as $column)
                <col style="width: {{ round(($columnWeights[$column] / $totalWeight) * 100, 2) }}%;">
            @endforeach
        </colgroup>
        <thead>
            <tr>
                @forelse($columns as $column)
                    <th>{{ $columnLabels[$column] }}</th>
                @empty
                    <th>Registros</th>
                @endforelse
            </tr>
        </thead>
        <tbody>
            @forelse($animales as $animal)
                <tr>
                    @forelse($columns as $column)
                        @switch($column)
                            @case('arete')
                                <td class="code nowrap">{{ $animal->arete }}</td>
                                @break
                            @case('nombre')
                                <td style="font-weight: bold; color: #0f172a;">{{ $animal->nombre ?: '-' }}</td>
                                @break
                            @case('especie')
                                <td class="nowrap" style="white-space: nowrap !important;">{{ $animal->especie?->nombre ?: '-' }}</td>
                                @break
                            @case('raza')
                                <td>{{ $animal->raza?->nombre ?: '-' }}</td>
                                @break
                            @case('genero')
                                <td class="center nowrap" style="white-space: nowrap !important;">{{ ucfirst($animal->genero) }}</td>
                                @break
                            @case('edad')
                                <td class="col-edad">{{ $animal->edad_texto }}</td>
                                @break
                            @case('peso')
                                <td class="number nowrap" style="white-space: nowrap !important;">{{ $animal->peso !== null ? number_format((float) $animal->peso, 2) : '-' }}</td>
                                @break
                            @case('estado_reproductivo')
                                <td>{{ $animal->estado_reproductivo_label }}</td>
                                @break
                            @case('tipo_alta')
                                <td class="nowrap">{{ $animal->tipo_alta_label }}</td>
                                @break
                            @case('precio_compra')
                                <td class="number nowrap">{{ $animal->precio_compra !== null ? 'S/ '.number_format((float) $animal->precio_compra, 2) : '-' }}</td>
                                @break
                            @case('activo')
                                <td class="center nowrap"><span class="badge {{ $animal->activo ? 'badge-active' : 'badge-inactive' }}">{{ $animal->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                @break
                            @case('fecha_alta')
                                <td class="date nowrap">{{ $animal->fecha_alta?->format('d/m/Y') ?? '-' }}</td>
                                @break
                        @endswitch
                    @empty
                        <td class="empty">Sin columnas seleccionadas</td>
                    @endforelse
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ max(count($columns), 1) }}">No se encontraron animales para los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($includeSignatures ?? true)
        @include('pdf.partials.signatures')
    @endif
    @include('pdf.partials.footer')
</body>
</html>
