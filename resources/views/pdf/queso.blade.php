@php
    $dailyWeights = [
        'date' => 8,
        'photo' => 8,
        'units' => 10,
        'presentations' => 23,
        'weight' => 10,
        'observations' => 28,
        'registered_at' => 13,
    ];
    $weeklyWeights = [
        'week' => 10,
        'period' => 19,
        'days' => 10,
        'units' => 13,
        'weight' => 12,
        'average_units' => 18,
        'average_weight' => 18,
    ];
    $monthlyWeights = [
        'month' => 14,
        'records' => 12,
        'days' => 12,
        'units' => 14,
        'weight' => 13,
        'average_units' => 18,
        'average_weight' => 17,
    ];
    $annualWeights = [
        'year' => 8,
        'months' => 11,
        'records' => 12,
        'days' => 11,
        'units' => 13,
        'weight' => 12,
        'average_units' => 17,
        'average_weight' => 16,
    ];
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de producción de queso | {{ $branding->name }}</title>
<style>
@page { size: A4 landscape; margin: 18pt 20pt 28pt; }
* { box-sizing: border-box; }
body {
    margin: 0;
    color: #26342c;
    font-family: DejaVu Sans, sans-serif;
    font-size: 8pt;
    line-height: 1.3;
}
.header { margin-bottom: 7pt; padding-bottom: 6pt; border-bottom: 2px solid #3f7662; }
.eyebrow { margin: 0 0 2pt; color: #3f7662; font-size: 6.5pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
h1 { margin: 0 0 3pt; color: #294c3f; font-size: 16pt; line-height: 1.1; }
.subtitle { margin: 0; color: #5c6d63; }
.meta-table, .context-table, .summary-grid, .data-table { width: 100%; border-collapse: collapse; }
.meta-table { margin-bottom: 7pt; table-layout: fixed; }
.meta-table td { padding: 4pt 6pt; border: 1px solid #c9dfd2; background: #f0f8f3; vertical-align: top; }
.meta-label { color: #315c4c; font-weight: bold; }
.context-title { padding: 4pt 6pt; background: #315c4c; color: #fff; font-size: 8pt; font-weight: bold; }
.context-table { margin-bottom: 10pt; table-layout: fixed; }
.context-table td { padding: 5pt 6pt; border: 1px solid #d7e5dc; vertical-align: top; }
.context-table td:first-child { width: 58%; background: #f6faf7; }
.context-table td:last-child { width: 42%; background: #fbfcfb; }
.context-label { display: block; margin-bottom: 2pt; color: #587064; font-size: 6.2pt; font-weight: bold; text-transform: uppercase; }
.report-section { page-break-before: always; }
.report-section.first { page-break-before: auto; }
.section-heading { margin: 0 0 7pt; padding: 6pt 8pt; border-left: 4px solid #3f8a6b; background: #edf8f2; color: #285a47; font-size: 11pt; page-break-after: avoid; }
.section-heading.daily { border-color: #b9812f; background: #fcf5e8; color: #6c4a1d; }
.section-heading.weekly { border-color: #4383a2; background: #edf6fa; color: #315f75; }
.section-heading.monthly { border-color: #7661a8; background: #f3f0fa; color: #564679; }
.section-heading.annual { border-color: #a25b68; background: #faeff1; color: #743f49; }
.section-note { display: block; margin-top: 2pt; color: #6d7b73; font-size: 6.5pt; font-weight: normal; }
.summary-grid { margin-bottom: 6pt; table-layout: fixed; }
.summary-grid td { width: 25%; padding: 3pt; vertical-align: top; }
.summary-card { min-height: 42pt; padding: 7pt; border: 1px solid #cce3d5; background: #f3faf6; }
.summary-card-label { display: block; margin-bottom: 4pt; color: #527061; font-size: 6.4pt; font-weight: bold; text-transform: uppercase; }
.summary-card-value { color: #244d3c; font-size: 12pt; font-weight: bold; line-height: 1.15; }
.data-table { table-layout: fixed; }
.data-table thead { display: table-header-group; }
.data-table tbody tr { page-break-inside: avoid; }
.data-table th {
    padding: 4pt 3.5pt;
    border: 1px solid #704f22;
    background: #80612f;
    color: #fff;
    font-size: 6.7pt;
    line-height: 1.15;
    text-align: left;
    text-transform: uppercase;
    vertical-align: middle;
    word-wrap: break-word;
}
.data-table td {
    padding: 4pt 3.5pt;
    border: 1px solid #e8dcc6;
    font-size: 7.3pt;
    line-height: 1.25;
    vertical-align: top;
    word-wrap: break-word;
}
.data-table.daily tbody tr:nth-child(even) { background: #fdf8ef; }
.data-table.weekly th { border-color: #315f75; background: #41768f; }
.data-table.weekly td { border-color: #d4e4eb; }
.data-table.weekly tbody tr:nth-child(even) { background: #f1f8fb; }
.data-table.monthly th { border-color: #564679; background: #6b5a91; }
.data-table.monthly td { border-color: #dfd9ec; }
.data-table.monthly tbody tr:nth-child(even) { background: #f7f5fb; }
.data-table.annual th { border-color: #743f49; background: #87515b; }
.data-table.annual td { border-color: #ead8dc; }
.data-table.annual tbody tr:nth-child(even) { background: #fcf4f6; }
.data-table.dense th { padding: 3.2pt 2.8pt; font-size: 6.2pt; }
.data-table.dense td { padding: 3.2pt 2.8pt; font-size: 6.8pt; line-height: 1.2; }
.nowrap { white-space: nowrap; }
.photo { display: block; width: 42pt; height: 32pt; border: 1px solid #d6c8ae; object-fit: cover; }
.no-photo { color: #8a8174; font-size: 6.5pt; font-style: italic; }
.empty { padding: 12pt !important; color: #718078; font-style: italic; text-align: center; }
.footer {
    position: fixed;
    right: 0;
    bottom: -21pt;
    left: 0;
    padding-top: 4pt;
    border-top: 1px solid #d6e5d9;
    color: #89998f;
    font-size: 6.5pt;
}
.page-number { float: right; }
.page-number::after { content: counter(page); }
</style>
</head>
<body>
<div class="header">
    <x-brand-logo pdf style="float: right; width: 28pt; height: 28pt; color: #3f7662; object-fit: contain" />
    <p class="eyebrow">{{ $branding->tagline }} | Producción y transformación láctea</p>
    <h1>{{ $branding->name }} - Reporte de Producción de Queso</h1>
    <p class="subtitle">Fundo: <strong>{{ $fundo->nombre }}</strong> &nbsp;&middot;&nbsp; Generado el {{ $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i') }} (hora Perú)</p>
</div>

<table class="meta-table">
    <tr>
        <td><span class="meta-label">Administrador(es):</span> {{ $administrators }}</td>
        <td><span class="meta-label">Generado por:</span> {{ $generatedBy }}</td>
    </tr>
</table>

<div class="context-title">Alcance del reporte</div>
<table class="context-table">
    <tr>
        <td style="width: 100%; background: #f6faf7; padding: 4px 6px;">
            <span class="context-label">Contenido seleccionado</span>
            {{ $reportSummary }}
        </td>
    </tr>
</table>

@php
    $renderedSection = 0;
@endphp
@if(in_array('summary', $selectedSections, true))
    @php
        $summaryFields = $selectedColumns['summary'] ?? [];
        $summaryRows = array_chunk($summaryFields, 4);
        $renderedSection++;
    @endphp
    <section class="report-section first">
        <h2 class="section-heading">
            Resumen productivo
            <span class="section-note">Indicadores calculados exclusivamente con los registros que cumplen los filtros aplicados.</span>
        </h2>
        @foreach($summaryRows as $row)
            <table class="summary-grid">
                <tr>
                    @foreach($row as $field)
                        @php
                            // Dynamic theme based on field to match dashboard styling
                            $cardBorder = '#cce3d5';
                            $cardBg = '#f3faf6';
                            $cardLabelColor = '#527061';
                            $cardValColor = '#244d3c';

                            if (in_array($field, ['period', 'weight'], true)) {
                                // Green / Emerald theme
                                $cardBorder = '#a7f3d0';
                                $cardBg = '#f0fdf4';
                                $cardLabelColor = '#047857';
                                $cardValColor = '#065f46';
                            } elseif (in_array($field, ['records', 'days', 'units'], true)) {
                                // Sky blue theme
                                $cardBorder = '#bae6fd';
                                $cardBg = '#f0f9ff';
                                $cardLabelColor = '#0369a1';
                                $cardValColor = '#075985';
                            } elseif (in_array($field, ['average_units', 'average_weight'], true)) {
                                // Violet theme
                                $cardBorder = '#ddd6fe';
                                $cardBg = '#f5f3ff';
                                $cardLabelColor = '#6d28d9';
                                $cardValColor = '#5b21b6';
                            } elseif ($field === 'last_production') {
                                // Amber / Orange theme
                                $cardBorder = '#fde68a';
                                $cardBg = '#fffbeb';
                                $cardLabelColor = '#b45309';
                                $cardValColor = '#92400e';
                            }
                        @endphp
                        <td>
                            <div class="summary-card" style="border: 1px solid {{ $cardBorder }}; background: {{ $cardBg }}; border-radius: 8px; min-height: 42pt; padding: 7pt;">
                                <span class="summary-card-label" style="color: {{ $cardLabelColor }}; display: block; margin-bottom: 4px; font-size: 6.4pt; font-weight: bold; text-transform: uppercase;">{{ $columnOptions['summary'][$field] }}</span>
                                <span class="summary-card-value" style="color: {{ $cardValColor }}; font-size: 12pt; font-weight: bold; line-height: 1.15;">{{ $summary[$field] ?? '-' }}</span>
                            </div>
                        </td>
                    @endforeach
                    @for($emptyCell = count($row); $emptyCell < 4; $emptyCell++)
                        <td></td>
                    @endfor
                </tr>
            </table>
        @endforeach

        <!-- Dashboard Visual Charts -->
        @php
            // Calculate monthly history (up to last 12 active months)
            $chartMonths = $monthlySummaries->take(12)->reverse()->values();
            $numMonths = $chartMonths->count();
            $maxWeight = max($chartMonths->pluck('total_peso')->max() ?: 1, 1);

            $barData = [];
            if ($numMonths > 0) {
                foreach ($chartMonths as $m) {
                    $w = (float) $m->total_peso;
                    // Max height of the bar is 55px, min is 2px
                    $pct = max(round(($w / $maxWeight) * 55), 2);
                    $barData[] = [
                        'weight' => $w,
                        'height' => $pct,
                        'monthLabel' => substr($m->mes_nombre, 0, 3),
                        'yearLabel' => substr($m->anio, 2)
                    ];
                }
            }

            // Calculate presentations mix from productions
            $presentationTotals = [];
            foreach ($productions as $p) {
                foreach ($p->presentaciones as $pres) {
                    $weight = $pres->peso_gramos;
                    $presentationTotals[$weight] = ($presentationTotals[$weight] ?? 0) + $pres->cantidad;
                }
            }
            arsort($presentationTotals);
            $totalPresentationsQty = array_sum($presentationTotals) ?: 1;
            $topPresentations = array_slice($presentationTotals, 0, 5, true);
            
            $presentationColors = ['#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444'];
            $colorIndex = 0;
        @endphp

        <table style="width: 100%; margin-top: 15px; border-collapse: collapse; table-layout: fixed;">
            <tr>
                <td style="width: 50%; padding-right: 12px; vertical-align: top;">
                    <div style="border: 1px solid #a7f3d0; background: #f0fdf4; padding: 12px; border-radius: 12px; height: 110pt;">
                        <strong style="color: #065f46; font-size: 8.5pt; display: block; margin-bottom: 6px; border-bottom: 1px solid #d1fae5; padding-bottom: 4px;">Evolución de Producción Mensual (Kg)</strong>
                        
                        @if(count($barData) > 0)
                            <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 8px;">
                                <tr>
                                    @foreach($barData as $bar)
                                        <td style="text-align: center; vertical-align: bottom; padding: 0 2px;">
                                            <div style="font-size: 5.8pt; color: #065f46; font-weight: bold; margin-bottom: 3px;">
                                                {{ number_format($bar['weight'], 0, ',', '.') }}
                                            </div>
                                            <!-- The vertical bar -->
                                            <div style="background: #10b981; width: 14px; height: {{ $bar['height'] }}px; border-radius: 3px 3px 0 0; margin: 0 auto;"></div>
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td colspan="{{ count($barData) }}" style="padding: 4px 0 2px;">
                                        <div style="border-top: 1px dashed #a7f3d0; height: 1px; width: 100%;"></div>
                                    </td>
                                </tr>
                                <tr>
                                    @foreach($barData as $bar)
                                        <td style="text-align: center; font-size: 6pt; color: #527061; font-weight: bold; overflow: hidden; white-space: nowrap;">
                                            {{ $bar['monthLabel'] }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach($barData as $bar)
                                        <td style="text-align: center; font-size: 5pt; color: #86a393; font-weight: normal; overflow: hidden; white-space: nowrap; padding-top: 1px;">
                                            '{{ $bar['yearLabel'] }}
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        @else
                            <div style="text-align: center; color: #047857; font-size: 8pt; padding: 25px 0; font-style: italic;">Sin datos de producción mensual.</div>
                        @endif
                    </div>
                </td>
                <td style="width: 50%; padding-left: 12px; vertical-align: top;">
                    <div style="border: 1px solid #bae6fd; background: #f0f9ff; padding: 12px; border-radius: 12px; height: 110pt;">
                        <strong style="color: #075985; font-size: 8.5pt; display: block; margin-bottom: 8px; border-bottom: 1px solid #e0f2fe; padding-bottom: 4px;">Mezcla de Presentaciones</strong>
                        <table style="width: 100%; border-collapse: collapse;">
                            @forelse($topPresentations as $weight => $qty)
                                @php
                                    $pct = round(($qty / $totalPresentationsQty) * 100);
                                    
                                    // Map color & label
                                    $color = '#8b5cf6'; // default violet
                                    if ((int)$weight === 500) { $color = '#10b981'; } // emerald
                                    elseif ((int)$weight === 1000) { $color = '#0ea5e9'; } // sky blue
                                    
                                    $textColor = '#5b21b6'; // default violet dark
                                    if ((int)$weight === 500) { $textColor = '#0f766e'; } // emerald dark
                                    elseif ((int)$weight === 1000) { $textColor = '#0369a1'; } // sky blue dark

                                    $label = \App\Models\ProduccionQuesoPresentacion::pesoLabel($weight);
                                @endphp
                                <tr style="height: 22px;">
                                    <td style="width: 32%; font-size: 7.5pt; color: {{ $textColor }}; font-weight: bold; padding: 2px 0;">{{ $label }}</td>
                                    <td style="width: 48%; padding: 2px 0;">
                                        <div style="background: #e0f2fe; height: 10px; border-radius: 5px; overflow: hidden; width: 100%;">
                                            <div style="background: {{ $color }}; height: 100%; width: {{ $pct }}%; border-radius: 5px;"></div>
                                        </div>
                                    </td>
                                    <td style="width: 20%; text-align: right; font-size: 7.5pt; font-weight: bold; color: #0f172a; padding: 2px 0;">{{ $qty }} moldes <span style="font-size: 6.5pt; color: #64748b; font-weight: normal;">({{ $pct }}%)</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td style="text-align: center; color: #075985; font-size: 8pt; padding: 20px 0; font-style: italic;">Sin desglose de presentaciones.</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </section>
@endif

@if(in_array('daily', $selectedSections, true))
    @php
        $dailyColumns = $selectedColumns['daily'] ?? [];
        $dailyTotalWeight = max(array_sum(array_intersect_key($dailyWeights, array_flip($dailyColumns))), 1);
        $dailyWidths = [];
        foreach ($dailyColumns as $column) {
            $dailyWidths[$column] = round((($dailyWeights[$column] ?? 10) / $dailyTotalWeight) * 100, 2);
        }
        $renderedSection++;
    @endphp
    <section class="report-section {{ $renderedSection === 1 ? 'first' : '' }}">
        <h2 class="section-heading daily">
            Elaboraciones registradas · {{ $productions->count() }} registro(s)
            <span class="section-note">Ordenado por ingreso más reciente. Los anchos priorizan presentaciones y observaciones.</span>
        </h2>
        <table class="data-table daily {{ count($dailyColumns) > 5 ? 'dense' : '' }}">
            <thead>
                <tr>
                    @foreach($dailyColumns as $column)
                        <th width="{{ $dailyWidths[$column] }}%" style="width: {{ $dailyWidths[$column] }}%">{{ $columnOptions['daily'][$column] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($productions as $production)
                    <tr>
                        @foreach($dailyColumns as $column)
                            <td width="{{ $dailyWidths[$column] }}%" style="width: {{ $dailyWidths[$column] }}%" class="{{ in_array($column, ['date', 'units', 'weight', 'registered_at'], true) ? 'nowrap' : '' }}">
                                @switch($column)
                                    @case('date')
                                        {{ $production->fecha->format('d/m/Y') }}
                                        @break
                                    @case('photo')
                                        @if($photoDataUris[$production->id] ?? null)
                                            <img class="photo" src="{{ $photoDataUris[$production->id] }}" alt="Foto">
                                        @else
                                            <span class="no-photo">Sin foto</span>
                                        @endif
                                        @break
                                    @case('units')
                                        {{ number_format($production->unidades, 0, ',', '.') }} moldes
                                        @break
                                    @case('presentations')
                                        @if($production->presentaciones->isNotEmpty())
                                            @foreach($production->presentaciones as $presentation)
                                                {{ \App\Models\ProduccionQuesoPresentacion::pesoLabel($presentation->peso_gramos) }} × {{ $presentation->cantidad }}@if(! $loop->last)<br>@endif
                                            @endforeach
                                        @else
                                            Sin desglose
                                        @endif
                                        @break
                                    @case('weight')
                                        {{ number_format((float) $production->peso_total_kg, 2, ',', '.') }} kg
                                        @break
                                    @case('observations')
                                        {{ $production->observaciones ?: 'Sin observaciones' }}
                                        @break
                                    @case('registered_at')
                                        {{ $production->created_at?->format('d/m/Y H:i') ?? '-' }}
                                        @break
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ max(count($dailyColumns), 1) }}" class="empty">Sin elaboraciones para los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endif

@if(in_array('weekly', $selectedSections, true))
    @php
        $weeklyColumns = $selectedColumns['weekly'] ?? [];
        $weeklyTotalWeight = max(array_sum(array_intersect_key($weeklyWeights, array_flip($weeklyColumns))), 1);
        $weeklyWidths = [];
        foreach ($weeklyColumns as $column) {
            $weeklyWidths[$column] = round((($weeklyWeights[$column] ?? 10) / $weeklyTotalWeight) * 100, 2);
        }
        $renderedSection++;
    @endphp
    <section class="report-section {{ $renderedSection === 1 ? 'first' : '' }}">
        <h2 class="section-heading weekly">
            Consolidado semanal · {{ $weeklySummaries->count() }} semana(s)
            <span class="section-note">Totales y promedios calculados por cada día con producción registrada.</span>
        </h2>
        <table class="data-table weekly {{ count($weeklyColumns) > 5 ? 'dense' : '' }}">
            <thead>
                <tr>
                    @foreach($weeklyColumns as $column)
                        <th width="{{ $weeklyWidths[$column] }}%" style="width: {{ $weeklyWidths[$column] }}%">{{ $columnOptions['weekly'][$column] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($weeklySummaries as $week)
                    <tr>
                        @foreach($weeklyColumns as $column)
                            <td width="{{ $weeklyWidths[$column] }}%" style="width: {{ $weeklyWidths[$column] }}%" class="{{ $column !== 'period' ? 'nowrap' : '' }}">
                                @switch($column)
                                    @case('week')
                                        Semana {{ substr($week->semana, 4) }} · {{ substr($week->semana, 0, 4) }}
                                        @break
                                    @case('period')
                                        {{ \Carbon\Carbon::parse($week->inicio_semana)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($week->fin_semana)->format('d/m/Y') }}
                                        @break
                                    @case('days')
                                        {{ $week->dias_producidos }} día(s)
                                        @break
                                    @case('units')
                                        {{ number_format($week->total_unidades, 0, ',', '.') }} moldes
                                        @break
                                    @case('weight')
                                        {{ number_format($week->total_peso, 2, ',', '.') }} kg
                                        @break
                                    @case('average_units')
                                        {{ number_format($week->promedio_unidades, 1, ',', '.') }} moldes/día
                                        @break
                                    @case('average_weight')
                                        {{ number_format($week->promedio_peso, 2, ',', '.') }} kg/día
                                        @break
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ max(count($weeklyColumns), 1) }}" class="empty">Sin semanas para consolidar con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endif

@if(in_array('monthly', $selectedSections, true))
    @php
        $monthlyColumns = $selectedColumns['monthly'] ?? [];
        $monthlyTotalWeight = max(array_sum(array_intersect_key($monthlyWeights, array_flip($monthlyColumns))), 1);
        $monthlyWidths = [];
        foreach ($monthlyColumns as $column) {
            $monthlyWidths[$column] = round((($monthlyWeights[$column] ?? 10) / $monthlyTotalWeight) * 100, 2);
        }
        $renderedSection++;
    @endphp
    <section class="report-section {{ $renderedSection === 1 ? 'first' : '' }}">
        <h2 class="section-heading monthly">
            Consolidado mensual · {{ $monthlySummaries->count() }} mes(es)
            <span class="section-note">Producción mes por mes para consultas históricas, financieras y comerciales.</span>
        </h2>
        <table class="data-table monthly {{ count($monthlyColumns) > 5 ? 'dense' : '' }}">
            <thead>
                <tr>
                    @foreach($monthlyColumns as $column)
                        <th width="{{ $monthlyWidths[$column] }}%" style="width: {{ $monthlyWidths[$column] }}%">{{ $columnOptions['monthly'][$column] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($monthlySummaries as $month)
                    <tr>
                        @foreach($monthlyColumns as $column)
                            <td width="{{ $monthlyWidths[$column] }}%" style="width: {{ $monthlyWidths[$column] }}%" class="nowrap">
                                @switch($column)
                                    @case('month')
                                        {{ $month->mes_nombre }} {{ $month->anio }}
                                        @break
                                    @case('records')
                                        {{ $month->registros }} registro(s)
                                        @break
                                    @case('days')
                                        {{ $month->dias_producidos }} día(s)
                                        @break
                                    @case('units')
                                        {{ number_format($month->total_unidades, 0, ',', '.') }} moldes
                                        @break
                                    @case('weight')
                                        {{ number_format($month->total_peso, 2, ',', '.') }} kg
                                        @break
                                    @case('average_units')
                                        {{ number_format($month->promedio_unidades, 1, ',', '.') }} moldes/día
                                        @break
                                    @case('average_weight')
                                        {{ number_format($month->promedio_peso, 2, ',', '.') }} kg/día
                                        @break
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ max(count($monthlyColumns), 1) }}" class="empty">Sin meses para consolidar con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endif

@if(in_array('annual', $selectedSections, true))
    @php
        $annualColumns = $selectedColumns['annual'] ?? [];
        $annualTotalWeight = max(array_sum(array_intersect_key($annualWeights, array_flip($annualColumns))), 1);
        $annualWidths = [];
        foreach ($annualColumns as $column) {
            $annualWidths[$column] = round((($annualWeights[$column] ?? 10) / $annualTotalWeight) * 100, 2);
        }
        $renderedSection++;
    @endphp
    <section class="report-section {{ $renderedSection === 1 ? 'first' : '' }}">
        <h2 class="section-heading annual">
            Consolidado anual · {{ $annualSummaries->count() }} año(s)
            <span class="section-note">Comparativo anual con actividad, volumen total y promedios mensuales.</span>
        </h2>
        <table class="data-table annual {{ count($annualColumns) > 5 ? 'dense' : '' }}">
            <thead>
                <tr>
                    @foreach($annualColumns as $column)
                        <th width="{{ $annualWidths[$column] }}%" style="width: {{ $annualWidths[$column] }}%">{{ $columnOptions['annual'][$column] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($annualSummaries as $year)
                    <tr>
                        @foreach($annualColumns as $column)
                            <td width="{{ $annualWidths[$column] }}%" style="width: {{ $annualWidths[$column] }}%" class="nowrap">
                                @switch($column)
                                    @case('year')
                                        {{ $year->anio }}
                                        @break
                                    @case('months')
                                        {{ $year->meses_producidos }} mes(es)
                                        @break
                                    @case('records')
                                        {{ $year->registros }} registro(s)
                                        @break
                                    @case('days')
                                        {{ $year->dias_producidos }} día(s)
                                        @break
                                    @case('units')
                                        {{ number_format($year->total_unidades, 0, ',', '.') }} moldes
                                        @break
                                    @case('weight')
                                        {{ number_format($year->total_peso, 2, ',', '.') }} kg
                                        @break
                                    @case('average_units')
                                        {{ number_format($year->promedio_unidades, 1, ',', '.') }} moldes/mes
                                        @break
                                    @case('average_weight')
                                        {{ number_format($year->promedio_peso, 2, ',', '.') }} kg/mes
                                        @break
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ max(count($annualColumns), 1) }}" class="empty">Sin años para consolidar con los filtros seleccionados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endif

<div class="footer">
    {{ $branding->name }} · Producción de queso · {{ $fundo->nombre }} · {{ $generatedBy }}
    <span class="page-number">Página </span>
</div>
</body>
</html>
