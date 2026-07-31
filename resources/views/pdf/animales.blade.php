<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Animal | {{ $branding->name }} - {{ $fundo->nombre }}</title>
    <style>
        @page { size: A4 landscape; margin: 22px; }
        body {
            margin: 0;
            color: #243229;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }
        .header {
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 4px solid #10b981;
        }
        .eyebrow {
            margin: 0 0 3px;
            color: #047857;
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
        .meta-table { margin-bottom: 10px; }
        .meta-table td {
            padding: 4px 6px;
            border: 1px solid #cce5d3;
            background: #effaf2;
            vertical-align: top;
        }
        .meta-label { color: #047857; font-weight: bold; }
        .data-table {
            table-layout: fixed;
            margin-top: 4px;
        }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th {
            padding: 5px 4px;
            border: 1px solid #065f46;
            background: #047857;
            color: #fff;
            font-size: 7.4px;
            line-height: 1.15;
            text-align: left;
            text-transform: uppercase;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .data-table td {
            padding: 4px;
            border: 1px solid #d6e5d9;
            line-height: 1.2;
            vertical-align: top;
            word-wrap: break-word;
        }
        .data-table tbody tr:nth-child(even) { background: #f0faf3; }
        .data-table.compact th, .data-table.compact td { padding: 3px; font-size: 7.2px; }
        .data-table.dense th, .data-table.dense td { padding: 2.5px; font-size: 6.6px; }
        .code { font-weight: bold; white-space: nowrap; }
        .center { text-align: center; }
        .number { text-align: right; white-space: nowrap; }
        .date { text-align: center; white-space: nowrap; }
        .badge {
            display: inline-block;
            min-width: 34px;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 6.5px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .badge-active { background: #d1fae5; color: #065f46; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .empty { padding: 13px !important; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    @php
        $columnLabels = [
            'arete' => 'Código del animal',
            'nombre' => 'Nombre',
            'especie' => 'Especie',
            'raza' => 'Raza',
            'genero' => 'Género',
            'edad' => 'Edad',
            'peso' => 'Peso registrado (kg)',
            'estado_reproductivo' => 'Estado reproductivo',
            'tipo_alta' => 'Procedencia',
            'precio_compra' => 'Precio de compra (S/)',
            'activo' => 'Estado',
            'fecha_alta' => 'Fecha de alta',
        ];
        $columnWeights = [
            'arete' => 12,
            'nombre' => 13,
            'especie' => 9,
            'raza' => 11,
            'genero' => 7,
            'edad' => 15,
            'peso' => 9,
            'estado_reproductivo' => 13,
            'tipo_alta' => 11,
            'precio_compra' => 11,
            'activo' => 8,
            'fecha_alta' => 10,
        ];
        $columns = array_keys(array_intersect_key($columnLabels, array_flip($selectedColumns ?? [])));
        $totalWeight = max(array_sum(array_intersect_key($columnWeights, array_flip($columns))), 1);
        $density = count($columns) >= 11 ? 'dense' : (count($columns) >= 9 ? 'compact' : '');
        $peruGeneratedAt = $generatedAt
            ? $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i')
            : 'Sin dato';
    @endphp

    <div class="header">
        <x-brand-logo pdf style="float: right; width: 28px; height: 28px; color: #047857; object-fit: contain" />
        <p class="eyebrow">{{ $branding->tagline }} | Inventario animal</p>
        <h1>{{ $branding->name }} - Reporte de Inventario Animal</h1>
        <p class="subtitle">
            Fundo: <strong>{{ $fundo->nombre }}</strong> |
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

    <table class="data-table {{ $density }}">
        <colgroup>
            @foreach($columns as $column)
                <col style="width: {{ round(($columnWeights[$column] / $totalWeight) * 100, 2) }}%;">
            @endforeach
        </colgroup>
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
            @forelse($animales as $animal)
                <tr>
                    @forelse($columns as $column)
                        @switch($column)
                            @case('arete')
                                <td class="code">{{ $animal->arete }}</td>
                                @break
                            @case('nombre')
                                <td>{{ $animal->nombre ?: '-' }}</td>
                                @break
                            @case('especie')
                                <td>{{ $animal->especie?->nombre ?: '-' }}</td>
                                @break
                            @case('raza')
                                <td>{{ $animal->raza?->nombre ?: '-' }}</td>
                                @break
                            @case('genero')
                                <td>{{ ucfirst($animal->genero) }}</td>
                                @break
                            @case('edad')
                                <td>{{ $animal->edad_texto }}</td>
                                @break
                            @case('peso')
                                <td class="number">{{ $animal->peso !== null ? number_format((float) $animal->peso, 2) : '-' }}</td>
                                @break
                            @case('estado_reproductivo')
                                <td>{{ $animal->estado_reproductivo_label }}</td>
                                @break
                            @case('tipo_alta')
                                <td>{{ $animal->tipo_alta_label }}</td>
                                @break
                            @case('precio_compra')
                                <td class="number">{{ $animal->precio_compra !== null ? 'S/ '.number_format((float) $animal->precio_compra, 2) : '-' }}</td>
                                @break
                            @case('activo')
                                <td class="center"><span class="badge {{ $animal->activo ? 'badge-active' : 'badge-inactive' }}">{{ $animal->activo ? 'Activo' : 'Inactivo' }}</span></td>
                                @break
                            @case('fecha_alta')
                                <td class="date">{{ $animal->fecha_alta?->format('d/m/Y') ?? '-' }}</td>
                                @break
                        @endswitch
                    @empty
                        <td class="empty">Sin columnas seleccionadas</td>
                    @endforelse
                </tr>
            @empty
                <tr>
                    <td class="empty" colspan="{{ max(count($columns), 1) }}">No se encontraron animales para los filtros aplicados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
