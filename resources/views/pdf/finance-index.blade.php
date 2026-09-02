<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }} | {{ $branding->name }}</title>
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
        $cardLabelSize = match($scale) {
            '40', '45' => '4.8pt',
            '50', '55' => '5.2pt',
            '65' => '5.6pt',
            '75' => '6.0pt',
            '85' => '6.3pt',
            default => '6.7pt',
        };

        $isViolet = $report['type'] === 'asignaciones';
        $isMono = $pdfConfig->modoColorTablas() === 'mono';
        $primaryColor = $isMono ? $pdfConfig->accentColor() : ($isViolet ? '#8b5cf6' : '#059669');
        $darkColor = $isMono ? $pdfConfig->accentDark() : ($isViolet ? '#5b21b6' : '#065f46');
        $softColor = $isMono ? $pdfConfig->accentSoft() : ($isViolet ? '#f5f3ff' : '#f0fdf4');
        $borderColor = $isMono ? $pdfConfig->accentBorder() : ($isViolet ? '#ddd6fe' : '#a7f3d0');
        $evenColor = $isMono ? $pdfConfig->accentRowEven() : ($isViolet ? '#fdfaff' : '#f9fdfa');
        $radius = $pdfConfig->tableBorderRadius();
    @endphp
    <style>
        @page { size: A4 landscape; margin: 12mm 10mm 14mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #1e293b; font-family: DejaVu Sans, sans-serif; font-size: {{ $tableFontSize }}; line-height: 1.25; }

        .header { padding: 6pt 10pt; border-left: 4px solid {{ $primaryColor }}; border-radius: {{ $radius }}; background: {{ $softColor }}; margin-bottom: 5pt; }
        .eyebrow { margin: 0 0 1.5pt; color: {{ $primaryColor }}; font-size: 6.2pt; font-weight: bold; letter-spacing: 0.8px; text-transform: uppercase; }
        h1 { margin: 0; color: {{ $darkColor }}; font-size: 13.5pt; line-height: 1.1; font-weight: 900; }
        .subtitle { margin: 2px 0 0; color: #475569; font-size: 7.2pt; }
        
        .meta { width: 100%; margin-top: 4pt; margin-bottom: 4pt; border-collapse: collapse; table-layout: fixed; }
        .meta td { padding: 3pt 5pt; border: 1px solid {{ $borderColor }}; background: {{ $softColor }}; font-size: 7pt; }
        .meta-label { display: block; color: {{ $darkColor }}; font-size: 5.6pt; font-weight: bold; text-transform: uppercase; }
        .meta-value { display: block; margin-top: 1px; color: #1e293b; font-size: 7pt; font-weight: bold; }
        
        .section { margin-top: 5pt; page-break-inside: auto; }
        .section-title { margin: 0 0 3pt; padding: 2.5pt 5pt; border-left: 3px solid {{ $primaryColor }}; background: {{ $softColor }}; color: {{ $darkColor }}; font-size: {{ $thFontSize }}; font-weight: bold; }
        .section-alt-title { margin: 0 0 3pt; padding: 2.5pt 5pt; border-left: 3px solid {{ $darkColor }}; background: {{ $softColor }}; color: {{ $darkColor }}; font-size: {{ $thFontSize }}; font-weight: bold; }
        
        .summary-wrap { margin-top: 3pt; margin-bottom: 5pt; page-break-inside: avoid; }
        .summary-heading { background: {{ $primaryColor }}; color: #ffffff; padding: 2.5pt 5pt; font-size: {{ $thFontSize }}; font-weight: bold; border-radius: {{ $radius }} {{ $radius }} 0 0; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .summary-table th { padding: {{ $tableCellPad }}; border: 1px solid {{ $borderColor }}; background: {{ $softColor }}; color: {{ $darkColor }}; font-size: {{ $thFontSize }}; text-transform: uppercase; }
        .summary-table td { padding: {{ $tableCellPad }}; border: 1px solid {{ $borderColor }}; font-size: {{ $tableFontSize }}; font-weight: bold; }
        
        .compact-grid-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .compact-grid-table th { padding: {{ $tableCellPad }}; border: 1px solid {{ $borderColor }}; background: {{ $softColor }}; color: {{ $darkColor }}; font-size: {{ $thFontSize }}; text-transform: uppercase; }
        .compact-grid-table td { padding: {{ $tableCellPad }}; border: 1px solid {{ $borderColor }}; font-size: {{ $tableFontSize }}; }
        .compact-grid-table tbody tr:nth-child(even) td { background: {{ $evenColor }}; }

        .data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data thead { display: table-header-group; }
        .data tr { page-break-inside: avoid; }
        .data th { padding: {{ $tableCellPad }}; border: 1px solid {{ $darkColor }}; background: {{ $primaryColor }}; color: #ffffff; font-size: {{ $thFontSize }}; text-transform: uppercase; }
        .data td { padding: {{ $tableCellPad }}; border: 1px solid {{ $borderColor }}; color: #1e293b; font-size: {{ $tableFontSize }}; vertical-align: top; word-wrap: break-word; }
        .data tbody tr:nth-child(even) td { background: {{ $evenColor }}; }
        
        .data-alt { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data-alt thead { display: table-header-group; }
        .data-alt tr { page-break-inside: avoid; }
        .data-alt th { padding: {{ $tableCellPad }}; border: 1px solid {{ $darkColor }}; background: {{ $primaryColor }}; color: #ffffff; font-size: {{ $thFontSize }}; text-transform: uppercase; }
        .data-alt td { padding: {{ $tableCellPad }}; border: 1px solid {{ $borderColor }}; color: #1e293b; font-size: {{ $tableFontSize }}; vertical-align: top; word-wrap: break-word; }
        .data-alt tbody tr:nth-child(even) td { background: {{ $evenColor }}; }
        
        .empty { padding: 8pt; border: 1px solid {{ $borderColor }}; color: #64748b; text-align: center; }
    </style>
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.watermark')
    @php
        $recordWeights = [
            'date' => 12,
            'type' => 12,
            'category' => 14,
            'beneficiary' => 20,
            'purpose' => 20,
            'description' => 28,
            'amount' => 14,
        ];

        $aggregateWeights = [
            'category' => 35,
            'type' => 20,
            'purpose' => 35,
            'records' => 15,
            'average' => 15,
            'total' => 15,
        ];

        $colAlign = function ($col) {
            if (in_array($col, ['income', 'expenses', 'balance', 'amount', 'total', 'average'])) {
                return 'text-align: right;';
            }
            if (in_array($col, ['date', 'records', 'type', 'period'])) {
                return 'text-align: center;';
            }
            return 'text-align: left;';
        };

        $cellStyle = function ($col, $val) use ($colAlign) {
            $style = $colAlign($col);
            if ($col === 'amount') {
                if (strpos($val, '+') === 0) {
                    return $style . ' color: #059669; font-weight: bold;';
                }
                if (strpos($val, '-') === 0) {
                    return $style . ' color: #e11d48; font-weight: bold;';
                }
            }
            return $style;
        };

        $aggCellStyle = function ($col, $row) use ($colAlign) {
            $style = $colAlign($col);
            if ($col === 'total') {
                $type = $row['type'] ?? '';
                if ($type === 'Ingreso') return $style . ' color: #059669; font-weight: bold;';
                if ($type === 'Egreso') return $style . ' color: #e11d48; font-weight: bold;';
            }
            return $style;
        };

        $summaryValueStyle = function ($col, $val) {
            if ($col === 'income') return 'color: #059669;';
            if ($col === 'expenses') return 'color: #e11d48;';
            if ($col === 'balance') {
                return strpos($val, '-') !== false ? 'color: #e11d48;' : 'color: #0284c7;';
            }
            return 'color: #1e293b;';
        };
    @endphp

    <header class="header">
        @if($pdfConfig->showHeaderLogo())
            <x-brand-logo pdf style="float: right; width: 26pt; height: 26pt; color: {{ $primaryColor }}; object-fit: contain" />
        @endif
        <p class="eyebrow">{{ $branding->name }} · {{ $branding->tagline }} · Finanzas</p>
        <h1>{{ $report['title'] }}</h1>
        <p class="subtitle">{{ $report['subtitle'] }}</p>
    </header>

    <table class="meta">
        <tr>
            <td><span class="meta-label">Fundo</span><span class="meta-value">{{ $fundo->nombre }}</span></td>
            <td><span class="meta-label">Generado</span><span class="meta-value">{{ $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i') }}</span></td>
            <td><span class="meta-label">Responsable</span><span class="meta-value">{{ $generatedBy }}</span></td>
        </tr>
    </table>

    <!-- Compact Analytical Tables Section: Evolución mensual + Top Categorías -->
    @php
        $grouped = $records->groupBy(fn ($r) => $r->fecha->format('Y-m'));
        $keys = $grouped->keys()->sort();
        
        $chartMonths = [];
        $monthNames = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        
        foreach ($keys as $key) {
            $monthGroup = $grouped->get($key);
            $first = $monthGroup->first();
            $carbon = $first->fecha;
            $label = $monthNames[$carbon->month].' '.substr($carbon->year, 2);
            
            if ($report['type'] === 'movimientos') {
                $income = (float) $monthGroup->where('tipo', 'ingreso')->sum('monto');
                $expenses = (float) $monthGroup->where('tipo', 'egreso')->sum('monto');
                $chartMonths[] = [
                    'label' => $label,
                    'income' => $income,
                    'expenses' => $expenses,
                ];
            } else {
                $amount = (float) $monthGroup->sum('monto');
                $chartMonths[] = [
                    'label' => $label,
                    'amount' => $amount,
                ];
            }
        }
        
        $tableMonths = array_slice($chartMonths, -5);

        // Top breakdown
        $topItems = [];
        $totalItemAmount = 1;
        if ($report['type'] === 'movimientos') {
            $categoryTotals = [];
            foreach ($records as $r) {
                $type = ucfirst($r->tipo);
                $catName = $r->categoria?->nombre ?? 'Sin categoría';
                $key = $catName . '|' . $type;
                if (!isset($categoryTotals[$key])) {
                    $categoryTotals[$key] = [
                        'name' => $catName,
                        'type' => $type,
                        'amount' => 0.0,
                    ];
                }
                $categoryTotals[$key]['amount'] += (float)$r->monto;
            }
            uasort($categoryTotals, fn ($a, $b) => $b['amount'] <=> $a['amount']);
            $totalItemAmount = array_sum(array_column($categoryTotals, 'amount')) ?: 1;
            $topItems = array_slice($categoryTotals, 0, 5);
        } else {
            $purposeTotals = [];
            foreach ($records as $r) {
                $purposeName = ucfirst(str_replace('_', ' ', $r->proposito));
                $key = $purposeName;
                if (!isset($purposeTotals[$key])) {
                    $purposeTotals[$key] = [
                        'name' => $purposeName,
                        'type' => 'Asignación',
                        'amount' => 0.0,
                    ];
                }
                $purposeTotals[$key]['amount'] += (float)$r->monto;
            }
            uasort($purposeTotals, fn ($a, $b) => $b['amount'] <=> $a['amount']);
            $totalItemAmount = array_sum(array_column($purposeTotals, 'amount')) ?: 1;
            $topItems = array_slice($purposeTotals, 0, 5);
        }
    @endphp

    <table style="width: 100%; margin-top: 2pt; margin-bottom: 4pt; border-collapse: collapse; table-layout: fixed; page-break-inside: avoid;">
        <tr>
            <td style="width: 50%; padding-right: 4pt; vertical-align: top;">
                <div class="summary-wrap" style="margin: 0;">
                    <div class="summary-heading">
                        {{ $isViolet ? 'Evolución de Entregas Mensuales' : 'Evolución de Caja Mensual' }}
                    </div>
                    <table class="compact-grid-table">
                        <thead>
                            <tr>
                                @if($isViolet)
                                    <th style="width: 50%; text-align: left;">Mes</th>
                                    <th style="width: 50%; text-align: right;">Total Asignado</th>
                                @else
                                    <th style="width: 25%; text-align: left;">Mes</th>
                                    <th style="width: 25%; text-align: right;">Ingresos</th>
                                    <th style="width: 25%; text-align: right;">Egresos</th>
                                    <th style="width: 25%; text-align: right;">Neto</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tableMonths as $m)
                                <tr>
                                    @if($isViolet)
                                        <td style="font-weight: bold;">{{ $m['label'] }}</td>
                                        <td style="text-align: right; font-weight: bold; color: {{ $primaryColor }};">S/. {{ number_format($m['amount'], 2) }}</td>
                                    @else
                                        @php $net = $m['income'] - $m['expenses']; @endphp
                                        <td style="font-weight: bold;">{{ $m['label'] }}</td>
                                        <td style="text-align: right; color: #059669; font-weight: bold;">S/. {{ number_format($m['income'], 2) }}</td>
                                        <td style="text-align: right; color: #e11d48; font-weight: bold;">S/. {{ number_format($m['expenses'], 2) }}</td>
                                        <td style="text-align: right; font-weight: bold; color: {{ $net >= 0 ? '#0284c7' : '#e11d48' }};">
                                            S/. {{ number_format($net, 2) }}
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isViolet ? 2 : 4 }}" style="text-align: center; color: #64748b; font-style: italic; padding: 5pt;">Sin datos de evolución mensual.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
            <td style="width: 50%; padding-left: 4pt; vertical-align: top;">
                <div class="summary-wrap" style="margin: 0;">
                    <div class="summary-heading">
                        {{ $isViolet ? 'Destinos de Asignaciones Principales' : 'Top Categorías (Ingreso y Egreso)' }}
                    </div>
                    <table class="compact-grid-table">
                        <thead>
                            <tr>
                                <th style="width: 48%; text-align: left;">Categoría</th>
                                <th style="width: 20%; text-align: center;">Tipo</th>
                                <th style="width: 32%; text-align: right;">Monto (% Part.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topItems as $item)
                                @php
                                    $pct = round(($item['amount'] / $totalItemAmount) * 100, 1);
                                    $typeColor = $item['type'] === 'Ingreso' ? '#059669' : ($item['type'] === 'Egreso' ? '#e11d48' : $darkColor);
                                @endphp
                                <tr>
                                    <td style="font-weight: bold; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">{{ $item['name'] }}</td>
                                    <td style="text-align: center; font-weight: bold; color: {{ $typeColor }};">{{ $item['type'] }}</td>
                                    <td style="text-align: right; font-weight: bold;">
                                        S/. {{ number_format($item['amount'], 2) }}
                                        <span style="font-size: {{ $cardLabelSize }}; color: #64748b; font-weight: normal;">({{ $pct }}%)</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: #64748b; font-style: italic; padding: 5pt;">Sin categorías registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Resumen Financiero Consolidado -->
    @if(in_array('summary', $report['sections'], true))
        @php
            $summaryColumns = $report['selectedColumns']['summary'] ?? [];
            $summaryColWidth = round(100 / max(count($summaryColumns), 1), 2);
        @endphp
        <div class="summary-wrap" style="margin-top: 2pt; margin-bottom: 5pt; page-break-inside: avoid;">
            <div class="summary-heading">
                {{ $report['sectionOptions']['summary']['label'] }} · Balance Consolidado
            </div>
            <table class="summary-table">
                <thead>
                    <tr>
                        @foreach($summaryColumns as $column)
                            <th style="width: {{ $summaryColWidth }}%; {{ $colAlign($column) }}">
                                {{ $report['columnOptions']['summary'][$column] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($summaryColumns as $column)
                            <td style="{{ $colAlign($column) }}; {{ $summaryValueStyle($column, $report['summary'][$column] ?? '') }}">
                                {{ $report['summary'][$column] ?? '-' }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif

    <!-- Tabla Detallada de Movimientos -->
    @if(in_array('records', $report['sections'], true))
        @php
            $recordColumns = $report['selectedColumns']['records'] ?? [];
        @endphp
        <section class="section">
            <h2 class="section-title">{{ $report['sectionOptions']['records']['label'] }}</h2>
            @if(count($report['rows']) > 0)
                <table class="data">
                    <thead>
                        <tr>
                            @foreach($recordColumns as $column)
                                <th style="width: {{ $recordWeights[$column] ?? 'auto' }}%; {{ $colAlign($column) }}">{{ $report['columnOptions']['records'][$column] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['rows'] as $row)
                            <tr>
                                @foreach($recordColumns as $column)
                                    <td style="{{ $cellStyle($column, $row[$column] ?? '') }}">{{ $row[$column] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">No hay registros para los filtros actuales.</div>
            @endif
        </section>
    @endif

    <!-- Tabla Agrupada (Categorías / Propósitos) -->
    @php
        $aggregateSection = $report['type'] === 'movimientos' ? 'categories' : 'purposes';
    @endphp
    @if(in_array($aggregateSection, $report['sections'], true))
        @php
            $aggregateColumns = $report['selectedColumns'][$aggregateSection] ?? [];
        @endphp
        <section class="section" style="margin-top: 8pt; page-break-inside: auto;">
            <h2 class="section-alt-title" style="page-break-after: avoid;">{{ $report['sectionOptions'][$aggregateSection]['label'] }}</h2>
            @if(count($report['aggregates']) > 0)
                <table class="data-alt">
                    <thead>
                        <tr>
                            @foreach($aggregateColumns as $column)
                                <th style="width: {{ $aggregateWeights[$column] ?? 'auto' }}%; {{ $colAlign($column) }}">{{ $report['columnOptions'][$aggregateSection][$column] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['aggregates'] as $row)
                            <tr>
                                @foreach($aggregateColumns as $column)
                                    <td style="{{ $aggCellStyle($column, $row) }}">{{ $row[$column] ?? '-' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">No hay información agrupada para mostrar.</div>
            @endif
        </section>
    @endif

    @include('pdf.partials.signatures')
    @include('pdf.partials.footer')
</body>
</html>
