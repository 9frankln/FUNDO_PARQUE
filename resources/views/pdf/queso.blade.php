@php
    $scale = (string) ($scale ?? '85');
    $tableFontSize = match($scale) {
        '40', '45' => '5.2pt',
        '50', '55' => '5.8pt',
        '65' => '6.4pt',
        '75' => '7.0pt',
        '85' => '7.5pt',
        default => '8.0pt',
    };
    $thFontSize = match($scale) {
        '40', '45' => '4.8pt',
        '50', '55' => '5.4pt',
        '65' => '6.0pt',
        '75' => '6.6pt',
        '85' => '7.0pt',
        default => '7.5pt',
    };
    $tableCellPad = match($scale) {
        '40', '45' => '1.5pt 2.5pt',
        '50', '55' => '2pt 3pt',
        '65' => '2.5pt 3.5pt',
        '75' => '3pt 4pt',
        '85' => '3.5pt 4.5pt',
        default => '4.5pt 5.5pt',
    };
    $cardPad = match($scale) {
        '40', '45' => '2.5pt 3.5pt',
        '50', '55' => '3pt 4.5pt',
        '65' => '3.5pt 5pt',
        '75' => '4pt 5.5pt',
        '85' => '4.5pt 6pt',
        default => '6pt 7.5pt',
    };
    $cardLabelSize = match($scale) {
        '40', '45' => '4.8pt',
        '50', '55' => '5.2pt',
        '65' => '5.6pt',
        '75' => '6.0pt',
        '85' => '6.3pt',
        default => '6.7pt',
    };
    $cardValSize = match($scale) {
        '40', '45' => '7.5pt',
        '50', '55' => '8.5pt',
        '65' => '9.5pt',
        '75' => '10.5pt',
        '85' => '11.5pt',
        default => '12.5pt',
    };
    $chartHeight = match($scale) {
        '40', '45' => '56pt',
        '50', '55' => '66pt',
        '65' => '74pt',
        '75' => '82pt',
        '85' => '88pt',
        default => '96pt',
    };
    $barMaxH = match($scale) {
        '40', '45' => 24,
        '50', '55' => 30,
        '65' => 36,
        '75' => 42,
        '85' => 46,
        default => 50,
    };

    $dailyWeights = [
        'date' => 9,
        'photo' => 7,
        'units' => 8,
        'presentations' => 13,
        'weight' => 9,
        'observations' => 42,
        'registered_at' => 12,
    ];
    $weeklyWeights = [
        'week' => 10,
        'period' => 19,
        'days' => 10,
        'units' => 13,
        'weight' => 12,
        'average_units' => 17,
        'average_weight' => 16,
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

    $isMono = $pdfConfig->modoColorTablas() === 'mono';
    $primaryColor = $pdfConfig->accentColor();
    $darkColor = $pdfConfig->accentDark();
    $softColor = $pdfConfig->accentSoft();
    $borderColor = $pdfConfig->accentBorder();
    $evenColor = $pdfConfig->accentRowEven();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de producción de queso | {{ $branding->name }}</title>
<style>
@page { size: A4 landscape; margin: 9mm 8mm 11mm 8mm; }
* { box-sizing: border-box; }
body {
    margin: 0;
    color: #1e293b;
    font-family: DejaVu Sans, sans-serif;
    font-size: 8pt;
    line-height: 1.25;
}
.header { margin-bottom: 5pt; padding-bottom: 4pt; border-bottom: 2.5px solid {{ $primaryColor }}; }
.eyebrow { margin: 0 0 1.5pt; color: {{ $primaryColor }}; font-size: 6.5pt; font-weight: bold; letter-spacing: 0.8px; text-transform: uppercase; }
h1 { margin: 0 0 2pt; color: {{ $darkColor }}; font-size: 15pt; line-height: 1.1; font-weight: 900; }
.subtitle { margin: 0; color: #475569; font-size: 7.2pt; }
.meta-table, .context-table, .summary-grid, .data-table { width: 100%; border-collapse: collapse; }
.meta-table { margin-bottom: 5pt; table-layout: fixed; }
.meta-table td { padding: 3pt 5pt; border: 1px solid {{ $borderColor }}; background: {{ $softColor }}; vertical-align: top; }
.meta-label { color: {{ $darkColor }}; font-weight: bold; }
.report-section { margin-top: 6pt; margin-bottom: 6pt; page-break-inside: auto; }
.section-heading {
    margin: 0 0 5pt;
    padding: 3.5pt 6pt;
    border-left: 4px solid {{ $primaryColor }};
    background: {{ $softColor }};
    color: {{ $darkColor }};
    font-size: 9.5pt;
    font-weight: bold;
    page-break-after: avoid;
    border-radius: {{ $pdfConfig->tableBorderRadius() }};
}
.section-note { display: block; margin-top: 1.5pt; color: #64748b; font-size: 6.2pt; font-weight: normal; }
.summary-grid { margin-bottom: 3pt; table-layout: fixed; }
.summary-grid td { width: 25%; padding: 2pt; vertical-align: top; }
.summary-card {
    padding: {{ $cardPad }};
    border-radius: {{ $pdfConfig->tableBorderRadius() }};
    page-break-inside: avoid;
}
.summary-card-label {
    display: block;
    margin-bottom: 2px;
    font-size: {{ $cardLabelSize }};
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.summary-card-value {
    font-size: {{ $cardValSize }};
    font-weight: bold;
    line-height: 1.1;
    display: block;
}
.data-table { table-layout: fixed; border-collapse: collapse; width: 100%; }
.data-table thead { display: table-header-group; }
.data-table tbody tr { page-break-inside: avoid; }
.data-table th {
    padding: {{ $tableCellPad }};
    font-size: {{ $thFontSize }};
    line-height: 1.18;
    text-align: left;
    text-transform: uppercase;
    vertical-align: middle;
    word-wrap: break-word;
}
.data-table td {
    padding: {{ $tableCellPad }};
    font-size: {{ $tableFontSize }};
    line-height: 1.22;
    vertical-align: top;
    word-wrap: break-word;
}

@if($isMono)
    .section-heading { border-left-color: {{ $primaryColor }}; background: {{ $softColor }}; color: {{ $darkColor }}; }
    .data-table th { border: 1px solid {{ $darkColor }}; background: {{ $primaryColor }}; color: #ffffff; }
    .data-table td { border: 1px solid {{ $borderColor }}; }
    .data-table tbody tr:nth-child(even) { background-color: {{ $evenColor }}; }
@else
    .section-heading.daily { border-left-color: #d97706; background: #fffbeb; color: #92400e; }
    .section-heading.weekly { border-left-color: #0284c7; background: #f0f9ff; color: #0369a1; }
    .section-heading.monthly { border-left-color: #7c3aed; background: #f5f3ff; color: #5b21b6; }
    .section-heading.annual { border-left-color: #e11d48; background: #fff1f2; color: #9f1239; }

    .data-table.daily th { border: 1px solid #b45309; background: #d97706; color: #ffffff; }
    .data-table.daily td { border: 1px solid #fed7aa; }
    .data-table.daily tbody tr:nth-child(even) { background-color: #fffbeb; }

    .data-table.weekly th { border: 1px solid #0369a1; background: #0284c7; color: #ffffff; }
    .data-table.weekly td { border: 1px solid #bae6fd; }
    .data-table.weekly tbody tr:nth-child(even) { background-color: #f0f9ff; }

    .data-table.monthly th { border: 1px solid #5b21b6; background: #7c3aed; color: #ffffff; }
    .data-table.monthly td { border: 1px solid #ddd6fe; }
    .data-table.monthly tbody tr:nth-child(even) { background-color: #f5f3ff; }

    .data-table.annual th { border: 1px solid #9f1239; background: #e11d48; color: #ffffff; }
    .data-table.annual td { border: 1px solid #fecdd3; }
    .data-table.annual tbody tr:nth-child(even) { background-color: #fff1f2; }
@endif

.nowrap { white-space: nowrap; }
.photo { display: block; width: 38pt; height: 28pt; border: 1px solid {{ $borderColor }}; object-fit: cover; border-radius: 3px; }
.no-photo { color: #94a3b8; font-size: 6.2pt; font-style: italic; }
.empty { padding: 10pt !important; color: #64748b; font-style: italic; text-align: center; }
</style>
@include('pdf.partials.styles')
</head>
<body>
@include('pdf.partials.watermark')
<div class="header" style="border-bottom-color: {{ $primaryColor }};">
    @if($pdfConfig->showHeaderLogo())
        <x-brand-logo pdf style="float: right; width: 26pt; height: 26pt; color: {{ $primaryColor }}; object-fit: contain" />
    @endif
    <p class="eyebrow" style="color: {{ $primaryColor }};">{{ $branding->tagline }} | Producción y transformación láctea</p>
    <h1 style="color: {{ $darkColor }};">{{ $branding->name }} - Reporte de Producción de Queso</h1>
    <p class="subtitle">Fundo: <strong>{{ $fundo->nombre }}</strong> &nbsp;&middot;&nbsp; Generado el {{ $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i') }} (hora Perú)</p>
</div>

<div class="summary-card" style="border: 1px solid {{ $borderColor }}; border-radius: {{ $pdfConfig->tableBorderRadius() }}; overflow: hidden; background-color: {{ $softColor }}; margin-bottom: 6pt; padding: 3pt 5pt;">
    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; background: transparent;">
        <tr>
            <td style="width: 50%; border: none; border-right: 1px solid {{ $borderColor }}; border-bottom: 1px solid {{ $borderColor }}; color: #1e293b; padding: 2pt 4pt; font-size: 7pt;"><strong style="color: {{ $darkColor }};">Generado por:</strong> {{ $generatedBy }}</td>
            <td style="width: 50%; border: none; border-bottom: 1px solid {{ $borderColor }}; color: #1e293b; padding: 2pt 4pt; font-size: 7pt;"><strong style="color: {{ $darkColor }};">Usuario / Documento:</strong> {{ auth()->user()?->name ?? 'Sistema' }} ({{ auth()->user()?->dni ? 'DNI: '.auth()->user()->dni : (auth()->user()?->username ? '@'.auth()->user()->username : 'Sistema') }})</td>
        </tr>
        <tr>
            <td colspan="2" style="border: none; color: #1e293b; padding: 2pt 4pt; font-size: 7pt;"><strong style="color: {{ $darkColor }};">Alcance del reporte:</strong> {{ $reportSummary }}</td>
        </tr>
    </table>
</div>

@php
    $renderedSection = 0;
@endphp
@if(in_array('summary', $selectedSections, true) && !empty($selectedColumns['summary']))
    @php
        $summaryFields = $selectedColumns['summary'] ?? [];
        $summaryRows = array_chunk($summaryFields, 4);
        $renderedSection++;
    @endphp
    <div class="summary-wrap" style="margin-top: 3pt; margin-bottom: 6pt; page-break-inside: avoid;">
        <div class="summary-heading" style="background: {{ $primaryColor }}; color: #ffffff; padding: 3pt 6pt; font-size: {{ $thFontSize }}; font-weight: bold; border-radius: {{ $pdfConfig->tableBorderRadius() }} {{ $pdfConfig->tableBorderRadius() }} 0 0; text-transform: uppercase; letter-spacing: 0.5px;">
            Resumen Productivo · Indicadores Consolidados
        </div>
        <table class="summary-table" style="width: 100%; border-collapse: collapse; table-layout: fixed;">
            @foreach($summaryRows as $row)
                <thead>
                    <tr>
                        @foreach($row as $field)
                            <th style="width: 25%; padding: {{ $tableCellPad }}; font-size: {{ $thFontSize }}; background: {{ $softColor }}; color: {{ $darkColor }}; border: 1px solid {{ $borderColor }}; text-transform: uppercase;">
                                {{ $columnOptions['summary'][$field] }}
                            </th>
                        @endforeach
                        @for($e = count($row); $e < 4; $e++)
                            <th style="width: 25%; border: 1px solid {{ $borderColor }}; background: {{ $softColor }};"></th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($row as $field)
                            @php
                                $valColor = '#1e293b';
                                if (!$isMono) {
                                    if (in_array($field, ['weight', 'period'], true)) $valColor = '#065f46';
                                    elseif (in_array($field, ['records', 'days', 'units'], true)) $valColor = '#075985';
                                    elseif (in_array($field, ['average_units', 'average_weight'], true)) $valColor = '#5b21b6';
                                    elseif ($field === 'last_production') $valColor = '#92400e';
                                } else {
                                    $valColor = $darkColor;
                                }
                            @endphp
                            <td style="padding: {{ $tableCellPad }}; font-size: {{ $tableFontSize }}; font-weight: bold; border: 1px solid {{ $borderColor }}; background: {{ $loop->parent->even ? $evenColor : '#ffffff' }}; color: {{ $valColor }};">
                                {{ $summary[$field] ?? '-' }}
                            </td>
                        @endforeach
                        @for($e = count($row); $e < 4; $e++)
                            <td style="border: 1px solid {{ $borderColor }}; background: {{ $loop->parent->even ? $evenColor : '#ffffff' }};"></td>
                        @endfor
                    </tr>
                </tbody>
            @endforeach
        </table>
    </div>
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

@include('pdf.partials.signatures')
@include('pdf.partials.footer')
</body>
</html>
