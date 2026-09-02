<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Asignación atómica y escalable de códigos secuenciales por año y fundo.
 *
 * Unifica la lógica de medicamentos (MET) e insumos (INS) que antes estaba
 * duplicada y sin protección ante concurrencia. Usa una fila de secuenciador
 * por (fundo, año) bloqueada con lockForUpdate() dentro de una transacción,
 * para que dos peticiones simultáneas nunca calculen el mismo número.
 */
abstract class SequentialCodeAllocator
{
    /** Prefijo del código (MET, INS...). */
    public const PREFIX = '';

    /** Tabla donde viven los lotes con la columna numero_lote. */
    abstract protected function lotsTable(): string;

    /** Tabla del secuenciador (ultimo_numero). */
    abstract protected function sequencesTable(): string;

    /** Columna de año en la tabla del secuenciador (codigo_anio|anio). */
    abstract protected function yearColumn(): string;

    /** Nombre singular del ítem para mensajes (medicamento|insumo). */
    abstract protected function itemLabel(): string;

    public static function format(int $year, int $number): string
    {
        return sprintf('%s%02d-%03d', static::PREFIX, $year % 100, $number);
    }

    public static function prefix(int $year): string
    {
        return sprintf('%s%02d-', static::PREFIX, $year % 100);
    }

    public static function normalizeNumber(mixed $value): string
    {
        $digits = substr(preg_replace('/\D+/', '', (string) $value), 0, 3);

        return $digits === '' ? '' : str_pad((string) (int) $digits, 3, '0', STR_PAD_LEFT);
    }

    public static function parse(string $code): ?array
    {
        if (! preg_match('/^'.static::PREFIX.'(\d{2})-(\d{3})$/i', trim($code), $matches)) {
            return null;
        }

        $currentCentury = intdiv(now()->year, 100) * 100;

        return [
            'year' => $currentCentury + (int) $matches[1],
            'number' => (int) $matches[2],
        ];
    }

    public function preview(int $fundoId, int $year, ?int $ignoreLotId = null): int
    {
        return $this->firstAvailableNumber($fundoId, $year, $ignoreLotId);
    }

    /** Primer número disponible (rellena huecos desde 001) partiendo de los códigos existentes. */
    public function firstAvailableNumber(int $fundoId, int $year, ?int $ignoreLotId = null): int
    {
        $existingCodes = DB::table($this->lotsTable())
            ->where('fundo_id', $fundoId)
            ->where('numero_lote', 'like', static::prefix($year).'%')
            ->when($ignoreLotId, fn ($query) => $query->where('id', '!=', $ignoreLotId))
            ->pluck('numero_lote')
            ->all();

        $taken = [];
        foreach ($existingCodes as $code) {
            $parsed = static::parse($code);
            if ($parsed && $parsed['year'] === $year) {
                $taken[$parsed['number']] = true;
            }
        }

        $number = 1;
        while ($number <= 999 && isset($taken[$number])) {
            $number++;
        }

        return min(999, $number);
    }

    public function allocate(
        int $fundoId,
        int $year,
        ?int $requestedNumber = null,
        ?int $ignoreLotId = null,
        string $errorField = 'numeroLote',
    ): string {
        return DB::transaction(function () use ($fundoId, $year, $requestedNumber, $ignoreLotId, $errorField): string {
            // Garantiza una fila de secuenciador por (fundo, año).
            DB::table($this->sequencesTable())->insertOrIgnore([
                'fundo_id' => $fundoId,
                $this->yearColumn() => $year,
                'ultimo_numero' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Bloqueo atómico: serializa la asignación entre peticiones concurrentes.
            DB::table($this->sequencesTable())
                ->where('fundo_id', $fundoId)
                ->where($this->yearColumn(), $year)
                ->lockForUpdate()
                ->first();

            $number = $requestedNumber ?? $this->firstAvailableNumber($fundoId, $year, $ignoreLotId);

            if ($number < 1 || $number > 999) {
                throw ValidationException::withMessages([
                    $errorField => 'La numeración debe estar entre 001 y 999.',
                ]);
            }

            if ($requestedNumber === null) {
                while ($number <= 999 && $this->isReserved($fundoId, $year, $number, $ignoreLotId)) {
                    $number++;
                }
            } elseif ($this->isReserved($fundoId, $year, $number, $ignoreLotId)) {
                throw ValidationException::withMessages([
                    $errorField => 'Este número ya está asignado a otro '.$this->itemLabel().'.',
                ]);
            }

            if ($number > 999) {
                throw ValidationException::withMessages([
                    $errorField => 'La numeración anual de '.$this->itemLabel().'s está agotada.',
                ]);
            }

            DB::table($this->sequencesTable())
                ->where('fundo_id', $fundoId)
                ->where($this->yearColumn(), $year)
                ->update([
                    'ultimo_numero' => $number,
                    'updated_at' => now(),
                ]);

            return self::format($year, $number);
        });
    }

    private function isReserved(int $fundoId, int $year, int $number, ?int $ignoreLotId): bool
    {
        return DB::table($this->lotsTable())
            ->where('fundo_id', $fundoId)
            ->where('numero_lote', self::format($year, $number))
            ->when($ignoreLotId, fn ($query) => $query->where('id', '!=', $ignoreLotId))
            ->exists();
    }
}
