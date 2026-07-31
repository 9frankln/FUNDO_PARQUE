<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('partos')
            ->whereNotNull('cria_animal_id')
            ->orderBy('id')
            ->chunkById(200, function ($partos): void {
                foreach ($partos as $parto) {
                    $cria = DB::table('animales')->where('id', $parto->cria_animal_id)->first();
                    if (! $cria) {
                        continue;
                    }

                    if ($parto->cria_sexo === null && $cria->genero) {
                        DB::table('partos')->where('id', $parto->id)->update([
                            'cria_sexo' => $cria->genero,
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Data reconciliation cannot be reversed safely.
    }
};
