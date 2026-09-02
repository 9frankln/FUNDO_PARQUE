<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Parto extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $madreFundoId = $model->relationLoaded('madre')
                ? $model->madre?->fundo_id
                : ($model->animal_madre_id ? DB::table('animales')->where('id', $model->animal_madre_id)->value('fundo_id') : null);

            if ($madreFundoId !== null && (int) $madreFundoId !== (int) $model->fundo_id) {
                throw new \InvalidArgumentException('La madre no pertenece al fundo del parto.');
            }

            $criaFundoId = $model->relationLoaded('cria')
                ? $model->cria?->fundo_id
                : ($model->cria_animal_id ? DB::table('animales')->where('id', $model->cria_animal_id)->value('fundo_id') : null);

            if ($criaFundoId !== null && (int) $criaFundoId !== (int) $model->fundo_id) {
                throw new \InvalidArgumentException('La cría no pertenece al fundo del parto.');
            }
        });
    }

    protected $fillable = [
        'fundo_id', 'animal_madre_id', 'cria_animal_id', 'fecha_parto',
        'tipo_parto', 'cria_sexo', 'cria_peso_nacer',
        'cria_estado', 'condicion_madre', 'observaciones',
    ];

    protected $casts = [
        'observaciones' => Uppercase::class,
        'fecha_parto' => 'date',
        'cria_peso_nacer' => 'decimal:2',
    ];

    public function madre()
    {
        return $this->belongsTo(Animal::class, 'animal_madre_id');
    }

    public function cria()
    {
        return $this->belongsTo(Animal::class, 'cria_animal_id');
    }

    public function fotos()
    {
        return $this->morphMany(RegistroFoto::class, 'fotografiable')
            ->orderBy('orden')
            ->orderBy('id');
    }
}
