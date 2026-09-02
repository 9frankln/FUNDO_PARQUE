<?php

namespace App\Support;

use App\Models\LoteEngorde;
use Illuminate\Support\Collection;

class EngordeReport
{
    public const COLUMNS = [
        'codigo' => 'Código',
        'nombre' => 'Nombre',
        'foto_registrada' => 'Foto registrada',
        'especie_raza' => 'Especie / raza',
        'sexo_clasificacion' => 'Sexo / clasificación',
        'fecha_ingreso' => 'Fecha de ingreso',
        'dias_engorde' => 'Días en engorde',
        'peso_inicial' => 'Peso inicial (kg)',
        'ultimo_pesaje' => 'Último control',
        'ganancia_kg' => 'Ganancia (kg)',
        'ganancia_pct' => 'Ganancia (%)',
        'gmd_kg_dia' => 'Ganancia diaria (kg/día)',
        'controles' => 'Controles',
        'estado' => 'Estado',
        'observaciones' => 'Observaciones',
    ];

    public const DEFAULT_COLUMNS = [
        'codigo',
        'nombre',
        'especie_raza',
        'fecha_ingreso',
        'dias_engorde',
        'peso_inicial',
        'ultimo_pesaje',
        'ganancia_kg',
        'ganancia_pct',
        'gmd_kg_dia',
    ];

    public const MAX_ANIMALS = 1000;

    public static function normalizeColumns(array $columns): array
    {
        return array_keys(array_intersect_key(self::COLUMNS, array_flip($columns)));
    }

    public static function loadLots(int $fundoId, array $lotIds): Collection
    {
        $lots = LoteEngorde::query()
            ->where('fundo_id', $fundoId)
            ->whereIn('id', array_values(array_unique(array_map('intval', $lotIds))))
            ->with([
                'animales' => fn ($query) => $query
                    ->withCount('pesajes')
                    ->with(['animal.especie', 'animal.raza', 'animal.movimientoVenta', 'ultimoPesaje'])
                    ->orderBy('id'),
            ])
            ->orderByDesc('fecha_inicio')
            ->orderByDesc('id')
            ->get();

        $lots->each(function (LoteEngorde $lot): void {
            $animals = $lot->animales
                ->each(fn ($engorde) => $engorde->setRelation('lote', $lot))
                ->sortBy(fn ($engorde) => $engorde->animal?->arete ?? '')
                ->values();
            $lot->setRelation('animales', $animals);
        });

        return $lots;
    }

    public static function summarize(Collection $lots): array
    {
        $animals = $lots->flatMap->animales;
        $metrics = $animals->map->reportMetrics();
        $initialWeight = (float) $metrics->sum('initial_weight');
        $referenceWeight = (float) $metrics->sum('reference_weight');
        $gainKg = $referenceWeight - $initialWeight;
        $dailyGains = $metrics->pluck('average_daily_gain')->filter(fn ($value) => $value !== null);

        return [
            'lots' => $lots->count(),
            'animals' => $animals->count(),
            'initial_weight' => $initialWeight,
            'reference_weight' => $referenceWeight,
            'gain_kg' => $gainKg,
            'gain_percentage' => $initialWeight > 0 ? ($gainKg / $initialWeight) * 100 : null,
            'average_daily_gain' => $dailyGains->isNotEmpty() ? (float) $dailyGains->average() : null,
        ];
    }
}
