<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }} | {{ $branding->name }}</title>
    <style>
        @page { size: A4 landscape; margin: 14mm 10mm 16mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #26342d; font-family: DejaVu Sans, sans-serif; font-size: 7.5pt; line-height: 1.3; }
        
        /* Dynamic Theme styles */
        @php
            $isViolet = $report['type'] === 'asignaciones';
            $primaryColor = $isViolet ? '#8b5cf6' : '#10b981';
            $secondaryBg = $isViolet ? '#faf5ff' : '#f4fbf7';
            $eyebrowColor = $isViolet ? '#7c3aed' : '#059669';
            $sectionBg = $isViolet ? '#fdfcff' : '#f9fdfa';
            $tableHeaderBg = $isViolet ? '#f5f3ff' : '#f0fdf4';
            $tableHeaderColor = $isViolet ? '#6d28d9' : '#065f46';
            $tableHeaderBorder = $isViolet ? '#ede9fe' : '#d1fae5';
            $altPrimary = $isViolet ? '#581c87' : '#2b6279';
            $altSectionBg = $isViolet ? '#faf5ff' : '#f2f7f9';
            $altHeaderBg = $isViolet ? '#6b21a8' : '#336b82';
            $altHeaderColor = '#ffffff';
            $altBorder = $isViolet ? '#e9d5ff' : '#d4e3ea';
            $altRowBg = $isViolet ? '#fdfaff' : '#f9fbfc';
        @endphp

        .header { padding: 9pt 11pt; border-left: 4px solid {{ $primaryColor }}; border-radius: 4px; background: linear-gradient(90deg, {{ $secondaryBg }} 0%, #ffffff 100%); margin-bottom: 7pt; }
        .eyebrow { margin: 0 0 2pt; color: {{ $eyebrowColor }}; font-size: 6.2pt; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        h1 { margin: 0; color: #14231b; font-size: 15pt; line-height: 1.1; }
        .subtitle { margin: 3px 0 0; color: #627168; font-size: 7.2pt; }
        
        .meta { width: 100%; margin-top: 5pt; margin-bottom: 5pt; border-collapse: collapse; table-layout: fixed; }
        .meta td { padding: 4pt 6pt; border: 1px solid #dbe5de; background: #fbfdfc; font-size: 7.2pt; }
        .meta-label { display: block; color: #708078; font-size: 5.8pt; font-weight: bold; text-transform: uppercase; }
        .meta-value { display: block; margin-top: 1px; color: #25372d; font-size: 7.2pt; font-weight: bold; }
        
        .section { margin-top: 7pt; }
        .section-title { margin: 0 0 4pt; padding: 4pt 6pt; border-left: 3px solid {{ $primaryColor }}; background: {{ $sectionBg }}; color: {{ $eyebrowColor }}; font-size: 8pt; font-weight: bold; }
        .section-alt-title { margin: 0 0 4pt; padding: 4pt 6pt; border-left: 3px solid {{ $altPrimary }}; background: {{ $altSectionBg }}; color: {{ $altPrimary }}; font-size: 8pt; font-weight: bold; }
        
        .summary { width: 100%; border-collapse: separate; border-spacing: 3pt 0; table-layout: fixed; }
        .summary td { padding: 5pt 6pt; border: 1px solid #d8e5dd; background: #fbfdfc; vertical-align: top; }
        .summary-label { display: block; color: #718078; font-size: 5.8pt; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; }
        .summary-value { display: block; margin-top: 2px; font-size: 9pt; font-weight: bold; }
        
        .data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data thead { display: table-header-group; }
        .data tr { page-break-inside: avoid; }
        .data th { padding: 3.5pt 4.5pt; border: 1px solid {{ $tableHeaderBorder }}; background: {{ $tableHeaderBg }}; color: {{ $tableHeaderColor }}; font-size: 6.5pt; text-transform: uppercase; }
        .data td { padding: 3.5pt 4.5pt; border: 1px solid #dde6e0; color: #2d3b33; font-size: 7.2pt; vertical-align: top; word-wrap: break-word; }
        .data tbody tr:nth-child(even) td { background: #fafcfb; }
        
        .data-alt { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data-alt thead { display: table-header-group; }
        .data-alt tr { page-break-inside: avoid; }
        .data-alt th { padding: 4.5pt 5.5pt; border: 1px solid {{ $altHeaderBg }}; background: {{ $altHeaderBg }}; color: {{ $altHeaderColor }}; font-size: 6pt; text-transform: uppercase; }
        .data-alt td { padding: 4pt 5pt; border: 1px solid {{ $altBorder }}; color: #334155; font-size: 7.2pt; vertical-align: top; word-wrap: break-word; }
        .data-alt tbody tr:nth-child(even) td { background: {{ $altRowBg }}; }
        
        .empty { padding: 10pt; border: 1px solid #dde6e0; color: #7a8980; text-align: center; }
    </style>
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.watermark')
    @php
        $recordWeights = [
            'date' => 12,
            'type' => 12,
            'category' => 13,
            'beneficiary' => 20,
            'purpose' => 20,
            'description' => 29,
            'amount' => 12,
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
                return strpos($val, '-') !== false ? 'color: #e11d48;' : 'color: #0369a1;';
            }
            return 'color: #1f3127;';
        };
    @endphp

    <header class="header">
        @if($pdfConfig->showHeaderLogo())
            <x-brand-logo pdf style="float: right; width: 28pt; height: 28pt; color: {{ $eyebrowColor }}; object-fit: contain" />
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

    <!-- Visual Dashboard Section -->
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
        
        $chartMonths = array_slice($chartMonths, -12);
        
        // Max value for scaling
        if ($report['type'] === 'movimientos') {
            $maxVal = max(array_merge([1], array_column($chartMonths, 'income'), array_column($chartMonths, 'expenses')));
        } else {
            $maxVal = max(array_merge([1], array_column($chartMonths, 'amount')));
        }

        // Top 5 breakdown
        $topItems = [];
        $totalItemAmount = 1;
        if ($report['type'] === 'movimientos') {
            $categoryTotals = [];
            foreach ($records as $r) {
                $catName = ($r->categoria?->nombre ?? 'Sin categoría') . ' (' . ucfirst($r->tipo) . ')';
                $categoryTotals[$catName] = ($categoryTotals[$catName] ?? 0) + (float)$r->monto;
            }
            arsort($categoryTotals);
            $totalItemAmount = array_sum($categoryTotals) ?: 1;
            $topItems = array_slice($categoryTotals, 0, 5, true);
        } else {
            $purposeTotals = [];
            foreach ($records as $r) {
                $purposeName = ucfirst(str_replace('_', ' ', $r->proposito));
                $purposeTotals[$purposeName] = ($purposeTotals[$purposeName] ?? 0) + (float)$r->monto;
            }
            arsort($purposeTotals);
            $totalItemAmount = array_sum($purposeTotals) ?: 1;
            $topItems = array_slice($purposeTotals, 0, 5, true);
        }
        
        $boxBorder = $isViolet ? '#ddd6fe' : '#a7f3d0';
        $boxBg = $isViolet ? '#f5f3ff' : '#f0fdf4';
        $titleColor = $isViolet ? '#5b21b6' : '#065f46';
        $borderBottomColor = $isViolet ? '#ddd6fe' : '#d1fae5';
        $barColor = $isViolet ? '#8b5cf6' : '#10b981';
    @endphp

    <table style="width: 100%; margin-top: 5px; margin-bottom: 5px; border-collapse: collapse; table-layout: fixed;">
        <tr>
            <td style="width: 50%; padding-right: 6px; vertical-align: top;">
                <div style="border: 1px solid {{ $boxBorder }}; background: {{ $boxBg }}; padding: 7px 9px; border-radius: 8px; height: 92pt;">
                    <strong style="color: {{ $titleColor }}; font-size: 7.2pt; display: block; margin-bottom: 3px; border-bottom: 1px solid {{ $borderBottomColor }}; padding-bottom: 2px;">
                        {{ $isViolet ? 'Evolución de Entregas Mensuales' : 'Evolución de Caja Mensual' }}
                    </strong>
                    
                    @if(count($chartMonths) > 0)
                        <table style="width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 4px;">
                            <tr style="height: 40px;">
                                @foreach($chartMonths as $bar)
                                    @php
                                        if ($isViolet) {
                                            $val = $bar['amount'];
                                            $h1 = max(round(($val / $maxVal) * 35), 1);
                                        } else {
                                            $valInc = $bar['income'];
                                            $valExp = $bar['expenses'];
                                            $h1 = max(round(($valInc / $maxVal) * 35), 1);
                                            $h2 = max(round(($valExp / $maxVal) * 35), 1);
                                        }
                                    @endphp
                                    <td style="text-align: center; vertical-align: bottom; padding: 0 1px;">
                                        @if($isViolet)
                                            <div style="font-size: 4.5pt; color: #5b21b6; font-weight: bold; margin-bottom: 1px;">
                                                {{ $val > 1000 ? round($val/1000, 1).'k' : round($val) }}
                                            </div>
                                            <div style="background: #8b5cf6; width: 10px; height: {{ $h1 }}px; border-radius: 1.5px 1.5px 0 0; margin: 0 auto;"></div>
                                        @else
                                            <div style="font-size: 3.8pt; color: #374151; margin-bottom: 1px; line-height: 1;">
                                                <span style="color:#047857;">{{ $valInc > 1000 ? round($valInc/1000, 1).'k' : round($valInc) }}</span>
                                                <span style="color:#b91c1c;">{{ $valExp > 1000 ? round($valExp/1000, 1).'k' : round($valExp) }}</span>
                                            </div>
                                            <div style="white-space: nowrap; margin: 0 auto; text-align: center;">
                                                <div style="display: inline-block; background: #10b981; width: 6px; height: {{ $h1 }}px; border-radius: 1px 1px 0 0; vertical-align: bottom;"></div>
                                                <div style="display: inline-block; background: #ef4444; width: 6px; height: {{ $h2 }}px; border-radius: 1px 1px 0 0; vertical-align: bottom; margin-left: 1px;"></div>
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td colspan="{{ count($chartMonths) }}" style="padding: 1px 0;">
                                    <div style="border-top: 1px dashed {{ $boxBorder }}; height: 1px; width: 100%;"></div>
                                </td>
                            </tr>
                            <tr>
                                @foreach($chartMonths as $bar)
                                    <td style="text-align: center; font-size: 5pt; color: #4b5563; font-weight: bold; overflow: hidden; white-space: nowrap;">
                                        {{ $bar['label'] }}
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                    @else
                        <div style="text-align: center; color: {{ $titleColor }}; font-size: 6.8pt; padding-top: 20px; font-style: italic;">Sin datos mensuales.</div>
                    @endif
                </div>
            </td>
            <td style="width: 50%; padding-left: 6px; vertical-align: top;">
                <div style="border: 1px solid {{ $boxBorder }}; background: {{ $boxBg }}; padding: 7px 9px; border-radius: 8px; height: 92pt;">
                    <strong style="color: {{ $titleColor }}; font-size: 7.2pt; display: block; margin-bottom: 3px; border-bottom: 1px solid {{ $borderBottomColor }}; padding-bottom: 2px;">
                        {{ $isViolet ? 'Destinos de Asignaciones Principales' : 'Top Categorías (Ingreso y Egreso)' }}
                    </strong>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 3px;">
                        @forelse($topItems as $name => $amt)
                            @php
                                $pct = round(($amt / $totalItemAmount) * 100);
                            @endphp
                            <tr style="height: 12px;">
                                <td style="width: 32%; font-size: 6.5pt; color: #1f2937; font-weight: bold; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; padding: 0.5px 0;">{{ $name }}</td>
                                <td style="width: 48%; padding: 0.5px 0; vertical-align: middle;">
                                    <div style="background: #e2e8f0; height: 5px; border-radius: 2.5px; overflow: hidden; width: 100%;">
                                        <div style="background: {{ $barColor }}; height: 100%; width: {{ $pct }}%; border-radius: 2.5px;"></div>
                                    </div>
                                </td>
                                <td style="width: 20%; text-align: right; font-size: 6.5pt; font-weight: bold; color: #111827; padding: 0.5px 0;">
                                    S/. {{ number_format($amt, 0, ',', '.') }}
                                    <span style="font-size: 5pt; color: #6b7280; font-weight: normal;">({{ $pct }}%)</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td style="text-align: center; color: {{ $titleColor }}; font-size: 6.8pt; padding-top: 20px; font-style: italic;">Sin desglose de categorías.</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </td>
        </tr>
    </table>

    @if(in_array('summary', $report['sections'], true))
        @php
            $summaryColumns = $report['selectedColumns']['summary'] ?? [];
        @endphp
        <section class="section">
            <h2 class="section-title">{{ $report['sectionOptions']['summary']['label'] }}</h2>
            <table class="summary">
                <tr>
                    @foreach($summaryColumns as $column)
                        <td>
                            <span class="summary-label">{{ $report['columnOptions']['summary'][$column] }}</span>
                            <span class="summary-value" style="{{ $summaryValueStyle($column, $report['summary'][$column] ?? '') }}">{{ $report['summary'][$column] ?? '-' }}</span>
                        </td>
                    @endforeach
                </tr>
            </table>
        </section>
    @endif

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

    @php
        $aggregateSection = $report['type'] === 'movimientos' ? 'categories' : 'purposes';
    @endphp
    @if(in_array($aggregateSection, $report['sections'], true))
        @php
            $aggregateColumns = $report['selectedColumns'][$aggregateSection] ?? [];
        @endphp
        <section class="section" style="margin-top: 12pt; page-break-inside: auto;">
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
