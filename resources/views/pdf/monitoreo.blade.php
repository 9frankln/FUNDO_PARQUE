@php
    $sectionWeights = [
        'sanidad' => [
            'fecha' => 9, 'animal' => 10, 'categoria' => 13, 'subtipo' => 12, 'hallazgo' => 24,
            'atencion' => 24, 'dosis' => 22, 'estado' => 12, 'evidencia' => 7,
        ],
        'partos' => [
            'fecha' => 7, 'madre' => 9, 'condicion' => 10, 'tipo' => 8, 'cria' => 21,
            'sexo' => 7, 'estado_cria' => 10, 'peso' => 7, 'observaciones' => 24,
        ],
        'alertas' => ['fecha' => 8, 'animal' => 10, 'tipo' => 12, 'mensaje' => 56, 'estado' => 10],
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{{ $title }} | {{ $branding->name }}</title>
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
@endphp
<style>
@page { size: A4 landscape; margin: 14mm 10mm 16mm 10mm; }
* { box-sizing: border-box; }
body {
    margin: 0;
    color: #243229;
    font-family: DejaVu Sans, sans-serif;
    font-size: 8pt;
    line-height: 1.3;
}
.header { margin-bottom: 6px; padding-bottom: 5px; border-bottom: 3.5px solid {{ $pdfConfig->accentColor() }}; }
.eyebrow { margin: 0 0 2px; color: {{ $pdfConfig->accentColor() }}; font-size: 7px; font-weight: bold; letter-spacing: 0.8px; text-transform: uppercase; }
h1 { margin: 0 0 2px; color: {{ $pdfConfig->accentDark() }}; font-size: 16px; line-height: 1.1; font-weight: 900; }
.subtitle { margin: 0; color: #52645a; font-size: 7.2px; }
.meta-table, .summary-table, .data-table { width: 100%; border-collapse: collapse; }
.meta-table { margin-bottom: 6px; table-layout: fixed; }
.meta-table td { padding: 3px 5px; border: 1px solid {{ $pdfConfig->accentBorder() }}; background: {{ $pdfConfig->accentSoft() }}; vertical-align: top; font-size: 7px; }
.meta-label { color: {{ $pdfConfig->accentDark() }}; font-weight: bold; }
.summary-wrap { margin-bottom: 8px; page-break-inside: avoid; }
.summary-heading { padding: 3px 6px; background: {{ $pdfConfig->accentColor() }}; color: #fff; font-size: 7.5px; font-weight: bold; }
.summary-table { table-layout: fixed; margin-top: 0; }
.summary-table th { padding: {{ $tableCellPad }}; border: 1px solid {{ $pdfConfig->accentDark() }}; background: {{ $pdfConfig->accentSoft() }}; color: {{ $pdfConfig->accentDark() }}; font-size: {{ $thFontSize }}; text-align: left; text-transform: uppercase; }
.summary-table td { padding: {{ $tableCellPad }}; border: 1px solid {{ $pdfConfig->accentBorder() }}; color: #334155; font-size: {{ $tableFontSize }}; vertical-align: top; }
.summary-table .count { text-align: center; font-weight: bold; }
.summary-section { font-weight: bold; }
.report-section { margin-top: 8px; margin-bottom: 6px; page-break-inside: auto; }
.section-title {
    margin: 0 0 2px;
    padding: 3.5px 6px;
    border-left: 4px solid {{ $pdfConfig->accentColor() }};
    background: {{ $pdfConfig->accentSoft() }};
    color: {{ $pdfConfig->accentDark() }};
    font-size: 8.5px;
    line-height: 1.2;
    font-weight: bold;
    page-break-after: avoid;
}
.section-meta { margin: 0 0 3px; color: #64748b; font-size: 6.8px; }
.data-table { table-layout: fixed; margin-top: 2px; }
.data-table thead { display: table-header-group; }
.data-table tr { page-break-inside: avoid; }
.data-table th {
    padding: 4pt 3.5pt;
    border: 1px solid {{ $pdfConfig->accentDark() }};
    background: {{ $pdfConfig->accentColor() }};
    color: #fff;
    font-size: 6.8pt;
    line-height: 1.2;
    text-align: left;
    text-transform: uppercase;
    vertical-align: middle;
    word-wrap: break-word;
}
.data-table td {
    padding: 4pt 3.5pt;
    border: 1px solid {{ $pdfConfig->accentBorder() }};
    font-size: 7.5pt;
    line-height: 1.28;
    vertical-align: top;
    word-wrap: break-word;
}
.data-table tbody tr:nth-child(even) { background-color: {{ $pdfConfig->accentRowEven() }}; }
.empty { padding: 10px !important; background: #f8fbf8; color: #64748b; font-style: italic; text-align: center; }
</style>
@include('pdf.partials.styles')
</head>
<body>
@include('pdf.partials.watermark')
<div class="header">
    @if($pdfConfig->showHeaderLogo())
        <x-brand-logo pdf style="float: right; width: 26px; height: 26px; color: {{ $pdfConfig->accentColor() }}; object-fit: contain" />
    @endif
    <p class="eyebrow">{{ $branding->tagline }} | MONITOREO SANITARIO Y REPRODUCTIVO</p>
    <h1>{{ $branding->name }} &mdash; {{ $title }}</h1>
    <p class="subtitle">Fundo: <strong>{{ $fundo->nombre }}</strong> &bull; {{ $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i') }} (hora Perú)</p>
</div>

<div class="summary-card" style="border: 1px solid {{ $pdfConfig->accentBorder() }}; border-radius: {{ $pdfConfig->tableBorderRadius() }}; overflow: hidden; background-color: {{ $pdfConfig->accentSoft() }}; margin-bottom: 7pt;">
    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; background: transparent;">
        <tr>
            <td style="width: 50%; border: none; border-right: 1px solid {{ $pdfConfig->accentBorder() }}; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Generado por:</strong> {{ $generatedBy }}</td>
            <td style="width: 50%; border: none; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Usuario / Documento:</strong> {{ auth()->user()?->name ?? 'Sistema' }} ({{ auth()->user()?->dni ? 'DNI: '.auth()->user()->dni : (auth()->user()?->username ? '@'.auth()->user()->username : 'Sistema') }})</td>
        </tr>
    </table>
</div>

<div class="summary-wrap">
    <div class="summary-heading" style="border-radius: {{ $pdfConfig->tableBorderRadius() }} {{ $pdfConfig->tableBorderRadius() }} 0 0;">Resumen del contenido exportado</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 23%">Tipo de información</th>
                <th style="width: 10%; text-align: center;">Registros</th>
                <th style="width: 67%">Campos incluidos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportSections as $section)
                <tr>
                    <td class="summary-section">{{ $section['label'] }}</td>
                    <td class="count">{{ count($section['rows']) }}</td>
                    <td>
                        @foreach($section['columns'] as $column)
                            {{ $section['columnLabels'][$column] ?? $column }}@if(! $loop->last), @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@foreach($reportSections as $sectionIndex => $section)
    @php
        $weights = $sectionWeights[$section['key']] ?? [];
        $selectedWeights = array_intersect_key($weights, array_flip($section['columns']));
        $totalWeight = max(array_sum($selectedWeights), 1);
        $columnWidths = [];

        foreach ($section['columns'] as $column) {
            $columnWidths[$column] = round((($weights[$column] ?? 10) / $totalWeight) * 100, 2);
        }
    @endphp
    <div class="report-section">
        <h2 class="section-title {{ $section['key'] }}">
            {{ $sectionIndex + 1 }}. {{ $section['label'] }} &middot; {{ count($section['rows']) }} registro(s)
        </h2>
        @if(!empty($section['filterSummary']))
            <p class="section-meta"><strong>Filtros:</strong> {{ $section['filterSummary'] }}</p>
        @endif
        <table class="data-table {{ $section['key'] }}">
            <thead>
                <tr>
                    @foreach($section['columns'] as $column)
                        <th width="{{ $columnWidths[$column] }}%" style="width: {{ $columnWidths[$column] }}%">{{ $section['columnLabels'][$column] ?? $column }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($section['rows'] as $row)
                    <tr>
                        @foreach($section['columns'] as $column)
                            <td width="{{ $columnWidths[$column] }}%" style="width: {{ $columnWidths[$column] }}%">{{ $row[$column] ?? '-' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ max(count($section['columns']), 1) }}" class="empty">Sin registros para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endforeach

@include('pdf.partials.signatures')
@include('pdf.partials.footer')
</body>
</html>

