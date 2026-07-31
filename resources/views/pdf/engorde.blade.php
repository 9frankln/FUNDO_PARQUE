<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lotes de engorde | {{ $branding->name }} - {{ $fundo->nombre ?? 'Fundo' }}</title>
    <style>
        @page { margin: 24px; size: A4 landscape; }
        body {
            margin: 0;
            color: #243229;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }
        .header {
            margin-bottom: 11px;
            padding-bottom: 9px;
            border-bottom: 4px solid #d97706;
        }
        .eyebrow {
            margin: 0 0 3px;
            color: #b45309;
            font-size: 7px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        h1 {
            margin: 0 0 4px;
            color: #065f46;
            font-size: 17px;
            line-height: 1.1;
        }
        .subtitle { margin: 0; color: #52645a; }
        .meta-table, .data-table { width: 100%; border-collapse: collapse; }
        .meta-table { margin-bottom: 11px; }
        .meta-table td {
            padding: 5px 7px;
            border: 1px solid #d6e5d9;
            background: #f0f7f1;
            vertical-align: top;
        }
        .meta-label { color: #047857; font-weight: bold; }
        .data-table { margin-top: 5px; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th {
            padding: 6px 5px;
            border: 1px solid #064e3b;
            background: #047857;
            color: #fff;
            font-size: 7.4px;
            text-align: left;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 5px;
            border: 1px solid #d6e5d9;
            vertical-align: top;
            word-wrap: break-word;
        }
        .data-table tbody tr:nth-child(even) { background: #f4f8f4; }
        .center { text-align: center; white-space: nowrap; }
        .number { text-align: right; white-space: nowrap; }
        .status {
            display: inline-block;
            min-width: 40px;
            padding: 2px 6px;
            border-radius: 3px;
            background: #d1fae5;
            color: #065f46;
            font-weight: bold;
            text-align: center;
        }
        .status.closed { background: #fef3c7; color: #92400e; }
        .empty { padding: 13px !important; color: #64748b; text-align: center; }
    </style>
</head>
<body>
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
        $columns = array_keys(array_intersect_key($columnLabels, array_flip($selectedColumns ?? [])));
        $peruGeneratedAt = $generatedAt
            ? $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i')
            : 'Sin dato';
    @endphp

    <div class="header">
        <x-brand-logo pdf style="float: right; width: 28px; height: 28px; color: #b45309; object-fit: contain" />
        <p class="eyebrow">{{ $branding->tagline }} | Control de engorde</p>
        <h1>{{ $branding->name }} - Reporte de Lotes de Engorde</h1>
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
            @forelse($lotes as $lote)
                <tr>
                    @forelse($columns as $column)
                        @switch($column)
                            @case('codigo')
                                <td>{{ $lote->codigo }}</td>
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
                                <td class="center"><span class="status {{ $lote->estado === 'cerrado' ? 'closed' : '' }}">{{ ucfirst($lote->estado) }}</span></td>
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
</body>
</html>
