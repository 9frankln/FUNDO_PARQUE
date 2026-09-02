<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lotes de engorde | {{ $branding->name }} - {{ $fundo->nombre ?? 'Fundo' }}</title>
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
        @page { size: A4 landscape; margin: 8mm 8mm 12mm 8mm; }
        body {
            margin: 0;
            color: #243229;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.5px;
            line-height: 1.15;
        }
        .header {
            margin-bottom: 6px;
            padding-bottom: 5px;
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
        .meta-table { margin-bottom: 6px; }
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
            margin-top: 3px;
            font-size: {{ $tableFontSize }};
        }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th {
            padding: {{ $tableCellPad }};
            border: 1px solid {{ $pdfConfig->accentDark() }};
            background-color: {{ $pdfConfig->accentColor() }};
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
        .data-table tbody tr:nth-child(even) { background-color: {{ $pdfConfig->accentRowEven() }}; }
        .center { text-align: center; white-space: nowrap; }
        .number { text-align: right; white-space: nowrap; }
        .status {
            display: inline-block;
            min-width: 38px;
            padding: 1.5px 4px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .status-active {
            background-color: {{ $pdfConfig->accentSoft() }};
            color: {{ $pdfConfig->accentDark() }};
            border: 1px solid {{ $pdfConfig->accentBorder() }};
        }
        .status-closed {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .empty { padding: 12px !important; color: #64748b; text-align: center; }
    </style>
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.watermark')
    @php
        $columnLabels = [
            'codigo' => 'Código',
            'nombre' => 'Nombre del lote',
            'fecha_inicio' => 'Fecha de inicio',
            'fecha_fin' => 'Fecha de fin',
            'animales' => 'Animales',
            'estado' => 'Estado',
            'observaciones' => 'Observaciones',
        ];
        $columnWeights = [
            'codigo' => 14,
            'nombre' => 28,
            'fecha_inicio' => 14,
            'fecha_fin' => 14,
            'animales' => 12,
            'estado' => 14,
            'observaciones' => 24,
        ];
        $columns = array_keys(array_intersect_key($columnLabels, array_flip($selectedColumns ?? [])));
        $totalWeight = max(array_sum(array_intersect_key($columnWeights, array_flip($columns))), 1);
        $peruGeneratedAt = $generatedAt
            ? $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i')
            : 'Sin dato';
    @endphp

    <div class="header">
        @if($pdfConfig->showHeaderLogo())
            <x-brand-logo pdf style="float: right; width: 26px; height: 26px; color: {{ $pdfConfig->accentColor() }}; object-fit: contain" />
        @endif
        <p class="eyebrow">{{ $branding->tagline }} | CONTROL DE ENGORDE</p>
        <h1>{{ $branding->name }} &mdash; Reporte de Lotes de Engorde</h1>
        <p class="subtitle">
            Fundo: <strong>{{ $fundo->nombre ?? 'Sin dato' }}</strong> &bull;
            Generado el {{ $peruGeneratedAt }} (hora de Perú)
        </p>
    </div>

    <div class="summary-card" style="border: 1px solid {{ $pdfConfig->accentBorder() }}; border-radius: {{ $pdfConfig->tableBorderRadius() }}; overflow: hidden; background-color: {{ $pdfConfig->accentSoft() }}; margin-bottom: 7pt;">
        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; background: transparent;">
            <tr>
                <td style="width: 50%; border: none; border-right: 1px solid {{ $pdfConfig->accentBorder() }}; border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; color: #1e293b;">
                    <strong style="color: {{ $pdfConfig->accentDark() }};">Generado por:</strong> {{ $generatedBy ?: 'Sin dato' }}
                </td>
                <td style="width: 50%; border: none; border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; color: #1e293b;">
                    <strong style="color: {{ $pdfConfig->accentDark() }};">Usuario / Documento:</strong> {{ auth()->user()?->name ?? 'Sistema' }} ({{ auth()->user()?->dni ? 'DNI: '.auth()->user()->dni : (auth()->user()?->username ? '@'.auth()->user()->username : 'Sistema') }})
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; @if(!empty($filterSummary) && $filterSummary !== 'Sin filtros adicionales') border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; @endif color: #1e293b;">
                    <strong style="color: {{ $pdfConfig->accentDark() }};">Resumen:</strong> {{ $reportSummary ?: 'Sin resumen' }}
                </td>
            </tr>
            @if(!empty($filterSummary) && $filterSummary !== 'Sin filtros adicionales')
            <tr>
                <td colspan="2" style="border: none; color: #1e293b;">
                    <strong style="color: {{ $pdfConfig->accentDark() }};">Filtros aplicados:</strong> {{ $filterSummary }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <table class="data-table">
        <colgroup>
            @foreach($columns as $column)
                <col style="width: {{ round((($columnWeights[$column] ?? 14) / $totalWeight) * 100, 2) }}%;">
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
        @forelse($lotes as $lote)
            <tr>
                @forelse($columns as $column)
                    @switch($column)
                        @case('codigo')
                            <td style="font-weight: bold;">{{ $lote->codigo }}</td>
                            @break
                        @case('nombre')
                            <td>{{ $lote->nombre ?: 'Sin nombre' }}</td>
                            @break
                        @case('fecha_inicio')
                            <td class="center">{{ $lote->fecha_inicio?->format('d/m/Y') ?? 'Sin dato' }}</td>
                            @break
                        @case('fecha_fin')
                            <td class="center">{{ $lote->fecha_fin?->format('d/m/Y') ?? 'En curso' }}</td>
                            @break
                        @case('animales')
                            <td class="number">{{ (int) $lote->animales_count }}</td>
                            @break
                        @case('estado')
                            <td class="center">
                                <span class="status {{ $lote->estado === 'cerrado' ? 'status-closed' : 'status-active' }}">
                                    {{ ucfirst($lote->estado) }}
                                </span>
                            </td>
                            @break
                        @case('observaciones')
                            <td>{{ $lote->observaciones ?: 'Sin observaciones' }}</td>
                            @break
                    @endswitch
                @empty
                    <td class="empty">Sin columnas seleccionadas</td>
                @endforelse
            </tr>
        @empty
            <tr>
                <td class="empty" colspan="{{ max(count($columns), 1) }}">No se encontraron lotes para los filtros aplicados.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    @include('pdf.partials.signatures')
    @include('pdf.partials.footer')
</body>
</html>

