<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrdenoDetalle extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $ordenoFundoId = $model->relationLoaded('ordeno')
                ? $model->ordeno?->fundo_id
                : ($model->ordeno_id ? DB::table('ordenos')->where('id', $model->ordeno_id)->value('fundo_id') : null);

            $animalFundoId = $model->relationLoaded('animal')
                ? $model->animal?->fundo_id
                : ($model->animal_id ? DB::table('animales')->where('id', $model->animal_id)->value('fundo_id') : null);

            if ($ordenoFundoId !== null && $animalFundoId !== null && (int) $ordenoFundoId !== (int) $animalFundoId) {
                throw new \InvalidArgumentException('El animal no pertenece al mismo fundo que el ordeño.');
            }
        });
    }

    protected $fillable = [
        'ordeno_id', 'animal_id', 'litros',
        'causa_excepcion', 'justificacion_otros',
    ];

    protected $casts = [
        'justificacion_otros' => Uppercase::class,
        'litros' => 'decimal:2',
    ];

    public function ordeno()
    {
        return $this->belongsTo(Ordeno::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class)->withTrashed();
    }
}
