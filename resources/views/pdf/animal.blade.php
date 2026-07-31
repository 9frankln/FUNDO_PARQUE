<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha integral | {{ $branding->name }} - {{ $animal->arete }}</title>
    <style>
        @page { size: A4 landscape; margin: 14px 16px 24px; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: #243229;
            font-family: DejaVu Sans, sans-serif;
            font-size: 6.4px;
            line-height: 1.16;
        }
        .header {
            margin-bottom: 4px;
            padding-bottom: 4px;
            border-bottom: 2px solid #4f7a69;
        }
        .eyebrow {
            margin: 0 0 2px;
            color: #35564b;
            font-size: 5.6px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        h1 {
            margin: 0 0 2px;
            color: #2f493f;
            font-size: 13px;
            line-height: 1.1;
        }
        .subtitle { margin: 0; color: #52645a; }
        .meta-table, .profile-table, .facts-table, .summary-table, .data-table, .milk-summary, .overview-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table { margin-bottom: 4px; }
        .meta-table td {
            width: 50%;
            padding: 2px 4px;
            border: 1px solid #cce5d3;
            background: #effaf2;
            vertical-align: top;
        }
        .meta-label { color: #35564b; font-weight: bold; }
        .section { margin-top: 4px; }
        .section-title {
            margin: 0 0 3px;
            padding: 3px 5px;
            border-left: 3px solid #4f7a69;
            background: #eff4f1;
            color: #2f493f;
            font-size: 7.6px;
            line-height: 1.15;
            page-break-after: avoid;
        }
        .section-title.productive { border-color: #b78639; background: #fbf5e9; color: #6f5124; }
        .section-title.clinical { border-color: #a7666f; background: #faeff1; color: #704149; }
        .section-title.preventive { border-color: #4c7c94; background: #eef6f9; color: #365b6c; }
        .section-title.reproductive { border-color: #756a94; background: #f3f1f8; color: #554d70; }
        .section-title.milk { border-color: #477d76; background: #edf7f5; color: #315b55; }
        .overview-table { table-layout: fixed; page-break-inside: avoid; }
        .overview-table > tbody > tr > td { width: 50%; padding: 0 4px 0 0; vertical-align: top; }
        .overview-table > tbody > tr > td + td { padding: 0 0 0 4px; }
        .profile-table {
            table-layout: fixed;
            border: 1px solid #cce5d3;
            background: #fbfefc;
        }
        .profile-table > tbody > tr > td { padding: 4px; vertical-align: middle; }
        .photo-cell {
            width: 34%;
            border-right: 1px solid #d6e5d9;
            text-align: center;
        }
        .animal-photo {
            width: 124px;
            height: 88px;
            border: 1px solid #b9d5c1;
            border-radius: 7px;
            background: #edf8f0;
            object-fit: contain;
        }
        .photo-placeholder {
            width: 124px;
            height: 88px;
            margin: 0 auto;
            padding-top: 37px;
            border: 1px dashed #9eb8a6;
            border-radius: 7px;
            background: #f4f8f5;
            color: #718277;
            font-size: 6.5px;
            font-weight: bold;
            letter-spacing: .7px;
            text-align: center;
            text-transform: uppercase;
        }
        .identity-name { margin: 0 0 1px; color: #2f493f; font-size: 10px; line-height: 1.1; }
        .identity-code { margin: 0 0 3px; color: #4f7a69; font-size: 7px; font-weight: bold; letter-spacing: .5px; }
        .identity-table { width: 100%; border-collapse: collapse; }
        .identity-table td { padding: 1.6px 2px; border-bottom: 1px solid #edf2ee; vertical-align: top; }
        .identity-table td:first-child { width: 42%; color: #52645a; font-weight: bold; }
        .badge {
            display: inline-block;
            min-width: 42px;
            padding: 1px 4px;
            border-radius: 10px;
            font-size: 6px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }
        .badge-active { background: #dfeae3; color: #2f493f; }
        .badge-inactive { background: #fee2e2; color: #991b1b; }
        .summary-table { margin-top: 3px; table-layout: fixed; }
        .summary-table td {
            padding: 3px 4px;
            border: 1px solid #d6e5d9;
            background: #f8fbf8;
            text-align: center;
        }
        .summary-label { display: block; color: #64748b; font-size: 5.6px; font-weight: bold; text-transform: uppercase; }
        .summary-value { display: block; margin-top: 1px; color: #2f493f; font-size: 6.8px; font-weight: bold; }
        .facts-table { table-layout: fixed; }
        .facts-table td { padding: 2.5px 4px; border: 1px solid #dce9df; vertical-align: top; }
        .facts-label { width: 18%; background: #f4f7f5; color: #35564b; font-weight: bold; }
        .facts-value { width: 32%; }
        .overview-table .facts-label { width: 40%; }
        .overview-table .facts-value { width: 60%; }
        .note, .origin-note {
            margin-top: 3px;
            padding: 3px 5px;
            border: 1px solid #cbd9cf;
            border-radius: 4px;
            background: #f5f8f6;
            color: #3b5145;
        }
        .data-table { table-layout: fixed; }
        .data-table thead { display: table-header-group; }
        .data-table tr { page-break-inside: avoid; }
        .data-table th {
            padding: 2px 2.5px;
            border: 1px solid #2f493f;
            background: #35564b;
            color: #fff;
            font-size: 5.5px;
            line-height: 1.1;
            text-align: left;
            text-transform: uppercase;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .data-table.clinical th,
        .data-table.preventive th,
        .data-table.reproductive th,
        .data-table.milk th { border-color: #2f493f; background: #35564b; }
        .data-table td {
            padding: 2px 2.5px;
            border: 1px solid #dce7df;
            line-height: 1.12;
            vertical-align: top;
            word-wrap: break-word;
        }
        .data-table tbody tr:nth-child(even) { background: #f4faf5; }
        .date, .number { white-space: nowrap; }
        .number { text-align: right; }
        .empty {
            padding: 6px !important;
            border: 1px solid #dce7df;
            background: #f8fbf8;
            color: #64748b;
            font-style: italic;
            text-align: center;
        }
        .milk-summary { margin-bottom: 4px; table-layout: fixed; }
        .milk-summary td {
            padding: 2.5px 2px;
            border: 1px solid #cbd9cf;
            background: #eef5f0;
            text-align: center;
        }
        .milk-summary strong { display: block; color: #52645a; font-size: 5.5px; text-transform: uppercase; }
        .milk-summary span { display: block; margin-top: 1px; color: #2f493f; font-size: 6.8px; font-weight: bold; }
        .footer {
            position: fixed;
            right: 0;
            bottom: -19px;
            left: 0;
            padding-top: 3px;
            border-top: 1px solid #d6e5d9;
            color: #94a3b8;
            font-size: 5.8px;
        }
        .page-number { float: right; }
        .page-number::after { content: counter(page); }
    </style>
</head>
<body>
    @php
        $interventionLabels = [
            'vacuna' => 'Vacuna',
            'desparasitante_interno' => 'Desparasitante interno',
            'desparasitante_externo' => 'Desparasitante externo',
            'vitamina' => 'Vitamina',
        ];
        $isFemale = $animal->genero === 'hembra';
        $hasMilkSection = $animal->apta_ordeno || $milkRecords->isNotEmpty();
        $generatedInPeru = $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i');
        $selectedSections = $selectedSections ?? ['identity', 'productive', 'clinical', 'preventive', 'reproductive', 'milk'];
        $allReportColumns = \App\Livewire\Animal\Show::reportColumnOptions();
        $selectedColumns = $selectedColumns ?? collect($allReportColumns)->map(fn ($columns) => array_keys($columns))->all();
        $hasField = fn (string $section, string $field): bool => in_array($field, $selectedColumns[$section] ?? [], true);
        $parallelOverview = in_array('identity', $selectedSections, true) && in_array('productive', $selectedSections, true);
        $clinicalWeights = ['date' => 7, 'classification' => 12, 'status' => 10, 'diagnosis' => 22, 'treatment' => 28, 'medication' => 15, 'dosage' => 12, 'evidence' => 8];
        $preventiveWeights = ['date' => 8, 'intervention' => 14, 'product' => 20, 'purpose' => 24, 'dose' => 10, 'next_dose' => 18, 'responsible' => 18, 'observations' => 28, 'evidence' => 9];
        $reproductiveWeights = ['date' => 8, 'birth_type' => 10, 'maternal_condition' => 14, 'calf' => 14, 'calf_sex' => 9, 'calf_status' => 13, 'birth_weight' => 10, 'observations' => 30];
        $milkWeights = ['date' => 8, 'shift' => 8, 'liters' => 8, 'exception' => 22, 'justification' => 38];
        $sectionNumber = 0;
    @endphp

    <div class="header">
        <x-brand-logo pdf style="float: right; width: 24px; height: 24px; color: #35564b; object-fit: contain" />
        <p class="eyebrow">{{ $branding->tagline }} | Ficha individual integral</p>
        <h1>{{ $branding->name }} - Reporte Integral del Animal</h1>
        <p class="subtitle">Ejemplar: <strong>{{ $animal->arete }} | {{ $animal->nombre ?: 'Sin nombre' }}</strong> &nbsp;·&nbsp; Fundo: <strong>{{ $fundo->nombre }}</strong></p>
    </div>

    <table class="meta-table">
        <tr>
            <td><span class="meta-label">Administrador(es):</span> {{ $administrators ?: 'No asignado' }}</td>
            <td><span class="meta-label">Generado por:</span> {{ $generatedBy ?: 'Sin dato' }}</td>
        </tr>
        <tr>
            <td><span class="meta-label">Código del animal:</span> {{ $animal->arete }}</td>
            <td><span class="meta-label">Fecha del reporte:</span> {{ $generatedInPeru }} (hora de Perú)</td>
        </tr>
        <tr>
            <td colspan="2"><span class="meta-label">Contenido:</span> {{ $reportSummary }}</td>
        </tr>
    </table>

    @if($parallelOverview)
    <table class="overview-table">
        <tr>
            <td>
    @endif

    @if(in_array('identity', $selectedSections, true))
    @php
        $sectionNumber++;
    @endphp
    <section class="section">
        <h2 class="section-title">{{ $sectionNumber }}. Identificación y fotografía</h2>
        <table class="profile-table">
            <tr>
                @if($hasField('identity', 'photo'))
                <td class="photo-cell">
                    @if($photoDataUri)
                        <img class="animal-photo" src="{{ $photoDataUri }}" alt="Fotografía de {{ $animal->arete }}">
                    @else
                        <div class="photo-placeholder">Sin fotografía registrada</div>
                    @endif
                </td>
                @endif
                <td @if(!$hasField('identity', 'photo')) colspan="2" @endif>
                    @if($hasField('identity', 'name'))
                        <h3 class="identity-name">{{ $animal->nombre ?: 'Sin nombre' }}</h3>
                    @endif
                    @if($hasField('identity', 'code'))
                        <p class="identity-code">{{ $animal->arete }}</p>
                    @endif
                    <table class="identity-table">
                        @if($hasField('identity', 'species'))<tr><td>Especie</td><td>{{ $animal->especie?->nombre ?? '-' }}</td></tr>@endif
                        @if($hasField('identity', 'breed'))<tr><td>Raza</td><td>{{ $animal->raza?->nombre ?? '-' }}</td></tr>@endif
                        @if($hasField('identity', 'sex'))<tr><td>Sexo</td><td>{{ $animal->genero === 'hembra' ? 'Hembra' : 'Macho' }}</td></tr>@endif
                        @if($hasField('identity', 'status'))<tr><td>Estado</td><td><span class="badge {{ $animal->activo ? 'badge-active' : 'badge-inactive' }}">{{ $animal->activo ? 'Activo' : 'Inactivo' }}</span></td></tr>@endif
                        @if($hasField('identity', 'birth_date'))<tr><td>Nacimiento</td><td>{{ $animal->fecha_nacimiento?->format('d/m/Y') ?? 'Sin fecha exacta' }}</td></tr>@endif
                    </table>
                </td>
            </tr>
        </table>
        @if(collect(['classification', 'age', 'weight', 'reproductive_status'])->contains(fn ($field) => $hasField('identity', $field)))
        <table class="summary-table">
            <tr>
                @if($hasField('identity', 'classification'))<td><span class="summary-label">Clasificación</span><span class="summary-value">{{ $animal->clasificacion_edad }}</span></td>@endif
                @if($hasField('identity', 'age'))<td><span class="summary-label">Edad</span><span class="summary-value">{{ $animal->edad_texto }}</span></td>@endif
                @if($hasField('identity', 'weight'))<td><span class="summary-label">Peso registrado</span><span class="summary-value">{{ $animal->peso ? number_format((float) $animal->peso, 2).' kg' : '-' }}</span></td>@endif
                @if($hasField('identity', 'reproductive_status'))<td><span class="summary-label">Estado reproductivo</span><span class="summary-value">{{ $animal->estado_reproductivo_label }}</span></td>@endif
            </tr>
        </table>
        @endif
    </section>
    @endif

    @if($parallelOverview)
            </td>
            <td>
    @endif

    @if(in_array('productive', $selectedSections, true))
    @php
        $sectionNumber++;
    @endphp
    <section class="section">
        <h2 class="section-title productive">{{ $sectionNumber }}. Datos productivos y de alta</h2>
        @php
            $productiveFacts = collect([
                'admission_type' => ['label' => 'Procedencia', 'value' => $animal->tipo_alta === 'parto' ? 'Nacimiento / parto' : ucfirst($animal->tipo_alta)],
                'admission_date' => ['label' => 'Fecha de alta', 'value' => $animal->fecha_alta->format('d/m/Y')],
                'productive_status' => ['label' => 'Estado productivo', 'value' => ucfirst(str_replace('_', ' ', $animal->estado_productivo ?? '-'))],
                'milking_eligible' => ['label' => 'Apta para ordeño', 'value' => $animal->apta_ordeno ? 'Sí' : 'No'],
                'dentition' => ['label' => 'Dentición estimada', 'value' => $animal->denticion_estimada ?? 'No aplica'],
                'purchase_price' => ['label' => 'Precio de compra', 'value' => $animal->tipo_alta === 'compra' && $animal->precio_compra !== null ? 'S/ '.number_format((float) $animal->precio_compra, 2) : 'No aplica'],
            ])->filter(fn ($value, $field) => $hasField('productive', $field));
        @endphp
        @if($productiveFacts->isNotEmpty())
        <table class="facts-table">
            @foreach($productiveFacts->chunk($parallelOverview ? 1 : 2) as $facts)
                <tr>
                    @foreach($facts as $fact)
                        <td class="facts-label">{{ $fact['label'] }}</td><td class="facts-value">{{ $fact['value'] }}</td>
                    @endforeach
                    @if(! $parallelOverview && $facts->count() === 1)<td class="facts-label"></td><td class="facts-value"></td>@endif
                </tr>
            @endforeach
        </table>
        @endif

        @if($hasField('productive', 'observations') && $animal->observaciones)
            <div class="note"><strong>Observaciones generales:</strong> {{ $animal->observaciones }}</div>
        @endif

        @if($hasField('productive', 'origin') && $animal->partosCria->isNotEmpty())
            @foreach($animal->partosCria as $origin)
                <div class="origin-note">
                    <strong>Registro de origen:</strong>
                    Nació el {{ $origin->fecha_parto->format('d/m/Y') }}
                    @if($origin->madre) de la madre {{ $origin->madre->arete }} @endif
                    mediante parto {{ ucfirst(str_replace('_', ' ', $origin->tipo_parto)) }}.
                    Peso al nacer: {{ $origin->cria_peso_nacer ? number_format((float) $origin->cria_peso_nacer, 2).' kg' : '-' }}.
                </div>
            @endforeach
        @endif
    </section>
    @endif

    @if($parallelOverview)
            </td>
        </tr>
    </table>
    @endif

    @if(in_array('clinical', $selectedSections, true))
    @php
        $sectionNumber++;
    @endphp
    <section class="section">
        <h2 class="section-title clinical">{{ $sectionNumber }}. Historial clínico</h2>
        @if($animal->sanidadRegistros->isNotEmpty())
            @php
                $clinicalColumns = array_values(array_intersect(array_keys($allReportColumns['clinical']), $selectedColumns['clinical'] ?? []));
                $clinicalTotalWeight = max(array_sum(array_intersect_key($clinicalWeights, array_flip($clinicalColumns))), 1);
            @endphp
            <table class="data-table clinical">
                <colgroup>
                    @foreach($clinicalColumns as $column)
                        <col width="{{ round(($clinicalWeights[$column] / $clinicalTotalWeight) * 100, 2) }}%" style="width: {{ round(($clinicalWeights[$column] / $clinicalTotalWeight) * 100, 2) }}%">
                    @endforeach
                </colgroup>
                <thead><tr>@foreach($clinicalColumns as $column)<th>{{ $allReportColumns['clinical'][$column] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($animal->sanidadRegistros as $record)
                        <tr>
                            @foreach($clinicalColumns as $column)
                                @switch($column)
                                    @case('date')
                                        <td class="date">{{ $record->fecha_evento->format('d/m/Y') }}</td>
                                        @break
                                    @case('classification')
                                        <td>{{ ucfirst(str_replace('_', ' ', $record->clasificacion)) }}</td>
                                        @break
                                    @case('status')
                                        <td><strong>{{ ucfirst(str_replace('_', ' ', $record->estado_clinico)) }}</strong></td>
                                        @break
                                    @case('diagnosis')
                                        <td>{{ $record->sintomas_diagnostico }}</td>
                                        @break
                                    @case('treatment')
                                        <td>{{ $record->tratamiento ?: 'Sin tratamiento registrado' }}</td>
                                        @break
                                    @case('medication')
                                        <td>{{ $record->medicamento?->nombre ?? '-' }}</td>
                                        @break
                                    @case('dosage')
                                        <td>{{ $record->dosis_via ?: '-' }}</td>
                                        @break
                                    @case('evidence')
                                        <td>{{ $record->fotos->isNotEmpty() ? $record->fotos->count().' foto(s)' : ($record->evidencia_ruta ? 'Adjunto anterior' : 'No adjunta') }}</td>
                                        @break
                                @endswitch
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">Sin eventos clínicos registrados.</div>
        @endif
    </section>
    @endif

    @if(in_array('preventive', $selectedSections, true))
    @php
        $sectionNumber++;
    @endphp
    <section class="section">
        <h2 class="section-title preventive">{{ $sectionNumber }}. Profilaxis y vacunas</h2>
        @if($profilaxis->isNotEmpty())
            @php
                $preventiveColumns = array_values(array_intersect(array_keys($allReportColumns['preventive']), $selectedColumns['preventive'] ?? []));
                $preventiveTotalWeight = max(array_sum(array_intersect_key($preventiveWeights, array_flip($preventiveColumns))), 1);
            @endphp
            <table class="data-table preventive">
                <colgroup>
                    @foreach($preventiveColumns as $column)
                        <col width="{{ round(($preventiveWeights[$column] / $preventiveTotalWeight) * 100, 2) }}%" style="width: {{ round(($preventiveWeights[$column] / $preventiveTotalWeight) * 100, 2) }}%">
                    @endforeach
                </colgroup>
                <thead><tr>@foreach($preventiveColumns as $column)<th>{{ $allReportColumns['preventive'][$column] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($profilaxis as $record)
                        <tr>
                            @foreach($preventiveColumns as $column)
                                @switch($column)
                                    @case('date')
                                        <td class="date">{{ $record->fecha_aplicacion->format('d/m/Y') }}</td>
                                        @break
                                    @case('intervention')
                                        <td><strong>{{ $interventionLabels[$record->tipo_intervencion] ?? ucfirst(str_replace('_', ' ', $record->tipo_intervencion)) }}</strong></td>
                                        @break
                                    @case('product')
                                        <td>{{ $record->producto_marca }}</td>
                                        @break
                                    @case('purpose')
                                        <td>{{ $record->proposito ?: '-' }}</td>
                                        @break
                                    @case('dose')
                                        <td>{{ $record->dosis ?: '-' }}</td>
                                        @break
                                    @case('next_dose')
                                        <td>{{ $record->fechasDosisProgramadas()->isEmpty() ? 'Única dosis' : $record->fechasDosisProgramadas()->map(fn ($scheduledDate, $doseIndex) => 'Dosis '.($doseIndex + 2).': '.$scheduledDate->format('d/m/Y'))->join(' | ') }}</td>
                                        @break
                                    @case('responsible')
                                        <td>{{ $record->responsable ?: '-' }}</td>
                                        @break
                                    @case('observations')
                                        <td>{{ $record->observaciones ?: '-' }}</td>
                                        @break
                                    @case('evidence')
                                        <td>{{ $record->fotos->isNotEmpty() ? $record->fotos->count().' foto(s)' : 'No' }}</td>
                                        @break
                                @endswitch
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">Sin registros de profilaxis o vacunación.</div>
        @endif
    </section>
    @endif

    @if(in_array('reproductive', $selectedSections, true) && $isFemale)
        @php
            $sectionNumber++;
        @endphp
        <section class="section">
            <h2 class="section-title reproductive">{{ $sectionNumber }}. Historial reproductivo: partos y crías</h2>
            @if($animal->partosMadre->isNotEmpty())
                @php
                    $reproductiveColumns = array_values(array_intersect(array_keys($allReportColumns['reproductive']), $selectedColumns['reproductive'] ?? []));
                    $reproductiveTotalWeight = max(array_sum(array_intersect_key($reproductiveWeights, array_flip($reproductiveColumns))), 1);
                @endphp
                <table class="data-table reproductive">
                    <colgroup>
                        @foreach($reproductiveColumns as $column)
                            <col width="{{ round(($reproductiveWeights[$column] / $reproductiveTotalWeight) * 100, 2) }}%" style="width: {{ round(($reproductiveWeights[$column] / $reproductiveTotalWeight) * 100, 2) }}%">
                        @endforeach
                    </colgroup>
                    <thead><tr>@foreach($reproductiveColumns as $column)<th>{{ $allReportColumns['reproductive'][$column] }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach($animal->partosMadre as $record)
                            <tr>
                                @foreach($reproductiveColumns as $column)
                                    @switch($column)
                                        @case('date')
                                            <td class="date">{{ $record->fecha_parto->format('d/m/Y') }}</td>
                                            @break
                                        @case('birth_type')
                                            <td>{{ ucfirst(str_replace('_', ' ', $record->tipo_parto)) }}</td>
                                            @break
                                        @case('maternal_condition')
                                            <td>{{ ucfirst(str_replace('_', ' ', $record->condicion_madre)) }}</td>
                                            @break
                                        @case('calf')
                                            <td><strong>{{ $record->cria?->nombre ?: 'Sin nombre' }}</strong><br>{{ $record->cria?->arete ?? 'Sin cría vinculada' }}</td>
                                            @break
                                        @case('calf_sex')
                                            <td>{{ ucfirst($record->cria?->genero ?? $record->cria_sexo ?? '-') }}</td>
                                            @break
                                        @case('calf_status')
                                            <td>{{ ucfirst(str_replace('_', ' ', $record->cria_estado ?? '-')) }}</td>
                                            @break
                                        @case('birth_weight')
                                            <td class="number">{{ $record->cria_peso_nacer ? number_format((float) $record->cria_peso_nacer, 2).' kg' : '-' }}</td>
                                            @break
                                        @case('observations')
                                            <td>{{ $record->observaciones ?: '-' }}</td>
                                            @break
                                    @endswitch
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty">Sin partos registrados para este ejemplar.</div>
            @endif
        </section>
    @endif

    @if(in_array('milk', $selectedSections, true) && $hasMilkSection)
        @php
            $sectionNumber++;
        @endphp
        <section class="section milk-section">
            <h2 class="section-title milk">{{ $sectionNumber }}. Producción láctea individual</h2>
            @if($milkRecords->isNotEmpty())
                @if($hasField('milk', 'summary'))
                <table class="milk-summary">
                    <tr>
                        <td><strong>Controles</strong><span>{{ $milkSummary['controls'] }}</span></td>
                        <td><strong>Productivos</strong><span>{{ $milkSummary['productive'] }}</span></td>
                        <td><strong>Excepciones</strong><span>{{ $milkSummary['exceptions'] }}</span></td>
                        <td><strong>Litros acumulados</strong><span>{{ number_format($milkSummary['liters'], 2) }}</span></td>
                        <td><strong>Promedio productivo</strong><span>{{ $milkSummary['average'] !== null ? number_format($milkSummary['average'], 2) : '-' }}</span></td>
                        <td><strong>Último control</strong><span>{{ $milkSummary['last_date']?->format('d/m/Y') ?? '-' }}</span></td>
                    </tr>
                </table>
                @endif
                @php
                    $milkDetailColumns = array_values(array_intersect(array_keys($milkWeights), $selectedColumns['milk'] ?? []));
                    $milkTotalWeight = max(array_sum(array_intersect_key($milkWeights, array_flip($milkDetailColumns))), 1);
                @endphp
                @if($milkDetailColumns !== [])
                <table class="data-table milk">
                    <colgroup>
                        @foreach($milkDetailColumns as $column)
                            <col width="{{ round(($milkWeights[$column] / $milkTotalWeight) * 100, 2) }}%" style="width: {{ round(($milkWeights[$column] / $milkTotalWeight) * 100, 2) }}%">
                        @endforeach
                    </colgroup>
                    <thead><tr>@foreach($milkDetailColumns as $column)<th>{{ $allReportColumns['milk'][$column] }}</th>@endforeach</tr></thead>
                    <tbody>
                        @foreach($milkRecords as $record)
                            <tr>
                                @foreach($milkDetailColumns as $column)
                                    @switch($column)
                                        @case('date')
                                            <td class="date">{{ $record->ordeno?->fecha?->format('d/m/Y') ?? '-' }}</td>
                                            @break
                                        @case('shift')
                                            <td>{{ \App\Models\Ordeno::turnoLabel($record->ordeno?->turno ?? '-') }}</td>
                                            @break
                                        @case('liters')
                                            <td class="number">{{ number_format((float) $record->litros, 2) }}</td>
                                            @break
                                        @case('exception')
                                            <td>{{ $record->causa_excepcion ? ucfirst(str_replace('_', ' ', $record->causa_excepcion)) : '-' }}</td>
                                            @break
                                        @case('justification')
                                            <td>{{ $record->justificacion_otros ?: '-' }}</td>
                                            @break
                                    @endswitch
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            @else
                <div class="empty">Animal apto para ordeño, sin controles individuales registrados.</div>
            @endif
        </section>
    @endif

    <div class="footer">
        {{ $branding->name }} · Ficha integral {{ $animal->arete }} · {{ $generatedInPeru }} · {{ $generatedBy }}
        <span class="page-number">Página </span>
    </div>
</body>
</html>
