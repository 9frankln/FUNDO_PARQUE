@if($pdfConfig->showFooter())
    <div class="pdf-footer">
        <span class="pdf-footer-text">
            <span>{{ $pdfConfig->footerText($fundo->nombre ?? null, $branding->name ?? null) }}</span>
            @if($pdfConfig->showGeneratedDateTime() && isset($generatedAt))
                <span>&nbsp;&middot;&nbsp;{{ is_string($generatedAt) ? $generatedAt : $generatedAt->copy()->timezone('America/Lima')->format('d/m/Y H:i') }}</span>
            @endif
            @if($pdfConfig->showGeneratedBy() && !empty($generatedBy))
                <span>&nbsp;&middot;&nbsp;{{ $generatedBy }}</span>
            @endif
        </span>
    </div>

    @if($pdfConfig->showPageNumbers())
        <script type="text/php">
            if (isset($pdf)) {
                $font = $fontMetrics->getFont("Helvetica", "bold");
                $size = 6.2;
                $color = [15/255, 23/255, 42/255];
                $text = "Pág. {PAGE_NUM} de {PAGE_COUNT}";
                $textWidth = $fontMetrics->getTextWidth("Pág. 99 de 99", $font, $size);
                $w = $pdf->get_width();
                $h = $pdf->get_height();
                $pdf->page_text($w - $textWidth - 28.35, $h - 22, $text, $font, $size, $color);
            }
        </script>
    @endif
@endif
