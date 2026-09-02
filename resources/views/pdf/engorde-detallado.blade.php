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
        @page { size: A4 landscape; margin: 10mm 8mm 14mm 8mm; }
        body { margin: 0; color: #1e2922; font-family: DejaVu Sans, sans-serif; font-size: 7.5px; }
        .header { margin-bottom: 8px; padding-bottom: 6px; border-bottom: 3.5px solid #be123c; }
        .eyebrow { margin: 0 0 2px; color: #be123c; font-size: 7px; font-weight: bold; letter-spacing: 0.8px; text-transform: uppercase; }
        h1 { margin: 0 0 2px; color: #065f46; font-size: 15px; line-height: 1.1; font-weight: bold; }
        .subtitle { margin: 0; color: #475569; font-size: 7.2px; }
        
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .meta-table td { padding: 3px 5px; border: 1px solid #bbf7d0; background: #f0fdf4; vertical-align: middle; font-size: 7.2px; }
        .meta-label { color: #047857; font-weight: bold; }
        
        .data-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: {{ $tableFontSize }}; page-break-inside: avoid !important; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th { padding: {{ $tableCellPad }}; font-size: {{ $thFontSize }}; line-height: 1.15; word-wrap: break-word; vertical-align: middle; }
        .data-table th.center { text-align: center; }
        .data-table th.left { text-align: left; }
        .data-table td { padding: {{ $tableCellPad }}; line-height: 1.2; vertical-align: middle; word-wrap: break-word; }
        
        .code { font-weight: bold; white-space: nowrap; color: #0f172a; }
        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }
        .font-bold { font-weight: bold; }
        .positive { color: #047857; font-weight: bold; }
        .negative { color: #be123c; font-weight: bold; }
        .neutral { color: #64748b; font-weight: normal; }
        
        .badge { display: inline-block; padding: 1.5px 4px; border-radius: 3px; font-size: 6.2px; font-weight: bold; text-align: center; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-neutral { background: #f1f5f9; color: #475569; }
        .empty { padding: 10px !important; color: #64748b; text-align: center; font-style: italic; }
    </style>
    @include('pdf.partials.styles')
</head>
<body>
    @include('pdf.partials.watermark')
    @php
        $labels = \App\Support\EngordeReport::COLUMNS;
        $columnAlignments = [
            'codigo' => 'center',
            'nombre' => 'left',
            'foto_registrada' => 'center',
            'especie_raza' => 'left',
            'sexo_clasificacion' => 'left',
            'fecha_ingreso' => 'center',
            'dias_engorde' => 'center',
            'peso_inicial' => 'center',
            'ultimo_pesaje' => 'center',
            'ganancia_kg' => 'center',
            'ganancia_pct' => 'center',
            'gmd_kg_dia' => 'center',
            'controles' => 'center',
            'estado' => 'center',
            'observaciones' => 'left',
        ];
        $weights = [
            'codigo' => 10,
            'nombre' => 12,
            'foto_registrada' => 7,
            'especie_raza' => 12,
            'sexo_clasificacion' => 14,
            'fecha_ingreso' => 9,
            'dias_engorde' => 8,
            'peso_inicial' => 10,
            'ultimo_pesaje' => 13,
            'ganancia_kg' => 9,
            'ganancia_pct' => 8,
            'gmd_kg_dia' => 9,
            'controles' => 7,
            'estado' => 9,
            'observaciones' => 16,
        ];
        $columns = array_keys(array_intersect_key($labels, array_flip($selectedColumns ?? [])));
        $totalWeight = max(array_sum(array_intersect_key($weights, array_flip($columns))), 1);
        $density = count($columns) >= 11 ? 'dense' : '';
        $peruGeneratedAt = $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i');
        $currentUser = auth()->user();
        $userDoc = $currentUser?->dni ? 'DNI: '.$currentUser->dni : ($currentUser?->username ? '@'.$currentUser->username : 'Sistema');
        $colCount = max(count($columns), 1);
    @endphp

    <div class="header" style="border-bottom-color: {{ $pdfConfig->accentColor() }};">
        @if($pdfConfig->showHeaderLogo())
            <x-brand-logo pdf style="float: right; width: 26px; height: 26px; color: {{ $pdfConfig->accentColor() }}; object-fit: contain" />
        @endif
        <p class="eyebrow" style="color: {{ $pdfConfig->accentColor() }};">{{ $branding->tagline }} | REPORTE DETALLADO DE ENGORDE</p>
        <h1 style="color: {{ $pdfConfig->accentDark() }};">{{ $branding->name }} &mdash; {{ $title }}</h1>
        <p class="subtitle">
            Fundo: <strong>{{ $fundo->nombre }}</strong> &bull;
            Generado el {{ $peruGeneratedAt }} (hora de Perú)
        </p>
    </div>

    <div class="summary-card" style="border: 1px solid {{ $pdfConfig->accentBorder() }}; border-radius: {{ $pdfConfig->tableBorderRadius() }}; overflow: hidden; background-color: {{ $pdfConfig->accentSoft() }}; margin-bottom: 7pt;">
        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; background: transparent;">
            <tr>
                <td style="width: 50%; border: none; border-right: 1px solid {{ $pdfConfig->accentBorder() }}; border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; padding: 3pt 5pt; font-size: 7px; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Generado por:</strong> {{ $generatedBy ?: 'Sin dato' }}</td>
                <td style="width: 50%; border: none; border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; padding: 3pt 5pt; font-size: 7px; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Usuario / Documento:</strong> {{ $userDoc }}</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; @if(!empty($filterSummary)) border-bottom: 1px solid {{ $pdfConfig->accentBorder() }}; @endif padding: 3pt 5pt; font-size: 7px; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Resumen:</strong> {{ $reportSummary ?: 'Sin resumen' }}</td>
            </tr>
            @if(!empty($filterSummary))
                <tr>
                    <td colspan="2" style="border: none; padding: 3pt 5pt; font-size: 7px; color: #1e293b;"><strong style="color: {{ $pdfConfig->accentDark() }};">Filtros aplicados:</strong> {{ $filterSummary }}</td>
                </tr>
            @endif
        </table>
    </div>

    @foreach($lots as $lot)
        @php
            $lotSummary = \App\Support\EngordeReport::summarize(collect([$lot]));
            $lotTheme = $pdfConfig->tableThemeForIndex($loop->index);
        @endphp
        <table class="data-table lot-themed-table {{ $density }}"
               style="width: 100%; border-collapse: collapse; border: 1.2px solid {{ $lotTheme['border'] }}; margin-top: 6pt; margin-bottom: 8pt; background: #ffffff; page-break-inside: avoid !important;">
            <colgroup>
                @foreach($columns as $column)
                    <col style="width: {{ round(($weights[$column] / $totalWeight) * 100, 2) }}%;">
                @endforeach
            </colgroup>
            <thead>
                {{-- Fila 1: Título del Lote encapsulado --}}
                <tr style="page-break-inside: avoid; page-break-after: avoid;">
                    <th colspan="{{ $colCount }}" style="padding: 0 !important; border: none !important; border-bottom: 1.5px solid {{ $lotTheme['border'] }} !important; background-color: {{ $lotTheme['soft'] }} !important; font-weight: normal !important; text-transform: none !important;">
                        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; background: transparent;">
                            <tr>
                                <td style="border: none; padding: 3.5pt 5pt; text-align: left; vertical-align: middle;">
                                    <span style="display: inline-block; padding: 1pt 4.5pt; background: {{ $lotTheme['primary'] }}; border-radius: 3pt; font-weight: 800; font-size: 8px; letter-spacing: 0.4px; margin-right: 4pt; color: #ffffff;">
                                        LOTE #{{ $loop->iteration }}
                                    </span>
                                    <strong style="color: #0f172a; letter-spacing: 0.2px; font-weight: 800; font-size: 9.5px;">
                                        {{ $lot->codigo }} &bull; {{ $lot->nombre ?: 'Sin nombre' }}
                                    </strong>
                                </td>
                                <td style="border: none; padding: 3.5pt 5pt; text-align: right; vertical-align: middle; font-size: 7.5px; color: {{ $lotTheme['dark'] }};">
                                    <span style="color: {{ $lotTheme['dark'] }}; font-weight: bold;">{{ ucfirst($lot->estado) }}</span> &bull; Total: <strong>{{ $lotSummary['animals'] }} animales</strong> &bull; Ganancia: <strong style="color: {{ $lotTheme['dark'] }};">{{ number_format($lotSummary['gain_kg'], 2) }} kg</strong>
                                </td>
                            </tr>
                        </table>
                    </th>
                </tr>

                {{-- Fila 2: Metadatos del Lote --}}
                <tr style="page-break-inside: avoid; page-break-after: avoid;">
                    <th colspan="{{ $colCount }}" style="padding: 0 !important; border: none !important; border-bottom: 1px solid {{ $lotTheme['border'] }} !important; background: #ffffff !important; font-weight: normal !important; text-transform: none !important;">
                        <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; background-color: #ffffff;">
                            <tr>
                                <td style="width: 20%; border: none; border-right: 1px solid {{ $lotTheme['border'] }}; padding: 2.5pt 4.5pt; font-size: 7px; color: #1e293b;">
                                    <span style="color: {{ $lotTheme['dark'] }}; font-weight: bold;">Inicio:</span> {{ $lot->fecha_inicio?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td style="width: 20%; border: none; border-right: 1px solid {{ $lotTheme['border'] }}; padding: 2.5pt 4.5pt; font-size: 7px; color: #1e293b;">
                                    <span style="color: {{ $lotTheme['dark'] }}; font-weight: bold;">Cierre:</span> {{ $lot->fecha_fin?->format('d/m/Y') ?? 'En curso' }}
                                </td>
                                <td style="width: 20%; border: none; border-right: 1px solid {{ $lotTheme['border'] }}; padding: 2.5pt 4.5pt; font-size: 7px; color: #1e293b;">
                                    <span style="color: {{ $lotTheme['dark'] }}; font-weight: bold;">Estado:</span> {{ ucfirst($lot->estado) }}
                                </td>
                                <td style="width: 20%; border: none; border-right: 1px solid {{ $lotTheme['border'] }}; padding: 2.5pt 4.5pt; font-size: 7px; color: #1e293b;">
                                    <span style="color: {{ $lotTheme['dark'] }}; font-weight: bold;">Total animales:</span> {{ $lotSummary['animals'] }}
                                </td>
                                <td style="width: 20%; border: none; padding: 2.5pt 4.5pt; font-size: 7px; color: #1e293b;">
                                    <span style="color: {{ $lotTheme['dark'] }}; font-weight: bold;">Ganancia Lote:</span> <strong style="color: {{ $lotTheme['dark'] }};">{{ number_format($lotSummary['gain_kg'], 2) }} kg</strong>
                                </td>
                            </tr>
                            @if($lot->observaciones)
                                <tr>
                                    <td colspan="5" style="border: none; border-top: 0.8px solid {{ $lotTheme['border'] }}; padding: 2.5pt 4.5pt; font-size: 6.8px; background: #fafafa; color: #334155;">
                                        <strong style="color: {{ $lotTheme['dark'] }};">Observaciones:</strong> {{ $lot->observaciones }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </th>
                </tr>

                {{-- Fila 3: Column Headers --}}
                <tr style="page-break-inside: avoid;">
                    @foreach($columns as $column)
                        <th class="{{ $columnAlignments[$column] ?? 'left' }}"
                            style="background-color: {{ $lotTheme['soft'] }} !important; border: none !important; border-bottom: 1.5px solid {{ $lotTheme['border'] }} !important; border-right: 1px solid {{ $lotTheme['border'] }} !important; color: {{ $lotTheme['dark'] }} !important; font-weight: bold !important; text-transform: uppercase;">
                            {{ $labels[$column] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($lot->animales as $engorde)
                    @php
                        $animal = $engorde->animal;
                        $metrics = $engorde->reportMetrics();
                        $lastWeight = $metrics['last_weight'];
                        $gainClass = $metrics['gain_kg'] > 0 ? 'positive' : ($metrics['gain_kg'] < 0 ? 'negative' : 'neutral');
                        $rowBg = $loop->even ? ($lotTheme['row_even'] ?? '#fafafa') : '#ffffff';
                    @endphp
                    <tr style="background-color: {{ $rowBg }}; page-break-inside: avoid;">
                        @foreach($columns as $column)
                            <td class="{{ $columnAlignments[$column] ?? 'left' }}"
                                style="border: none !important; border-bottom: 1px solid {{ $lotTheme['border'] }} !important; border-right: 1px solid {{ $lotTheme['border'] }} !important; color: #1e293b;">
                                @switch($column)
                                    @case('codigo')
                                        <span class="code" style="color: {{ $lotTheme['dark'] }}; font-weight: bold;">{{ $animal?->arete ?? 'Archivado' }}</span>
                                        @break
                                    @case('nombre')
                                        <span class="font-bold" style="color: #0f172a;">{{ $animal?->nombre ?: 'Sin nombre' }}</span>
                                        @break
                                    @case('foto_registrada')
                                        <span class="badge {{ $animal?->foto_ruta ? 'badge-success' : 'badge-neutral' }}">
                                            {{ $animal?->foto_ruta ? 'Sí' : 'No' }}
                                        </span>
                                        @break
                                    @case('especie_raza')
                                        <span>{{ $animal?->especie?->nombre ?? '-' }}</span><br><span class="sub-text" style="color: #64748b;">{{ $animal?->raza?->nombre ?? '-' }}</span>
                                        @break
                                    @case('sexo_clasificacion')
                                        <span>{{ ucfirst($animal?->genero ?? '-') }}</span><br><span class="sub-text" style="color: #64748b;">{{ $animal?->clasificacion_edad ?? '-' }} | {{ $animal?->edad_texto ?? '-' }}</span>
                                        @break
                                    @case('fecha_ingreso')
                                        <span>{{ $engorde->fecha_ingreso?->format('d/m/Y') ?? '-' }}</span>
                                        @break
                                    @case('dias_engorde')
                                        <strong style="color: #0f172a;">{{ (int) $metrics['days_in_fattening'] }}</strong>
                                        @break
                                    @case('peso_inicial')
                                        <strong style="color: #0f172a;">{{ number_format($metrics['initial_weight'], 2) }} kg</strong>
                                        @break
                                    @case('ultimo_pesaje')
                                        <strong style="color: #0f172a;">{{ number_format($metrics['reference_weight'], 2) }} kg</strong>
                                        @if($lastWeight)
                                            <br><span class="sub-text" style="color: #64748b;">{{ $lastWeight->fecha->format('d/m/Y') }}</span>
                                        @else
                                            <br><span class="sub-text" style="color: #94a3b8;">Inicial</span>
                                        @endif
                                        @break
                                    @case('ganancia_kg')
                                        <span class="{{ $gainClass }}">{{ $metrics['gain_kg'] > 0 ? '+' : '' }}{{ number_format($metrics['gain_kg'], 2) }} kg</span>
                                        @break
                                    @case('ganancia_pct')
                                        <span class="{{ $gainClass }}">{{ $metrics['gain_percentage'] !== null ? ($metrics['gain_percentage'] > 0 ? '+' : '').number_format($metrics['gain_percentage'], 2).' %' : '-' }}</span>
                                        @break
                                    @case('gmd_kg_dia')
                                        <span class="{{ $gainClass }}">{{ $metrics['average_daily_gain'] !== null ? ($metrics['average_daily_gain'] > 0 ? '+' : '').number_format($metrics['average_daily_gain'], 3) : '-' }}</span>
                                        @break
                                    @case('controles')
                                        <strong style="color: #0f172a;">{{ (int) $engorde->pesajes_count }}</strong>
                                        @break
                                    @case('estado')
                                        <span class="badge {{ $engorde->estado === 'engorde_activo' ? 'badge-success' : 'badge-warning' }}">
                                            {{ $metrics['state_label'] }}
                                        </span>
                                        @break
                                    @case('observaciones')
                                        <span class="sub-text" style="color: #475569;">{{ $engorde->observaciones ?: '-' }}</span>
                                        @break
                                @endswitch
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $colCount }}" class="empty" style="border: none !important; text-align: center;">
                            Lote sin animales vinculados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

    @if($includeSignatures ?? true)
        @include('pdf.partials.signatures')
    @endif
    @include('pdf.partials.footer')
</body>
</html>
