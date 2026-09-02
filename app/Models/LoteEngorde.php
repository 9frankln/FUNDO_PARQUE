<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class LoteEngorde extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::deleting(function (self $model) {
            if (! $model->isForceDeleting() && $model->codigo) {
                $model->codigo = $model->codigo . '-DEL-' . $model->id . '-' . time();
                $model->codigo_secuencia = null;
                $model->saveQuietly();
            }
        });

        static::deleted(function (self $model) {
            if ($model->fundo_id && $model->codigo_anio) {
                $maxSeq = (int) DB::table('lotes_engorde')
                    ->where('fundo_id', $model->fundo_id)
                    ->where('codigo_anio', $model->codigo_anio)
                    ->whereNull('deleted_at')
                    ->max('codigo_secuencia');

                DB::table('lote_code_sequences')->updateOrInsert(
                    [
                        'fundo_id' => $model->fundo_id,
                        'codigo_anio' => $model->codigo_anio,
                    ],
                    [
                        'ultimo_numero' => $maxSeq,
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    protected $table = 'lotes_engorde';

    protected $fillable = [
        'fundo_id', 'codigo', 'codigo_anio', 'codigo_secuencia', 'nombre', 'foto_ruta', 'foto_encuadre', 'fecha_inicio', 'fecha_fin',
        'estado', 'observaciones',
    ];

    protected $casts = [
        'codigo' => Uppercase::class,
        'nombre' => Uppercase::class,
        'observaciones' => Uppercase::class,
        'codigo_anio' => 'integer',
        'codigo_secuencia' => 'integer',
        'foto_encuadre' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    public function animales()
    {
        return $this->hasMany(EngordeAnimal::class, 'lote_id');
    }
}
