<?php

namespace App\Models;

use App\Casts\Lowercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SanidadRegistro extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $table = 'sanidad_registros';

    protected $fillable = [
        'fundo_id', 'animal_id', 'fecha_evento', 'clasificacion',
        'sintomas_diagnostico', 'tratamiento', 'medicamento_id',
        'medicamento_nombre', 'dosis_via', 'estado_clinico', 'evidencia_ruta',
    ];

    protected $casts = [
        'sintomas_diagnostico' => Lowercase::class,
        'tratamiento' => Lowercase::class,
        'dosis_via' => Lowercase::class,
        'fecha_evento' => 'date',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function fotos()
    {
        return $this->morphMany(RegistroFoto::class, 'fotografiable')
            ->orderBy('orden')
            ->orderBy('id');
    }
}
