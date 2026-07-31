<?php

namespace App\Support;

use App\Models\LoteEngorde;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoteCodeAllocator
{
    public static function format(int $year, int $number): string
    {
        return sprintf('LOT%02d-%03d', $year % 100, $number);
    }

    public function preview(int $fundoId, int $year): int
    {
        $counter = (int) DB::table('lote_code_sequences')
            ->where('fundo_id', $fundoId)
            ->where('codigo_anio', $year)
            ->value('ultimo_numero');
        $existing = (int) LoteEngorde::withTrashed()
            ->where('fundo_id', $fundoId)
            ->where('codigo_anio', $year)
            ->max('codigo_secuencia');

        $number = max($counter, $existing) + 1;
        while ($number <= 999 && $this->isReserved($fundoId, $year, $number)) {
            $number++;
        }

        return min(999, $number);
    }

    public function allocate(LoteEngorde $lot, int $fundoId, int $year): array
    {
        if ($lot->exists) {
            return [
                'codigo' => $lot->codigo,
                'codigo_anio' => $lot->codigo_anio,
                'codigo_secuencia' => $lot->codigo_secuencia,
            ];
        }

        $existing = (int) LoteEngorde::withTrashed()
            ->where('fundo_id', $fundoId)
            ->where('codigo_anio', $year)
            ->max('codigo_secuencia');
        DB::table('lote_code_sequences')->insertOrIgnore([
            'fundo_id' => $fundoId,
            'codigo_anio' => $year,
            'ultimo_numero' => $existing,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $counter = DB::table('lote_code_sequences')
            ->where('fundo_id', $fundoId)
            ->where('codigo_anio', $year)
            ->lockForUpdate()
            ->first();
        $number = max((int) $counter->ultimo_numero, $existing) + 1;

        while ($number <= 999 && $this->isReserved($fundoId, $year, $number)) {
            $number++;
        }

        if ($number > 999) {
            throw ValidationException::withMessages([
                'codigo' => 'La numeración anual de lotes está agotada.',
            ]);
        }

        DB::table('lote_code_sequences')->where('id', $counter->id)->update([
            'ultimo_numero' => $number,
            'updated_at' => now(),
        ]);

        return [
            'codigo' => self::format($year, $number),
            'codigo_anio' => $year,
            'codigo_secuencia' => $number,
        ];
    }

    private function isReserved(int $fundoId, int $year, int $number): bool
    {
        return LoteEngorde::withTrashed()
            ->where('fundo_id', $fundoId)
            ->where('codigo', self::format($year, $number))
            ->exists();
    }
}
