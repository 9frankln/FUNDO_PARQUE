@php
    $sectionWeights = [
        'sanidad' => [
            'fecha' => 10, 'animal' => 8, 'clasificacion' => 15, 'sintomas' => 26,
            'tratamiento' => 29, 'medicamento' => 8, 'dosis' => 8, 'estado' => 12, 'evidencia' => 7,
        ],
        'profilaxis' => [
            'fecha' => 10, 'tipo' => 10, 'producto' => 14, 'animales' => 15, 'dosis' => 6,
            'proxima' => 27, 'responsable' => 10, 'observaciones' => 20, 'evidencia' => 6,
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
<style>
@page { size: A4 landscape; margin: 18pt 20pt 28pt; }
* { box-sizing: border-box; }
body {
    margin: 0;
    color: #243229;
    font-family: DejaVu Sans, sans-serif;
    font-size: 8pt;
    line-height: 1.3;
}
.header { margin-bottom: 7pt; padding-bottom: 6pt; border-bottom: 2px solid #4f7a69; }
.eyebrow { margin: 0 0 2pt; color: #35564b; font-size: 6.5pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
h1 { margin: 0 0 3pt; color: #2f493f; font-size: 16pt; line-height: 1.1; }
.subtitle { margin: 0; color: #52645a; }
.meta-table, .summary-table, .data-table { width: 100%; border-collapse: collapse; }
.meta-table { margin-bottom: 8pt; table-layout: fixed; }
.meta-table td { padding: 4pt 6pt; border: 1px solid #cce5d3; background: #effaf2; vertical-align: top; }
.meta-label { color: #35564b; font-weight: bold; }
.summary-wrap { margin-bottom: 10pt; page-break-inside: avoid; }
.summary-heading { padding: 4pt 6pt; background: #35564b; color: #fff; font-size: 8pt; font-weight: bold; }
.summary-table { table-layout: fixed; }
.summary-table th { padding: 3.5pt 4pt; border: 1px solid #cbd9d0; background: #e6f0e9; color: #35564b; font-size: 6.5pt; text-align: left; text-transform: uppercase; }
.summary-table td { padding: 4pt; border: 1px solid #dce7df; color: #435249; font-size: 7.2pt; vertical-align: top; }
.summary-table .count { text-align: center; }
.summary-section { font-weight: bold; }
.summary-section.sanidad { border-left: 3px solid #a7666f; background: #faeff1; color: #704149; }
.summary-section.profilaxis { border-left: 3px solid #4c7c94; background: #eef6f9; color: #365b6c; }
.summary-section.partos { border-left: 3px solid #b78639; background: #fbf5e9; color: #6f5124; }
.summary-section.alertas { border-left: 3px solid #756a94; background: #f3f1f8; color: #554d70; }
.report-section { margin-top: 10pt; }
.section-title {
    margin: 0 0 4pt;
    padding: 5pt 7pt;
    border-left: 4px solid #a7666f;
    background: #faeff1;
    color: #704149;
    font-size: 10pt;
    line-height: 1.2;
    page-break-after: avoid;
}
.section-title.profilaxis { border-color: #4c7c94; background: #eef6f9; color: #365b6c; }
.section-title.partos { border-color: #b78639; background: #fbf5e9; color: #6f5124; }
.section-title.alertas { border-color: #756a94; background: #f3f1f8; color: #554d70; }
.section-meta { margin: 0 0 5pt; color: #64748b; font-size: 7pt; }
.data-table { table-layout: fixed; }
.data-table thead { display: table-header-group; }
.data-table tr { page-break-inside: avoid; }
.data-table th {
    padding: 4pt 3.5pt;
    border: 1px solid #2f493f;
    background: #35564b;
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
    border: 1px solid #dce7df;
    font-size: 7.5pt;
    line-height: 1.28;
    vertical-align: top;
    word-wrap: break-word;
}
.data-table th:first-child, .data-table td:first-child { white-space: nowrap; }
.data-table.sanidad th { border-color: #704149; background: #80515a; }
.data-table.sanidad td { border-color: #ead9dc; }
.data-table.sanidad tbody tr:nth-child(even) { background: #fcf5f6; }
.data-table.profilaxis th { border-color: #365b6c; background: #466b7c; }
.data-table.profilaxis td { border-color: #d5e4ea; }
.data-table.profilaxis tbody tr:nth-child(even) { background: #f2f8fa; }
.data-table.partos th { border-color: #6f5124; background: #80632f; }
.data-table.partos td { border-color: #eadfc9; }
.data-table.partos tbody tr:nth-child(even) { background: #fcf8ef; }
.data-table.alertas th { border-color: #554d70; background: #655d80; }
.data-table.alertas td { border-color: #e0ddea; }
.data-table.alertas tbody tr:nth-child(even) { background: #f7f5fa; }
.empty { padding: 12pt !important; background: #f8fbf8; color: #64748b; font-style: italic; text-align: center; }
.footer {
    position: fixed;
    right: 0;
    bottom: -21pt;
    left: 0;
    padding-top: 4pt;
    border-top: 1px solid #d6e5d9;
    color: #94a3b8;
    font-size: 6.5pt;
}
.page-number { float: right; }
.page-number::after { content: counter(page); }
</style>
</head>
<body>
<div class="header">
    <x-brand-logo pdf style="float: right; width: 28pt; height: 28pt; color: #35564b; object-fit: contain" />
    <p class="eyebrow">{{ $branding->tagline }} | Monitoreo sanitario y reproductivo</p>
    <h1>{{ $branding->name }} - {{ $title }}</h1>
    <p class="subtitle">Fundo: <strong>{{ $fundo->nombre }}</strong> &nbsp;&middot;&nbsp; {{ $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i') }} (hora Peru)</p>
</div>

<table class="meta-table">
    <tr>
        <td><span class="meta-label">Administrador(es):</span> {{ $administrators }}</td>
        <td><span class="meta-label">Generado por:</span> {{ $generatedBy }}</td>
    </tr>
</table>

<div class="summary-wrap">
    <div class="summary-heading">Resumen del contenido exportado</div>
    <table class="summary-table">
        <thead>
            <tr>
                <th style="width: 23%">Tipo de informacion</th>
                <th style="width: 10%">Registros</th>
                <th style="width: 67%">Campos incluidos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportSections as $section)
                <tr>
                    <td class="summary-section {{ $section['key'] }}">{{ $section['label'] }}</td>
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
    <section class="report-section">
        <h2 class="section-title {{ $section['key'] }}">{{ $sectionIndex + 1 }}. {{ $section['label'] }} &middot; {{ count($section['rows']) }} registro(s)</h2>
        <p class="section-meta"><strong>Filtros:</strong> {{ $section['filterSummary'] }}</p>
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
    </section>
@endforeach

<div class="footer">
    {{ $branding->name }} &middot; Reporte integral de Monitoreo &middot; {{ $generatedAt->format('d/m/Y') }} &middot; {{ $generatedBy }}
    <span class="page-number">Pagina </span>
</div>
</body>
</html>
