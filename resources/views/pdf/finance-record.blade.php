<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $reportTitle }} | {{ $branding->name }}</title>
    <style>
        @page { size: A4 portrait; margin: 26px 30px 38px; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #24312b; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.35; }
        .header { padding: 14px 16px; border-left: 5px solid {{ $accent }}; background: {{ $accentSoft }}; }
        .eyebrow { margin: 0 0 4px; color: {{ $accent }}; font-size: 7px; font-weight: bold; letter-spacing: 1.2px; text-transform: uppercase; }
        h1 { margin: 0; color: #17231d; font-size: 18px; line-height: 1.15; }
        .subtitle { margin: 4px 0 0; color: #5f6f66; font-size: 8px; }
        .meta { width: 100%; margin-top: 8px; border-collapse: collapse; table-layout: fixed; }
        .meta td { padding: 5px 7px; border: 1px solid #d9e2dc; background: #fbfdfc; vertical-align: top; }
        .meta-label { display: block; color: #6b7c72; font-size: 6.5px; font-weight: bold; text-transform: uppercase; }
        .meta-value { display: block; margin-top: 2px; color: #26372e; font-size: 8px; font-weight: bold; }
        .section { margin-top: 10px; page-break-inside: avoid; }
        .section-head { margin-bottom: 5px; padding: 6px 8px; border-left: 3px solid {{ $accent }}; background: {{ $accentSoft }}; }
        .section-title { margin: 0; color: {{ $accent }}; font-size: 10px; }
        .section-description { margin: 1px 0 0; color: #718078; font-size: 7px; }
        .facts { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .facts td { width: 50%; padding: 6px 8px; border: 1px solid #dce5df; vertical-align: top; }
        .field-label { display: block; color: #718078; font-size: 6.5px; font-weight: bold; letter-spacing: .3px; text-transform: uppercase; }
        .field-value { display: block; margin-top: 2px; color: #23342b; font-size: 8.5px; font-weight: bold; word-wrap: break-word; }
        .photo-box { margin-bottom: 6px; padding: 8px; border: 1px solid #dce5df; background: #fafcfb; text-align: center; }
        .photo { width: 290px; height: 185px; object-fit: contain; }
        .photo-empty { padding: 30px; color: #839087; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .footer { position: fixed; right: 0; bottom: -24px; left: 0; padding-top: 5px; border-top: 1px solid #d9e2dc; color: #85938b; font-size: 6.5px; }
        .page { float: right; }
        .page::after { content: counter(page); }
    </style>
</head>
<body>
    @php
        $generatedInPeru = $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i');
    @endphp
    <header class="header">
        <x-brand-logo pdf style="float: right; width: 30px; height: 30px; color: {{ $accent }}; object-fit: contain" />
        <p class="eyebrow">{{ $branding->name }} · {{ $branding->tagline }} · Reporte financiero</p>
        <h1>{{ $reportTitle }}</h1>
        <p class="subtitle">{{ $reportSubtitle }}</p>
    </header>

    <table class="meta">
        <tr>
            <td><span class="meta-label">Fundo</span><span class="meta-value">{{ $fundo->nombre }}</span></td>
            <td><span class="meta-label">Generado</span><span class="meta-value">{{ $generatedInPeru }}</span></td>
        </tr>
        <tr>
            <td><span class="meta-label">Responsable del reporte</span><span class="meta-value">{{ $generatedBy }}</span></td>
            <td><span class="meta-label">Administración</span><span class="meta-value">{{ $administrators }}</span></td>
        </tr>
    </table>

    @foreach($reportSections as $section)
        <section class="section">
            <div class="section-head">
                <h2 class="section-title">{{ $section['label'] }}</h2>
                <p class="section-description">{{ $section['description'] }}</p>
            </div>

            @foreach($section['fields'] as $field)
                @if($field['kind'] === 'image')
                    <div class="photo-box">
                        @if($field['value'])
                            <img src="{{ $field['value'] }}" alt="{{ $field['label'] }}" class="photo">
                        @else
                            <div class="photo-empty">Sin imagen disponible</div>
                        @endif
                    </div>
                @endif
            @endforeach

            @php
                $textFields = collect($section['fields'])->where('kind', 'text')->values();
            @endphp
            @if($textFields->isNotEmpty())
                <table class="facts">
                    @foreach($textFields->chunk(2) as $row)
                        <tr>
                            @foreach($row as $field)
                                <td><span class="field-label">{{ $field['label'] }}</span><span class="field-value">{{ $field['value'] }}</span></td>
                            @endforeach
                            @if($row->count() === 1)<td></td>@endif
                        </tr>
                    @endforeach
                </table>
            @endif
        </section>
    @endforeach

    <footer class="footer">
        Documento generado por {{ $branding->name }}. Información del fundo activo al momento de exportación.
        <span class="page">Página </span>
    </footer>
</body>
</html>
