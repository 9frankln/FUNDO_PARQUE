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
        $maxUsed = (int) DB::table('lotes_engorde')
            ->where('fundo_id', $fundoId)
            ->where('codigo_anio', $year)
            ->whereNull('deleted_at')
            ->max('codigo_secuencia');

        $candidate = $maxUsed + 1;
        while ($candidate <= 999 && $this->isReserved($fundoId, $year, $candidate)) {
            $candidate++;
        }

        return min(999, $candidate);
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

        $maxUsed = (int) DB::table('lotes_engorde')
            ->where('fundo_id', $fundoId)
            ->where('codigo_anio', $year)
            ->whereNull('deleted_at')
            ->max('codigo_secuencia');

        $number = $maxUsed + 1;
        while ($number <= 999 && $this->isReserved($fundoId, $year, $number)) {
            $number++;
        }

        if ($number > 999) {
            throw ValidationException::withMessages([
                'codigo' => 'La numeración anual de lotes está agotada.',
            ]);
        }

        DB::table('lote_code_sequences')->updateOrInsert(
            [
                'fundo_id' => $fundoId,
                'codigo_anio' => $year,
            ],
            [
                'ultimo_numero' => $number,
                'updated_at' => now(),
            ]
        );

        return [
            'codigo' => self::format($year, $number),
            'codigo_anio' => $year,
            'codigo_secuencia' => $number,
        ];
    }

    private function isReserved(int $fundoId, int $year, int $number): bool
    {
        return DB::table('lotes_engorde')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->where('codigo', self::format($year, $number))
            ->exists();
    }
}
