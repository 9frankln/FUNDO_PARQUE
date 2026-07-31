<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Producción de leche | {{ $branding->name }} - {{ $fundo->nombre ?? 'Fundo' }}</title>
    <style>
        @page { margin: 24px; }
        body {
            margin: 0;
            color: #0f2942;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }
        .header {
            margin-bottom: 11px;
            padding: 0 0 9px;
            border-bottom: 4px solid #06b6d4;
        }
        .eyebrow {
            margin: 0 0 3px;
            color: #0891b2;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        h1 {
            margin: 0 0 4px;
            color: #075985;
            font-size: 17px;
            line-height: 1.1;
        }
        .subtitle { margin: 0; color: #475569; }
        .meta-table, .data-table { width: 100%; border-collapse: collapse; }
        .meta-table { margin-bottom: 11px; }
        .meta-table td {
            padding: 5px 7px;
            border: 1px solid #bae6fd;
            background: #e0f2fe;
            vertical-align: top;
        }
        .meta-label { color: #0369a1; font-weight: bold; }
        .data-table { margin-top: 5px; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th {
            padding: 6px 5px;
            border: 1px solid #075985;
            background: #0284c7;
            color: #fff;
            font-size: 7.4px;
            text-align: left;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 5px;
            border: 1px solid #bae6fd;
            vertical-align: top;
            word-wrap: break-word;
        }
        .data-table tbody tr:nth-child(even) { background: #f0f9ff; }
        .number { text-align: right; white-space: nowrap; }
        .center { text-align: center; white-space: nowrap; }
        .photo {
            display: inline-block;
            min-width: 22px;
            padding: 2px 5px;
            border-radius: 3px;
            background: #cffafe;
            color: #155e75;
            font-weight: bold;
            text-align: center;
        }
        .empty { padding: 13px !important; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    @php
        $columnLabels = [
            'fecha' => 'Fecha',
            'turno' => 'Turno',
            'tipo_registro' => 'Tipo de registro',
            'litros_total' => 'Litros totales',
            'cantidad_vacas' => 'Cantidad de vacas',
            'promedio' => 'Promedio (L/vaca)',
            'foto' => 'Foto diaria',
            'observaciones' => 'Observaciones',
            'created_at' => 'Registrado el',
        ];
        $columns = array_keys(array_intersect_key($columnLabels, array_flip($selectedColumns ?? [])));
        $peruGeneratedAt = $generatedAt
            ? $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i')
            : 'Sin dato';
    @endphp

    <div class="header">
        <x-brand-logo pdf style="float: right; width: 28px; height: 28px; color: #0891b2; object-fit: contain" />
        <p class="eyebrow">{{ $branding->tagline }} | Control de ordeño</p>
        <h1>{{ $branding->name }} - Reporte de Producción de Leche</h1>
        <p class="subtitle">
            Fundo: <strong>{{ $fundo->nombre ?? 'Sin dato' }}</strong> |
            Generado el {{ $peruGeneratedAt }} (hora de Perú)
        </p>
    </div>

    <table class="meta-table">
        <tr>
            <td><span class="meta-label">Administrador(es):</span> {{ $administrators ?: 'No asignado' }}</td>
            <td><span class="meta-label">Generado por:</span> {{ $generatedBy ?: 'Sin dato' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="meta-label">Resumen:</span> {{ $reportSummary ?: 'Sin resumen' }}</td>
        </tr>
        <tr>
            <td colspan="2"><span class="meta-label">Filtros aplicados:</span> {{ $filterSummary ?: 'Sin filtros adicionales' }}</td>
        </tr>
    </table>

    <table class="data-table">
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
            @forelse($ordenos as $ordeno)
                @php
                    $cowCount = (int) ($ordeno->cantidad_vacas ?? 0);
                    $average = $cowCount > 0 ? (float) ($ordeno->litros_total ?? 0) / $cowCount : 0;
                    $hasPhoto = (bool) ($ordeno->tiene_foto
                        ?? $ordeno->foto
                        ?? $ordeno->foto_diaria
                        ?? $ordeno->has_daily_photo
                        ?? false);
                @endphp
                <tr>
                    @forelse($columns as $column)
                        @switch($column)
                            @case('fecha')
                                <td class="center">{{ $ordeno->fecha?->format('d/m/Y') ?? 'Sin dato' }}</td>
                                @break
                            @case('turno')
                                <td>{{ $ordeno->turno ? \App\Models\Ordeno::turnoLabel($ordeno->turno) : 'Sin dato' }}</td>
                                @break
                            @case('tipo_registro')
                                <td>{{ $ordeno->tipo_registro ? \App\Models\Ordeno::tipoLabel($ordeno->tipo_registro) : 'Sin dato' }}</td>
                                @break
                            @case('litros_total')
                                <td class="number">{{ number_format((float) ($ordeno->litros_total ?? 0), 2) }} L</td>
                                @break
                            @case('cantidad_vacas')
                                <td class="number">{{ $cowCount }}</td>
                                @break
                            @case('promedio')
                                <td class="number">{{ number_format($average, 2) }} L</td>
                                @break
                            @case('foto')
                                <td class="center"><span class="photo">{{ $hasPhoto ? 'Sí' : 'No' }}</span></td>
                                @break
                            @case('observaciones')
                                <td>{{ $ordeno->observaciones ?: 'Sin observaciones' }}</td>
                                @break
                            @case('created_at')
                                <td class="center">{{ $ordeno->created_at?->copy()->timezone('America/Lima')->format('d/m/Y H:i') ?? 'Sin dato' }}</td>
                                @break
                        @endswitch
                    @empty
                        <td class="empty">Sin columnas seleccionadas</td>
                    @endforelse
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ max(count($columns), 1) }}">No se encontraron registros para los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
