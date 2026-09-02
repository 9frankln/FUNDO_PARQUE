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
        $species = Especie::find($speciesId);
        $prefix = $species?->codigo_animal;
        if (! $prefix) {
            return 1;
        }

        $maxUsed = (int) DB::table('animales')
            ->where('fundo_id', $fundoId)
            ->where('especie_id', $speciesId)
            ->where('codigo_anio', $year)
            ->whereNull('deleted_at')
            ->max('codigo_secuencia');

        $candidate = $maxUsed + 1;
        while ($candidate <= 999 && $this->isReserved($fundoId, null, $prefix, $speciesId, $year, $candidate)) {
            $candidate++;
        }

        return min(999, $candidate);
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

        if ($requestedNumber !== null) {
            if ($requestedNumber < 1 || $requestedNumber > 999) {
                throw ValidationException::withMessages([
                    'codigoNumero' => 'La numeración debe estar entre 001 y 999.',
                ]);
            }
            if ($this->isReserved($fundoId, $animal->id, $prefix, $species->id, $year, $requestedNumber)) {
                throw ValidationException::withMessages([
                    'codigoNumero' => 'Este número ya está asignado a otro animal.',
                ]);
            }
            $number = $requestedNumber;
        } else {
            $maxUsed = (int) DB::table('animales')
                ->where('fundo_id', $fundoId)
                ->where('especie_id', $species->id)
                ->where('codigo_anio', $year)
                ->whereNull('deleted_at')
                ->when($animal->exists, fn ($q) => $q->where('id', '!=', $animal->id))
                ->max('codigo_secuencia');

            $number = $maxUsed + 1;
            while ($number <= 999 && $this->isReserved($fundoId, $animal->id, $prefix, $species->id, $year, $number)) {
                $number++;
            }

            if ($number > 999) {
                throw ValidationException::withMessages([
                    'codigoNumero' => 'La numeración anual para esta especie está agotada.',
                ]);
            }
        }

        DB::table('animal_code_sequences')->updateOrInsert(
            [
                'fundo_id' => $fundoId,
                'especie_id' => $species->id,
                'codigo_anio' => $year,
            ],
            [
                'ultimo_numero' => $number,
                'updated_at' => now(),
            ]
        );

        return [
            'arete' => self::format($prefix, $year, $number),
            'codigo_prefijo' => $prefix,
            'codigo_anio' => $year,
            'codigo_secuencia' => $number,
        ];
    }

    public function record(Animal $animal): void
    {
        DB::table('animal_identifiers')->updateOrInsert(
            [
                'fundo_id' => $animal->fundo_id,
                'animal_id' => $animal->id,
            ],
            [
                'arete' => $animal->arete,
                'codigo_prefijo' => $animal->codigo_prefijo,
                'codigo_anio' => $animal->codigo_anio,
                'codigo_secuencia' => $animal->codigo_secuencia,
                'created_at' => now(),
            ]
        );
    }

    private function isReserved(int $fundoId, ?int $animalId, string $prefix, int $speciesId, int $year, int $number): bool
    {
        $code = self::format($prefix, $year, $number);

        $animalQuery = DB::table('animales')
            ->where('fundo_id', $fundoId)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($code, $prefix, $speciesId, $year, $number) {
                $query->where('arete', $code)
                    ->orWhere(function ($structured) use ($prefix, $year, $number) {
                        $structured->where('codigo_prefijo', $prefix)
                            ->where('codigo_anio', $year)
                            ->where('codigo_secuencia', $number);
                    })
                    ->orWhere(function ($scoped) use ($speciesId, $year, $number) {
                        $scoped->where('especie_id', $speciesId)
                            ->where('codigo_anio', $year)
                            ->where('codigo_secuencia', $number);
                    });
            });

        if ($animalId) {
            $animalQuery->where('id', '!=', $animalId);
        }

        return $animalQuery->exists();
    }
}
