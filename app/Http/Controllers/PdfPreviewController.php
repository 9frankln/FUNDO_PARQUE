<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PdfPreviewController extends Controller
{
    /**
     * Serve a cached PDF by token.
     * ?dl=1  → attachment download
     * default → inline (for iframe)
     */
    public function __invoke(Request $request, string $token): Response
    {
        $cacheKey = 'pdf_preview_' . $token;
        $entry = cache()->get($cacheKey);

        abort_unless(
            $entry && is_array($entry) && isset($entry['binary']),
            404,
            'Vista previa expirada o no encontrada.'
        );

        $binary   = $entry['binary'];
        $filename = $entry['filename'] ?? 'reporte.pdf';
        $inline   = ! $request->boolean('dl');

        return response($binary, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . addslashes($filename) . '"',
            'Content-Length'      => strlen($binary),
            'Cache-Control'       => 'private, no-store',
            'Accept-Ranges'       => 'bytes',
        ]);
    }
}
