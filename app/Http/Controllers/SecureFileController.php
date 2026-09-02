<?php

namespace App\Http\Controllers;

use App\Models\Movimiento;
use App\Models\RegistroFoto;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureFileController extends Controller
{
    public function showRegistroFoto(RegistroFoto $foto): StreamedResponse
    {
        $record = $foto->fotografiable;
        abort_unless($record && (int) $record->fundo_id === (int) session('fundo_id'), 404);
        abort_unless(Storage::disk('local')->exists($foto->ruta), 404);

        return Storage::disk('local')->response($foto->ruta, headers: [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function showComprobante(Movimiento $movimiento): StreamedResponse
    {
        abort_unless((int) $movimiento->fundo_id === (int) session('fundo_id'), 404);
        abort_unless($movimiento->comprobante_ruta, 404);
        abort_unless(Storage::disk('local')->exists($movimiento->comprobante_ruta), 404);

        return Storage::disk('local')->response($movimiento->comprobante_ruta, headers: [
            'Cache-Control' => 'private, no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
