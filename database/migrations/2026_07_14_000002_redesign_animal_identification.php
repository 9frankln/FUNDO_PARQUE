<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('especies', function (Blueprint $table) {
            $table->char('codigo_animal', 3)->nullable()->after('nombre');
            $table->unique('codigo_animal', 'especies_codigo_animal_unique');
        });

        Schema::table('animales', function (Blueprint $table) {
            $table->char('codigo_prefijo', 3)->nullable()->after('arete');
            $table->unsignedSmallInteger('codigo_anio')->nullable()->after('codigo_prefijo');
            $table->unsignedInteger('codigo_secuencia')->nullable()->after('codigo_anio');
            $table->string('numero_arete', 50)->nullable()->after('codigo_secuencia');
            $table->date('fecha_nacimiento')->nullable()->after('fecha_alta');
            $table->unsignedSmallInteger('edad_estimada_meses_alta')->nullable()->after('fecha_nacimiento');
            $table->unique(
                ['fundo_id', 'especie_id', 'codigo_anio', 'codigo_secuencia'],
                'animales_codigo_scope_unique'
            );
            $table->unique(['fundo_id', 'numero_arete'], 'animales_numero_arete_unique');
        });

        Schema::create('animal_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('especie_id')->constrained('especies')->cascadeOnDelete();
            $table->unsignedSmallInteger('codigo_anio');
            $table->unsignedInteger('ultimo_numero')->default(0);
            $table->timestamps();
            $table->unique(
                ['fundo_id', 'especie_id', 'codigo_anio'],
                'animal_code_sequences_scope_unique'
            );
        });

        Schema::create('animal_identifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fundo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('animal_id')->constrained('animales')->cascadeOnDelete();
            $table->string('arete', 50);
            $table->char('codigo_prefijo', 3)->nullable();
            $table->unsignedSmallInteger('codigo_anio')->nullable();
            $table->unsignedInteger('codigo_secuencia')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['fundo_id', 'arete'], 'animal_identifiers_fundo_arete_unique');
            $table->index('animal_id', 'animal_identifiers_animal_index');
        });

        $prefixes = [
            'Bovino' => 'BOV',
            'Equino' => 'EQU',
            'Ovino' => 'OVI',
            'Porcino' => 'POR',
            'Cuy' => 'CUY',
            'Ave' => 'AVE',
        ];

        foreach ($prefixes as $name => $prefix) {
            DB::table('especies')->where('nombre', $name)->update(['codigo_animal' => $prefix]);
        }

        $animals = DB::table('animales')
            ->join('especies', 'especies.id', '=', 'animales.especie_id')
            ->whereNotNull('especies.codigo_animal')
            ->orderBy('animales.fundo_id')
            ->orderBy('animales.especie_id')
            ->orderBy('animales.fecha_alta')
            ->orderBy('animales.id')
            ->get([
                'animales.id',
                'animales.fundo_id',
                'animales.especie_id',
                'animales.arete',
                'animales.fecha_alta',
                'especies.codigo_animal as prefix',
            ]);

        foreach ($animals as $animal) {
            DB::table('animal_identifiers')->insertOrIgnore([
                'fundo_id' => $animal->fundo_id,
                'animal_id' => $animal->id,
                'arete' => $animal->arete,
                'created_at' => now(),
            ]);
        }

        foreach ($animals->groupBy(fn ($animal) => implode(':', [
            $animal->fundo_id,
            $animal->especie_id,
            CarbonImmutable::parse($animal->fecha_alta)->year,
        ])) as $group) {
            $prefix = $group->first()->prefix;
            $year = CarbonImmutable::parse($group->first()->fecha_alta)->year;
            $used = [];
            $assignments = [];

            foreach ($group as $animal) {
                $pattern = '/^'.preg_quote($prefix, '/').'-'.$year.'-(\d{5})$/';
                if (preg_match($pattern, $animal->arete, $matches)) {
                    $number = (int) $matches[1];
                    if ($number >= 1 && $number <= 99999 && ! isset($used[$number])) {
                        $used[$number] = true;
                        $assignments[$animal->id] = $number;
                    }
                }
            }

            $next = 1;
            foreach ($group as $animal) {
                if (! isset($assignments[$animal->id])) {
                    while (isset($used[$next])) {
                        $next++;
                    }
                    $assignments[$animal->id] = $next;
                    $used[$next] = true;
                }

                $number = $assignments[$animal->id];
                $code = sprintf('%s-%d-%05d', $prefix, $year, $number);

                DB::table('animales')->where('id', $animal->id)->update([
                    'arete' => $code,
                    'codigo_prefijo' => $prefix,
                    'codigo_anio' => $year,
                    'codigo_secuencia' => $number,
                ]);
                DB::table('animal_identifiers')->insertOrIgnore([
                    'fundo_id' => $animal->fundo_id,
                    'animal_id' => $animal->id,
                    'arete' => $code,
                    'codigo_prefijo' => $prefix,
                    'codigo_anio' => $year,
                    'codigo_secuencia' => $number,
                    'created_at' => now(),
                ]);
            }

            DB::table('animal_code_sequences')->insert([
                'fundo_id' => $group->first()->fundo_id,
                'especie_id' => $group->first()->especie_id,
                'codigo_anio' => $year,
                'ultimo_numero' => max(array_keys($used)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('animales')
            ->where('tipo_alta', 'parto')
            ->update(['fecha_nacimiento' => DB::raw('fecha_alta')]);
        DB::table('animales')
            ->where('tipo_alta', '!=', 'parto')
            ->whereNull('edad_estimada_meses_alta')
            ->update([
                'edad_estimada_meses_alta' => DB::raw("CASE estado_productivo WHEN 'produccion' THEN 36 WHEN 'recria' THEN 18 ELSE 6 END"),
            ]);
    }

    public function down(): void
    {
        if (Schema::hasTable('animal_identifiers')) {
            $originals = DB::table('animal_identifiers')
                ->orderBy('id')
                ->get()
                ->unique('animal_id');

            foreach ($originals as $identifier) {
                DB::table('animales')->where('id', $identifier->animal_id)->update([
                    'arete' => $identifier->arete,
                ]);
            }
        }

        Schema::dropIfExists('animal_identifiers');
        Schema::dropIfExists('animal_code_sequences');

        Schema::table('animales', function (Blueprint $table) {
            $table->dropUnique('animales_codigo_scope_unique');
            $table->dropUnique('animales_numero_arete_unique');
            $table->dropColumn([
                'codigo_prefijo',
                'codigo_anio',
                'codigo_secuencia',
                'numero_arete',
                'fecha_nacimiento',
                'edad_estimada_meses_alta',
            ]);
        });

        Schema::table('especies', function (Blueprint $table) {
            $table->dropUnique('especies_codigo_animal_unique');
            $table->dropColumn('codigo_animal');
        });
    }
};
