<?php

namespace App\Support;

use App\Models\Animal;
use App\Models\Especie;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnimalCodeAllocator
{
    public static function format(string $prefix, int $year, int $number): string
    {
        return sprintf('%s%02d-%03d', strtoupper($prefix), $year % 100, $number);
    }

    public function preview(int $fundoId, int $speciesId, int $year): int
    {
        $last = (int) DB::table('animal_code_sequences')
            ->where('fundo_id', $fundoId)
            ->where('especie_id', $speciesId)
            ->where('codigo_anio', $year)
            ->value('ultimo_numero');

        return min(999, $last + 1);
    }

    public function allocate(
        Animal $animal,
        int $fundoId,
        Especie $species,
        int $year,
        ?int $requestedNumber = null,
    ): array {
        $prefix = $species->codigo_animal;
        if (! $prefix) {
            throw ValidationException::withMessages([
                'especieId' => 'La especie seleccionada no admite código automático.',
            ]);
        }

        $sameBucket = $animal->exists
            && (int) $animal->especie_id === $species->id
            && (int) $animal->codigo_anio === $year
            && $animal->codigo_secuencia;

        if ($sameBucket && ($requestedNumber === null || $requestedNumber === (int) $animal->codigo_secuencia)) {
            return [
                'arete' => $animal->arete,
                'codigo_prefijo' => $animal->codigo_prefijo,
                'codigo_anio' => (int) $animal->codigo_anio,
                'codigo_secuencia' => (int) $animal->codigo_secuencia,
            ];
        }

        DB::table('animal_code_sequences')->insertOrIgnore([
            'fundo_id' => $fundoId,
            'especie_id' => $species->id,
            'codigo_anio' => $year,
            'ultimo_numero' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $counter = DB::table('animal_code_sequences')
            ->where('fundo_id', $fundoId)
            ->where('especie_id', $species->id)
            ->where('codigo_anio', $year)
            ->lockForUpdate()
            ->first();

        $number = $requestedNumber ?? ((int) $counter->ultimo_numero + 1);
        if ($number < 1 || $number > 999) {
            throw ValidationException::withMessages([
                'codigoNumero' => 'La numeración debe estar entre 001 y 999.',
            ]);
        }

        if ($requestedNumber === null) {
            while ($number <= 999 && $this->isReserved($fundoId, $animal->id, $prefix, $year, $number)) {
                $number++;
            }
        } elseif ($this->isReserved($fundoId, $animal->id, $prefix, $year, $number)) {
            throw ValidationException::withMessages([
                'codigoNumero' => 'Este número ya está asignado a otro animal.',
            ]);
        }

        if ($number > 999) {
            throw ValidationException::withMessages([
                'codigoNumero' => 'La numeración anual para esta especie está agotada.',
            ]);
        }

        DB::table('animal_code_sequences')
            ->where('id', $counter->id)
            ->update([
                'ultimo_numero' => max((int) $counter->ultimo_numero, $number),
                'updated_at' => now(),
            ]);

        return [
            'arete' => self::format($prefix, $year, $number),
            'codigo_prefijo' => $prefix,
            'codigo_anio' => $year,
            'codigo_secuencia' => $number,
        ];
    }

    public function record(Animal $animal): void
    {
        DB::table('animal_identifiers')->insertOrIgnore([
            'fundo_id' => $animal->fundo_id,
            'animal_id' => $animal->id,
            'arete' => $animal->arete,
            'codigo_prefijo' => $animal->codigo_prefijo,
            'codigo_anio' => $animal->codigo_anio,
            'codigo_secuencia' => $animal->codigo_secuencia,
            'created_at' => now(),
        ]);
    }

    private function isReserved(int $fundoId, ?int $animalId, string $prefix, int $year, int $number): bool
    {
        $code = self::format($prefix, $year, $number);

        $animalQuery = DB::table('animales')
            ->where('fundo_id', $fundoId)
            ->where(function ($query) use ($code, $prefix, $year, $number) {
                $query->where('arete', $code)
                    ->orWhere(function ($structured) use ($prefix, $year, $number) {
                        $structured->where('codigo_prefijo', $prefix)
                            ->where('codigo_anio', $year)
                            ->where('codigo_secuencia', $number);
                    });
            });
        $historyQuery = DB::table('animal_identifiers')
            ->where('fundo_id', $fundoId)
            ->where(function ($query) use ($code, $prefix, $year, $number) {
                $query->where('arete', $code)
                    ->orWhere(function ($structured) use ($prefix, $year, $number) {
                        $structured->where('codigo_prefijo', $prefix)
                            ->where('codigo_anio', $year)
                            ->where('codigo_secuencia', $number);
                    });
            });

        if ($animalId) {
            $animalQuery->where('id', '!=', $animalId);
            $historyQuery->where('animal_id', '!=', $animalId);
        }

        return $animalQuery->exists() || $historyQuery->exists();
    }
}
