<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }} | {{ $branding->name }}</title>
    <style>
        @page { size: A4 landscape; margin: 22px; }
        body { margin: 0; color: #243229; font-family: DejaVu Sans, sans-serif; font-size: 7.5px; }
        .header { margin-bottom: 9px; padding-bottom: 8px; border-bottom: 4px solid #e11d48; }
        .eyebrow { margin: 0 0 3px; color: #be123c; font-size: 7px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        h1 { margin: 0 0 4px; color: #065f46; font-size: 17px; line-height: 1.1; }
        .subtitle { margin: 0; color: #52645a; }
        .meta-table, .lot-meta, .data-table { width: 100%; border-collapse: collapse; }
        .meta-table { margin-bottom: 10px; }
        .meta-table td { padding: 4px 6px; border: 1px solid #cce5d3; background: #effaf2; vertical-align: top; }
        .meta-label { color: #047857; font-weight: bold; }
        .lot-section { margin-top: 10px; }
        .lot-section.page-break { page-break-before: always; }
        .lot-title { margin: 0; padding: 6px 8px; background: #064e3b; color: #fff; font-size: 12px; }
        .lot-meta { margin-bottom: 5px; }
        .lot-meta td { padding: 4px 5px; border: 1px solid #d6e5d9; background: #f8fbf8; }
        .data-table { table-layout: fixed; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th { padding: 4px 3px; border: 1px solid #881337; background: #be123c; color: #fff; font-size: 6.8px; line-height: 1.15; text-align: left; text-transform: uppercase; word-wrap: break-word; }
        .data-table td { padding: 3px; border: 1px solid #e2e8e3; line-height: 1.2; vertical-align: top; word-wrap: break-word; }
        .data-table tbody tr:nth-child(even) { background: #f7faf7; }
        .data-table.dense th, .data-table.dense td { padding: 2.5px; font-size: 6.3px; }
        .code { font-weight: bold; white-space: nowrap; }
        .center { text-align: center; }
        .number { text-align: right; white-space: nowrap; }
        .positive { color: #047857; font-weight: bold; }
        .negative { color: #be123c; font-weight: bold; }
        .empty { padding: 12px !important; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    @php
        $labels = \App\Support\EngordeReport::COLUMNS;
        $weights = [
            'codigo' => 10, 'nombre' => 12, 'foto_registrada' => 7, 'especie_raza' => 12,
            'sexo_clasificacion' => 15, 'fecha_ingreso' => 9, 'dias_engorde' => 7,
            'peso_inicial' => 8, 'ultimo_pesaje' => 13, 'ganancia_kg' => 8,
            'ganancia_pct' => 8, 'gmd_kg_dia' => 9, 'controles' => 6,
            'estado' => 9, 'observaciones' => 18,
        ];
        $columns = array_keys(array_intersect_key($labels, array_flip($selectedColumns ?? [])));
        $totalWeight = max(array_sum(array_intersect_key($weights, array_flip($columns))), 1);
        $density = count($columns) >= 11 ? 'dense' : '';
        $peruGeneratedAt = $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i');
    @endphp

    <div class="header">
        <x-brand-logo pdf style="float: right; width: 28px; height: 28px; color: #be123c; object-fit: contain" />
        <p class="eyebrow">{{ $branding->tagline }} | Control detallado de engorde</p>
        <h1>{{ $branding->name }} - {{ $title }}</h1>
        <p class="subtitle">Fundo: <strong>{{ $fundo->nombre }}</strong> | Generado el {{ $peruGeneratedAt }} (hora de Perú)</p>
    </div>

    <table class="meta-table">
        <tr>
            <td><span class="meta-label">Administrador(es):</span> {{ $administrators }}</td>
            <td><span class="meta-label">Generado por:</span> {{ $generatedBy }}</td>
        </tr>
        <tr><td colspan="2"><span class="meta-label">Resumen:</span> {{ $reportSummary }}</td></tr>
        <tr><td colspan="2"><span class="meta-label">Selección y filtros:</span> {{ $filterSummary }}</td></tr>
    </table>

    @foreach($lots as $lot)
        @php
            $lotSummary = \App\Support\EngordeReport::summarize(collect([$lot]));
        @endphp
        <section class="lot-section {{ !$loop->first ? 'page-break' : '' }}">
            <h2 class="lot-title">{{ $lot->codigo }} | {{ $lot->nombre ?: 'Sin nombre' }}</h2>
            <table class="lot-meta">
                <tr>
                    <td><strong>Inicio:</strong> {{ $lot->fecha_inicio?->format('d/m/Y') ?? '-' }}</td>
                    <td><strong>Cierre:</strong> {{ $lot->fecha_fin?->format('d/m/Y') ?? 'En curso' }}</td>
                    <td><strong>Estado:</strong> {{ ucfirst($lot->estado) }}</td>
                    <td><strong>Animales:</strong> {{ $lotSummary['animals'] }}</td>
                    <td><strong>Ganancia:</strong> {{ number_format($lotSummary['gain_kg'], 2) }} kg</td>
                </tr>
                @if($lot->observaciones)
                    <tr><td colspan="5"><strong>Observaciones:</strong> {{ $lot->observaciones }}</td></tr>
                @endif
            </table>

            <table class="data-table {{ $density }}">
                <colgroup>
                    @foreach($columns as $column)
                        <col style="width: {{ round(($weights[$column] / $totalWeight) * 100, 2) }}%;">
                    @endforeach
                </colgroup>
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th>{{ $labels[$column] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($lot->animales as $engorde)
                        @php
                            $animal = $engorde->animal;
                            $metrics = $engorde->reportMetrics();
                            $lastWeight = $metrics['last_weight'];
                            $gainClass = $metrics['gain_kg'] > 0 ? 'positive' : ($metrics['gain_kg'] < 0 ? 'negative' : '');
                        @endphp
                        <tr>
                            @foreach($columns as $column)
                                @switch($column)
                                    @case('codigo')
                                        <td class="code">{{ $animal?->arete ?? 'Archivado' }}</td>
                                        @break
                                    @case('nombre')
                                        <td>{{ $animal?->nombre ?: 'Sin nombre' }}</td>
                                        @break
                                    @case('foto_registrada')
                                        <td class="center">{{ $animal?->foto_ruta ? 'Sí' : 'No' }}</td>
                                        @break
                                    @case('especie_raza')
                                        <td>{{ $animal?->especie?->nombre ?? '-' }}<br>{{ $animal?->raza?->nombre ?? '-' }}</td>
                                        @break
                                    @case('sexo_clasificacion')
                                        <td>{{ ucfirst($animal?->genero ?? '-') }}<br>{{ $animal?->clasificacion_edad ?? '-' }} | {{ $animal?->edad_texto ?? '-' }}</td>
                                        @break
                                    @case('fecha_ingreso')
                                        <td class="center">{{ $engorde->fecha_ingreso?->format('d/m/Y') ?? '-' }}</td>
                                        @break
                                    @case('dias_engorde')
                                        <td class="number">{{ $metrics['days_in_fattening'] }}</td>
                                        @break
                                    @case('peso_inicial')
                                        <td class="number">{{ number_format($metrics['initial_weight'], 2) }}</td>
                                        @break
                                    @case('ultimo_pesaje')
                                        <td class="number">{{ number_format($metrics['reference_weight'], 2) }} kg<br>{{ $lastWeight ? $lastWeight->fecha->format('d/m/Y') : 'Sin control adicional' }}</td>
                                        @break
                                    @case('ganancia_kg')
                                        <td class="number {{ $gainClass }}">{{ $metrics['gain_kg'] > 0 ? '+' : '' }}{{ number_format($metrics['gain_kg'], 2) }}</td>
                                        @break
                                    @case('ganancia_pct')
                                        <td class="number {{ $gainClass }}">{{ $metrics['gain_percentage'] !== null ? number_format($metrics['gain_percentage'], 2).' %' : '-' }}</td>
                                        @break
                                    @case('gmd_kg_dia')
                                        <td class="number {{ $gainClass }}">{{ $metrics['average_daily_gain'] !== null ? number_format($metrics['average_daily_gain'], 3) : '-' }}</td>
                                        @break
                                    @case('controles')
                                        <td class="number">{{ (int) $engorde->pesajes_count }}</td>
                                        @break
                                    @case('estado')
                                        <td>{{ $metrics['state_label'] }}</td>
                                        @break
                                    @case('observaciones')
                                        <td>{{ $engorde->observaciones ?: 'Sin observaciones' }}</td>
                                        @break
                                @endswitch
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ max(count($columns), 1) }}" class="empty">Lote sin animales vinculados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endforeach
</body>
</html>
