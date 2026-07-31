<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $this->move('public', 'local');
    }

    public function down(): void
    {
        $this->move('local', 'public');
    }

    private function move(string $sourceDisk, string $targetDisk): void
    {
        $paths = DB::table('registro_fotos')->pluck('ruta')
            ->merge(DB::table('movimientos')->whereNotNull('comprobante_ruta')->pluck('comprobante_ruta'))
            ->filter()
            ->unique();

        foreach ($paths as $path) {
            if (! Storage::disk($sourceDisk)->exists($path)) {
                continue;
            }

            if (Storage::disk($targetDisk)->exists($path)
                || Storage::disk($targetDisk)->put($path, Storage::disk($sourceDisk)->get($path))) {
                Storage::disk($sourceDisk)->delete($path);
            }
        }
    }
};
