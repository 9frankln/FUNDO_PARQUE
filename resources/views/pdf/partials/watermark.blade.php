@if($pdfConfig->showWatermark())
    <div class="pdf-watermark">{{ $pdfConfig->watermarkText($fundo->nombre ?? null) }}</div>
@endif
