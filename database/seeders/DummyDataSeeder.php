<?php

namespace Database\Seeders;

use App\Models\AlertaProgramada;
use App\Models\Animal;
use App\Models\AsignacionFamiliar;
use App\Models\CategoriaFinanciera;
use App\Models\EngordeAnimal;
use App\Models\Especie;
use App\Models\Fundo;
use App\Models\LoteEngorde;
use App\Models\Medicamento;
use App\Models\Movimiento;
use App\Models\Ordeno;
use App\Models\Parto;
use App\Models\PesajeEngorde;
use App\Models\ProduccionQueso;
use App\Models\ProfilaxisRegistro;
use App\Models\Raza;
use App\Models\SanidadRegistro;
use App\Support\AnimalCodeAllocator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $fundo = Fundo::where('nombre', 'FUNDO PARQUE')->first() ?? Fundo::first();

        if (! $fundo) {
            $this->command?->warn('No existe un fundo. Ejecute FundoSeeder primero.');

            return;
        }

        $bovino = Especie::where('nombre', 'Bovino')->first();
        $holstein = Raza::where('especie_id', $bovino?->id)->whereIn('nombre', ['Holstein Friesian', 'Holstein'])->first();
        $brownSwiss = Raza::where('especie_id', $bovino?->id)->where('nombre', 'Brown Swiss')->first();

        if (! $bovino || ! $holstein || ! $brownSwiss) {
            $this->command?->warn('Faltan los catálogos Bovino, Holstein o Brown Swiss.');

            return;
        }

        DB::transaction(function () use ($fundo, $bovino, $holstein, $brownSwiss): void {
            $today = today();
            $fundoId = $fundo->id;
            $restore = static function ($model) {
                if (method_exists($model, 'trashed') && $model->trashed()) {
                    $model->restore();
                }

                return $model;
            };
            $allocator = app(AnimalCodeAllocator::class);
            $upsertAnimal = static function (string $name, array $attributes) use ($allocator, $bovino, $fundoId, $restore): Animal {
                $animal = Animal::withTrashed()
                    ->where('fundo_id', $fundoId)
                    ->where('nombre', Str::upper($name))
                    ->first();

                if ($animal) {
                    $restore($animal);
                } else {
                    $animal = new Animal;
                }

                if (! $animal->codigo_secuencia) {
                    $code = $allocator->allocate(
                        $animal,
                        $fundoId,
                        $bovino,
                        now()->year
                    );
                    $attributes = [...$attributes, ...$code];
                }

                $animal->fill([
                    'fundo_id' => $fundoId,
                    'nombre' => $name,
                    ...$attributes,
                ])->save();
                $allocator->record($animal);

                return $animal;
            };

            $femaleNames = ['Lola', 'Blanca', 'Pecosa', 'Clara', 'Estrella', 'Canela', 'Luna', 'Mora', 'Aurora', 'Nube'];
            $maleNames = ['Hércules', 'Trueno', 'Rambo', 'Fighter', 'Tauro', 'Titán', 'Bruno', 'Rayo', 'Canelo', 'Sultán'];
            $cows = [];
            $steers = [];

            for ($i = 1; $i <= 10; $i++) {
                $cows[] = $upsertAnimal('Vaca '.$femaleNames[$i - 1], [
                    'especie_id' => $bovino->id,
                    'raza_id' => $holstein->id,
                    'genero' => 'hembra',
                    'peso' => 440 + ($i * 12),
                    'estado_productivo' => 'produccion',
                    'estado_reproductivo' => $i % 3 === 0 ? 'gestante' : 'lactante',
                    'tipo_alta' => 'compra',
                    'fecha_alta' => $today->copy()->subMonths(18 + $i),
                    'edad_estimada_meses_alta' => 36,
                    'apta_ordeno' => true,
                    'activo' => true,
                    'observaciones' => 'Ejemplar lechero de demostración con control productivo activo.',
                ]);

                $steers[] = $upsertAnimal('Torete '.$maleNames[$i - 1], [
                    'especie_id' => $bovino->id,
                    'raza_id' => $brownSwiss->id,
                    'genero' => 'macho',
                    'peso' => 290 + ($i * 18),
                    'estado_productivo' => 'recria',
                    'estado_reproductivo' => null,
                    'tipo_alta' => 'compra',
                    'fecha_alta' => $today->copy()->subMonths(7 + $i),
                    'edad_estimada_meses_alta' => 18,
                    'apta_ordeno' => false,
                    'activo' => true,
                    'observaciones' => 'Ejemplar de demostración destinado al programa de engorde.',
                ]);
            }

            $lots = [];
            foreach (range('A', 'J') as $index => $letter) {
                $lots[] = $restore(LoteEngorde::withTrashed()->updateOrCreate(
                    ['fundo_id' => $fundoId, 'codigo' => 'LOTE-2026-'.$letter],
                    [
                        'nombre' => 'Lote de engorde '.($index + 1),
                        'fecha_inicio' => $today->copy()->subDays(75 - ($index * 4)),
                        'fecha_fin' => $index >= 8 ? $today->copy()->subDays(2 + $index) : null,
                        'estado' => $index >= 8 ? 'cerrado' : 'activo',
                        'observaciones' => 'Lote demostrativo con dieta y seguimiento de peso controlados.',
                    ]
                ));
            }

            foreach ($steers as $index => $steer) {
                $lot = $index < 5 ? $lots[0] : $lots[$index - 4];
                $initialWeight = (float) $steer->peso - 32;
                $fattening = $restore(EngordeAnimal::withTrashed()->updateOrCreate(
                    ['lote_id' => $lot->id, 'animal_id' => $steer->id],
                    [
                        'categoria' => $index < 4 ? 'toreton' : 'ternero',
                        'peso_inicial' => $initialWeight,
                        'peso_actual' => $steer->peso,
                        'estado' => 'engorde_activo',
                        'fecha_ingreso' => $today->copy()->subDays(60 - ($index * 2)),
                        'fecha_salida' => null,
                        'observaciones' => 'Ingreso saludable; control quincenal programado.',
                    ]
                ));

                PesajeEngorde::updateOrCreate(
                    [
                        'engorde_animal_id' => $fattening->id,
                        'fecha' => $today->copy()->subDays(20 + $index),
                    ],
                    [
                        'peso_kg' => $initialWeight + 17 + $index,
                        'observaciones' => 'Pesaje demostrativo de control intermedio.',
                    ]
                );
            }

            for ($day = 1; $day <= 10; $day++) {
                $date = $today->copy()->subDays($day);
                $milking = $restore(Ordeno::withTrashed()->updateOrCreate(
                    ['fundo_id' => $fundoId, 'fecha' => $date, 'turno' => $day % 2 === 0 ? 'manana' : 'tarde'],
                    [
                        'tipo_registro' => 'individual',
                        'litros_total' => 0,
                        'cantidad_vacas' => count($cows),
                        'observaciones' => 'Ordeño demostrativo diario con control individual.',
                    ]
                ));

                $totalLiters = 0;
                foreach ($cows as $index => $cow) {
                    $hasException = $index === (($day + 2) % 10) && $day % 4 === 0;
                    $liters = $hasException ? 0 : 10.5 + ($index * 0.75) + ($day * 0.2);
                    $milking->detalles()->updateOrCreate(
                        ['animal_id' => $cow->id],
                        [
                            'litros' => $liters,
                            'causa_excepcion' => $hasException ? 'mastitis' : null,
                            'justificacion_otros' => null,
                        ]
                    );
                    $totalLiters += $liters;
                }

                $milking->update(['litros_total' => $totalLiters]);
            }

            // Seed Queso productions over the past 12 months for visual trends
            for ($monthOffset = 11; $monthOffset >= 0; $monthOffset--) {
                $monthDate = $today->copy()->subMonths($monthOffset);

                // Seed 3 records per month on the 5th, 15th, and 25th
                foreach ([5, 15, 25] as $dayOfMonth) {
                    $date = $monthDate->copy()->day($dayOfMonth);
                    if ($date->isAfter($today)) {
                        continue;
                    }

                    // Generate variable quantities to make graphs look realistic and dynamic
                    $multiplier = (($monthOffset % 3) + 1) * 3 + ($dayOfMonth % 5);
                    $queso500g = 4 + $multiplier;
                    $queso1kg = 2 + ($multiplier * 2);
                    $queso2kg = ($monthOffset % 2 === 0) ? (1 + ($multiplier % 3)) : 0;

                    $totalUnits = $queso500g + $queso1kg + $queso2kg;
                    $totalWeight = ($queso500g * 0.5) + ($queso1kg * 1.0) + ($queso2kg * 2.0);

                    $produccionQueso = $restore(ProduccionQueso::withTrashed()->updateOrCreate(
                        ['fundo_id' => $fundoId, 'fecha' => $date],
                        [
                            'unidades' => $totalUnits,
                            'peso_total_kg' => $totalWeight,
                            'observaciones' => "Producción de queso del {$date->format('d/m/Y')}.",
                        ]
                    ));

                    $produccionQueso->presentaciones()->delete();

                    $presentacionesData = [
                        ['peso_gramos' => 500, 'cantidad' => $queso500g],
                        ['peso_gramos' => 1000, 'cantidad' => $queso1kg],
                    ];
                    if ($queso2kg > 0) {
                        $presentacionesData[] = ['peso_gramos' => 2000, 'cantidad' => $queso2kg];
                    }

                    $produccionQueso->presentaciones()->createMany($presentacionesData);
                }
            }

            $incomeCategories = CategoriaFinanciera::where('tipo', 'ingreso')->where('activo', true)->get();
            $expenseCategories = CategoriaFinanciera::where('tipo', 'egreso')->where('activo', true)->get();
            $purposes = ['estudio', 'salud', 'alimentacion', 'vivienda', 'transporte', 'ropa', 'gastos_personales', 'emergencia', 'otros', 'estudio'];
            $beneficiaries = ['Sofía Delgado', 'Carlos Delgado', 'María Delgado', 'Lucía Delgado', 'Mateo Delgado', 'Elena Vargas', 'José Delgado', 'Rosa Delgado', 'Ana Torres', 'Diego Delgado'];

            for ($i = 1; $i <= 10; $i++) {
                $isIncome = $i % 2 === 0;
                $categories = $isIncome ? $incomeCategories : $expenseCategories;
                $description = 'DEMO-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).' - '.($isIncome ? 'Venta de producción agropecuaria.' : 'Compra de insumos para el fundo.');

                $restore(Movimiento::withTrashed()->updateOrCreate(
                    ['fundo_id' => $fundoId, 'descripcion' => Str::upper($description)],
                    [
                        'tipo' => $isIncome ? 'ingreso' : 'egreso',
                        'categoria_id' => $categories[($i - 1) % $categories->count()]->id,
                        'monto' => $isIncome ? 950 + ($i * 125) : 280 + ($i * 55),
                        'moneda' => 'PEN',
                        'fecha' => $today->copy()->subDays($i),
                    ]
                ));

                $restore(AsignacionFamiliar::withTrashed()->updateOrCreate(
                    ['fundo_id' => $fundoId, 'descripcion' => Str::upper('Asignación de demostración '.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'.')],
                    [
                        'beneficiario' => $beneficiaries[$i - 1],
                        'monto' => 180 + ($i * 70),
                        'moneda' => 'PEN',
                        'fecha' => $today->copy()->subDays($i * 2),
                        'proposito' => $purposes[$i - 1],
                    ]
                ));
            }

            $medicines = Medicamento::where('activo', true)->get();
            $diagnoses = ['Mastitis clínica leve', 'Timpanismo por pastura', 'Cojera por contusión', 'Indigestión simple', 'Herida superficial', 'Fiebre moderada', 'Irritación ocular', 'Parasitismo interno', 'Deficiencia vitamínica', 'Inflamación de pezuña'];
            $clinicalStates = ['en_tratamiento', 'recuperada', 'cuarentena', 'recuperada', 'en_tratamiento', 'critico', 'recuperada', 'en_tratamiento', 'recuperada', 'cuarentena'];
            $interventions = ['vacuna', 'desparasitante_interno', 'vitamina', 'desparasitante_externo'];
            $products = ['Aftogan', 'Dectomax', 'Vigantol', 'Butox', 'Clostrisan', 'Ivermex', 'Complejo ADE', 'Fipronil Plus', 'Carbunvac', 'Albendazol'];

            for ($i = 1; $i <= 10; $i++) {
                $cow = $cows[$i - 1];
                $restore(SanidadRegistro::withTrashed()->updateOrCreate(
                    [
                        'fundo_id' => $fundoId,
                        'animal_id' => $cow->id,
                        'fecha_evento' => $today->copy()->subDays($i * 4),
                    ],
                    [
                        'clasificacion' => ['enfermedad_infecciosa', 'trastorno_metabolico', 'lesion_accidente'][($i - 1) % 3],
                        'sintomas_diagnostico' => $diagnoses[$i - 1],
                        'tratamiento' => 'Tratamiento demostrativo con seguimiento veterinario.',
                        'medicamento_id' => $medicines[($i - 1) % $medicines->count()]->id,
                        'dosis_via' => ($i + 4).' ml por vía intramuscular',
                        'estado_clinico' => $clinicalStates[$i - 1],
                    ]
                ));

                $applicationDate = $today->copy()->subDays($i * 3);
                $prophylaxis = $restore(ProfilaxisRegistro::withTrashed()->updateOrCreate(
                    [
                        'fundo_id' => $fundoId,
                        'fecha_aplicacion' => $applicationDate,
                        'producto_marca' => $products[$i - 1],
                    ],
                    [
                        'alcance' => $i % 3 === 0 ? 'lote' : 'individual',
                        'tipo_intervencion' => $interventions[($i - 1) % count($interventions)],
                        'proposito' => 'Programa demostrativo de prevención sanitaria.',
                        'dosis' => ($i % 3 + 1).' ml',
                        'proxima_dosis' => $today->copy()->addDays(30 + ($i * 7)),
                        'responsable' => 'Dr. Manuel Torres',
                        'observaciones' => 'Aplicación sin reacciones adversas.',
                    ]
                ));
                $prophylaxis->animales()->sync([$cow->id]);

                $birthDate = $today->copy()->subDays($i * 6);
                $calf = $upsertAnimal('Cría de '.$cow->nombre, [
                    'especie_id' => $bovino->id,
                    'raza_id' => $i % 2 === 0 ? $holstein->id : $brownSwiss->id,
                    'genero' => $i % 2 === 0 ? 'hembra' : 'macho',
                    'peso' => 29 + ($i * 1.4),
                    'estado_productivo' => 'cria',
                    'estado_reproductivo' => $i % 2 === 0 ? 'vacia' : null,
                    'tipo_alta' => 'parto',
                    'fecha_alta' => $birthDate,
                    'fecha_nacimiento' => $birthDate,
                    'apta_ordeno' => false,
                    'activo' => true,
                    'observaciones' => 'Cría registrada automáticamente desde un parto demostrativo.',
                ]);

                $restore(Parto::withTrashed()->updateOrCreate(
                    [
                        'fundo_id' => $fundoId,
                        'animal_madre_id' => $cow->id,
                        'fecha_parto' => $today->copy()->subDays($i * 6),
                    ],
                    [
                        'cria_animal_id' => $calf->id,
                        'tipo_parto' => $i % 4 === 0 ? 'asistido' : 'normal',
                        'cria_sexo' => $calf->genero,
                        'cria_peso_nacer' => $calf->peso,
                        'cria_estado' => $i === 7 ? 'debil' : 'vivo_vigoroso',
                        'condicion_madre' => $i === 4 ? 'retencion_placenta' : 'optima',
                        'observaciones' => 'Parto demostrativo con control posterior de madre y cría.',
                    ]
                ));

                $alertType = ['proxima_dosis', 'cuarentena', 'secado'][($i - 1) % 3];
                AlertaProgramada::updateOrCreate(
                    [
                        'fundo_id' => $fundoId,
                        'animal_id' => $cow->id,
                        'tipo' => $alertType,
                        'fecha_alerta' => $today->copy()->addDays($i * 3),
                    ],
                    [
                        'mensaje' => 'Alerta demostrativa '.str_pad((string) $i, 2, '0', STR_PAD_LEFT).' para seguimiento de '.$alertType.'.',
                        'leida' => $i > 8,
                    ]
                );
            }

            DB::table('configuracion_sistema')->updateOrInsert(
                ['fundo_id' => $fundoId, 'clave' => 'moneda'],
                ['valor' => 'PEN', 'created_at' => now(), 'updated_at' => now()]
            );
            DB::table('configuracion_sistema')->updateOrInsert(
                ['fundo_id' => $fundoId, 'clave' => 'alerta_dias'],
                ['valor' => '7', 'created_at' => now(), 'updated_at' => now()]
            );
        });

        $this->command?->info('Datos de demostración creados o actualizados: 10 registros por formulario operativo.');
    }
}
