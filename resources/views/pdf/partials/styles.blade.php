<style>
    .pdf-watermark {
        position: fixed;
        top: {{ $pdfConfig->watermarkOrientation() === 'horizontal' ? '44%' : '38%' }};
        left: 4%;
        right: 4%;
        text-align: center;
        transform: rotate({{ $pdfConfig->watermarkRotation() }});
        transform-origin: 50% 50%;
        opacity: {{ $pdfConfig->watermarkOpacity() }};
        font-size: 34pt;
        font-weight: 900;
        color: {{ $pdfConfig->watermarkColor() }};
        text-transform: uppercase;
        letter-spacing: 4px;
        z-index: -1000;
        pointer-events: none;
    }
    thead {
        display: table-header-group;
    }
    tfoot {
        display: table-footer-group;
    }
    tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .summary-card, .signature-card, .verification-seal, .signatures-block, .digital-stamp-container {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }
    .lot-section, .table-card-container, .summary-wrap, .report-section, .section, .data-table, table.data {
        page-break-inside: auto !important;
        break-inside: auto !important;
    }
    .lot-title-bar, .section-title, .summary-heading, .card-header, .context-title, .lot-meta {
        page-break-after: avoid !important;
        break-after: avoid !important;
    }
    .signatures-block, .digital-stamp-container {
        width: 100%;
        margin-top: 6pt;
        margin-bottom: 2pt;
        page-break-inside: avoid;
        border-collapse: collapse;
        background: transparent;
    }
    .signatures-block td, .digital-stamp-container td {
        vertical-align: top;
    }
    .signature-card {
        border-top: 1.5px solid #334155;
        padding-top: 4pt;
        text-align: center;
    }
    .signature-title {
        font-size: 7.5pt;
        font-weight: bold;
        color: #0f172a;
        display: block;
    }
    .signature-subtitle {
        font-size: 6.5pt;
        color: #64748b;
        display: block;
        margin-top: 1pt;
    }
    .signature-doc {
        font-size: 5.5pt;
        color: #94a3b8;
        display: block;
        margin-top: 0.5pt;
    }
    .verification-seal-box {
        text-align: center;
        vertical-align: middle;
    }
    .verification-seal {
        display: inline-block;
        border: 1.5px solid {{ $pdfConfig->accentColor() }};
        border-radius: 4pt;
        padding: 3pt 6pt;
        background: {{ $pdfConfig->accentSoft() }};
        text-align: center;
    }
    .verification-seal .seal-title {
        font-size: 6pt;
        font-weight: bold;
        color: {{ $pdfConfig->accentColor() }};
        text-transform: uppercase;
        display: block;
        letter-spacing: 0.5px;
    }
    .verification-seal .seal-subtitle {
        font-size: 5.5pt;
        color: #475569;
        display: block;
        margin-top: 1pt;
    }
    .header {
        border-bottom-color: {{ $pdfConfig->accentColor() }} !important;
    }
    .eyebrow {
        color: {{ $pdfConfig->accentColor() }} !important;
    }
    h1 {
        color: {{ $pdfConfig->accentDark() }} !important;
    }
    .meta-table td, .summary-card td, .summary-card table td {
        border-color: {{ $pdfConfig->accentBorder() }} !important;
    }
    .meta-table {
        border-color: {{ $pdfConfig->accentBorder() }} !important;
    }
    .meta-label {
        color: {{ $pdfConfig->accentDark() }} !important;
    }
    .data-table, table.data {
        width: 100%;
        border-collapse: collapse !important;
        margin-top: 5pt;
        margin-bottom: 6pt;
    }
    .data-table:not(.lot-themed-table) th, table.data:not(.lot-themed-table) th {
        background-color: {{ $pdfConfig->accentColor() }} !important;
        border: 0.8px solid {{ $pdfConfig->accentDark() }} !important;
        color: #ffffff !important;
    }
    .data-table:not(.lot-themed-table) td, table.data:not(.lot-themed-table) td {
        border: 0.8px solid {{ $pdfConfig->accentBorder() }} !important;
    }
    .data-table tbody tr:nth-child(even), table.data tbody tr:nth-child(even) {
        background-color: {{ $pdfConfig->accentRowEven() }} !important;
    }
    .badge-active {
        background-color: {{ $pdfConfig->accentSoft() }} !important;
        color: {{ $pdfConfig->accentDark() }} !important;
        border: 1px solid {{ $pdfConfig->accentBorder() }} !important;
    }
    .section-title {
        border-left-color: {{ $pdfConfig->accentColor() }} !important;
        background-color: {{ $pdfConfig->accentSoft() }} !important;
        color: {{ $pdfConfig->accentDark() }} !important;
    }
    .pdf-footer {
        position: fixed;
        right: 0;
        bottom: -8.5mm;
        left: 0;
        height: 5mm;
        padding-top: 2pt;
        border-top: 0.8px solid {{ $pdfConfig->accentBorder() }} !important;
        color: #64748b;
        font-size: 6.2pt;
        line-height: 1.15;
    }
    .pdf-footer .pdf-footer-text {
        max-width: 82%;
        display: inline-block;
        white-space: nowrap;
        overflow: hidden;
    }
    .summary-card {
        border: 1px solid {{ $pdfConfig->accentBorder() }};
        border-radius: {{ $pdfConfig->tableBorderRadius() }};
        overflow: hidden;
        background-color: {{ $pdfConfig->accentSoft() }};
        margin-bottom: 8pt;
        page-break-inside: avoid;
    }
    .summary-card table {
        margin: 0 !important;
        border: none !important;
    }
    .data-table, table.data, table.data-alt, .summary-table, .meta-table, .meta, .facts, .profile-table, .overview-table, .context-table, .summary-grid, .lot-themed-table, table.summary {
        width: 100%;
        border-collapse: collapse !important;
        border-spacing: 0 !important;
        margin-top: 5pt;
        margin-bottom: 6pt;
    }
    .data-table:not(.lot-themed-table) th, table.data:not(.lot-themed-table) th, table.data-alt:not(.lot-themed-table) th, .summary-table:not(.lot-themed-table) th, table.summary:not(.lot-themed-table) th {
        background-color: {{ $pdfConfig->accentColor() }} !important;
        border: 0.8px solid {{ $pdfConfig->accentDark() }} !important;
        color: #ffffff !important;
    }
    .data-table:not(.lot-themed-table) td, table.data:not(.lot-themed-table) td, table.data-alt:not(.lot-themed-table) td, .summary-table:not(.lot-themed-table) td, table.summary:not(.lot-themed-table) td {
        border: 0.8px solid {{ $pdfConfig->accentBorder() }} !important;
    }
    .data-table tbody tr:nth-child(even), table.data tbody tr:nth-child(even), table.data-alt tbody tr:nth-child(even), .summary-table tbody tr:nth-child(even) {
        background-color: {{ $pdfConfig->accentRowEven() }} !important;
    }

    @if($pdfConfig->isRoundedTables())
    .lot-section, .summary-card, .summary-wrap, .report-section, .section, .summary-card-wrapper, .meta-card, .badge, .status, .signature-card, .verification-seal, .table-wrap, .data-table-wrap, .lot-table-wrap, .photo-box {
        border-radius: {{ $pdfConfig->tableBorderRadius() }} !important;
    }
    .lot-title, .lot-title-bar, .section-title, .section-heading, .summary-heading, .context-title, .section-alt-title, .section-head {
        border-radius: {{ $pdfConfig->tableBorderRadius() }} {{ $pdfConfig->tableBorderRadius() }} 0 0 !important;
    }
    @else
    .lot-section, .summary-card, .summary-wrap, .report-section, .section, .profile-table, .summary-card-wrapper, .meta-card, .badge, .status, .signature-card, .verification-seal, .table-wrap, .data-table-wrap, .lot-table-wrap,
    .lot-title, .lot-title-bar, .section-title, .section-heading, .summary-heading, .context-title, .section-alt-title, .section-head, .photo-box {
        border-radius: 0 !important;
    }
    @endif
</style>

@php
    $scaleVal = isset($scale) ? (string) $scale : '85';
    $scaleMap = [
        '40' => [
            'body_font' => '4.0pt',
            'th_font' => '3.8pt',
            'td_font' => '3.6pt',
            'padding_y' => '0.5pt',
            'padding_x' => '1.2pt',
            'title_font' => '8.0pt',
            'section_font' => '5.0pt',
            'meta_font' => '3.8pt',
            'line_height' => '0.88',
        ],
        '45' => [
            'body_font' => '4.4pt',
            'th_font' => '4.1pt',
            'td_font' => '3.9pt',
            'padding_y' => '0.6pt',
            'padding_x' => '1.4pt',
            'title_font' => '8.5pt',
            'section_font' => '5.4pt',
            'meta_font' => '4.2pt',
            'line_height' => '0.90',
        ],
        '50' => [
            'body_font' => '4.8pt',
            'th_font' => '4.5pt',
            'td_font' => '4.2pt',
            'padding_y' => '0.7pt',
            'padding_x' => '1.6pt',
            'title_font' => '9.5pt',
            'section_font' => '6.0pt',
            'meta_font' => '4.6pt',
            'line_height' => '0.92',
        ],
        '55' => [
            'body_font' => '5.2pt',
            'th_font' => '4.8pt',
            'td_font' => '4.5pt',
            'padding_y' => '0.8pt',
            'padding_x' => '1.8pt',
            'title_font' => '10.0pt',
            'section_font' => '6.5pt',
            'meta_font' => '5.0pt',
            'line_height' => '0.95',
        ],
        '65' => [
            'body_font' => '5.8pt',
            'th_font' => '5.4pt',
            'td_font' => '5.1pt',
            'padding_y' => '1.2pt',
            'padding_x' => '2.2pt',
            'title_font' => '11.5pt',
            'section_font' => '7.2pt',
            'meta_font' => '5.6pt',
            'line_height' => '1.00',
        ],
        '75' => [
            'body_font' => '6.6pt',
            'th_font' => '6.0pt',
            'td_font' => '5.8pt',
            'padding_y' => '1.6pt',
            'padding_x' => '2.6pt',
            'title_font' => '12.8pt',
            'section_font' => '8.0pt',
            'meta_font' => '6.4pt',
            'line_height' => '1.05',
        ],
        '85' => [
            'body_font' => '7.4pt',
            'th_font' => '6.8pt',
            'td_font' => '6.5pt',
            'padding_y' => '2.2pt',
            'padding_x' => '3.2pt',
            'title_font' => '14.0pt',
            'section_font' => '9.0pt',
            'meta_font' => '7.2pt',
            'line_height' => '1.10',
        ],
        '100' => [
            'body_font' => '8.5pt',
            'th_font' => '7.8pt',
            'td_font' => '7.5pt',
            'padding_y' => '3.0pt',
            'padding_x' => '4.0pt',
            'title_font' => '15.5pt',
            'section_font' => '10.0pt',
            'meta_font' => '8.0pt',
            'line_height' => '1.15',
        ],
    ];
    $activeScale = $scaleMap[$scaleVal] ?? $scaleMap['85'];
@endphp

<style>
    body {
        font-size: {{ $activeScale['body_font'] }} !important;
        line-height: {{ $activeScale['line_height'] }} !important;
    }
    h1 {
        font-size: {{ $activeScale['title_font'] }} !important;
    }
    .eyebrow {
        font-size: calc({{ $activeScale['body_font'] }} * 0.9) !important;
    }
    .subtitle {
        font-size: {{ $activeScale['body_font'] }} !important;
    }
    .section-title, h2.section-title, .section-heading, .summary-heading, .context-title, .section-head {
        font-size: {{ $activeScale['section_font'] }} !important;
    }
    .summary-card table td, .meta-table td, .meta td, .facts td, .profile-table td, .overview-table td, .context-table td, .summary-grid td, .meta-value {
        font-size: {{ $activeScale['meta_font'] }} !important;
        padding: {{ $activeScale['padding_y'] }} {{ $activeScale['padding_x'] }} !important;
        line-height: {{ $activeScale['line_height'] }} !important;
    }
    .meta-label, .summary-label, .field-label {
        font-size: calc({{ $activeScale['meta_font'] }} * 0.85) !important;
    }
    .lot-title-bar, .lot-title {
        font-size: {{ $activeScale['section_font'] }} !important;
        padding: {{ $activeScale['padding_y'] }} calc({{ $activeScale['padding_x'] }} * 1.5) !important;
        line-height: {{ $activeScale['line_height'] }} !important;
    }
    .lot-title-bar strong {
        font-size: {{ $activeScale['section_font'] }} !important;
    }
    .lot-title-bar span, .lot-title-bar td {
        font-size: calc({{ $activeScale['section_font'] }} * 0.9) !important;
    }
    .lot-meta td, .lot-meta-cell {
        font-size: {{ $activeScale['td_font'] }} !important;
        padding: {{ $activeScale['padding_y'] }} {{ $activeScale['padding_x'] }} !important;
        line-height: {{ $activeScale['line_height'] }} !important;
    }
    .data-table th, table.data th, table.data-alt th, table.data-table th, .summary-table th, .lot-themed-table th, table.summary th {
        font-size: {{ $activeScale['th_font'] }} !important;
        padding: {{ $activeScale['padding_y'] }} {{ $activeScale['padding_x'] }} !important;
        line-height: {{ $activeScale['line_height'] }} !important;
    }
    .data-table td, table.data td, table.data-alt td, table.data-table td, .summary-table td, .lot-themed-table td, table.summary td {
        font-size: {{ $activeScale['td_font'] }} !important;
        padding: {{ $activeScale['padding_y'] }} {{ $activeScale['padding_x'] }} !important;
        line-height: {{ $activeScale['line_height'] }} !important;
    }
    .badge, .status {
        font-size: calc({{ $activeScale['td_font'] }} * 0.88) !important;
        padding: 0.5pt 3pt !important;
    }
    .col-edad, .data-table td.col-edad {
        font-size: calc({{ $activeScale['td_font'] }} * 0.92) !important;
        white-space: nowrap !important;
        letter-spacing: -0.15px !important;
    }
    .sub-text, .text-muted-xs {
        font-size: calc({{ $activeScale['td_font'] }} * 0.88) !important;
    }
</style>
