<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProfilaxisRegistro extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $table = 'profilaxis_registros';

    protected $fillable = [
        'fundo_id', 'alcance', 'fecha_aplicacion', 'tipo_intervencion',
        'proposito', 'producto_marca', 'dosis', 'proxima_dosis',
        'responsable', 'observaciones',
    ];

    protected $casts = [
        'proposito' => Uppercase::class,
        'producto_marca' => Uppercase::class,
        'dosis' => Uppercase::class,
        'responsable' => Uppercase::class,
        'observaciones' => Uppercase::class,
        'fecha_aplicacion' => 'date',
        'proxima_dosis' => 'date',
    ];

    public function animales()
    {
        return $this->belongsToMany(Animal::class, 'profilaxis_animales', 'profilaxis_id', 'animal_id')
            ->withTimestamps();
    }

    public function dosisProgramadas()
    {
        return $this->hasMany(ProfilaxisDosisProgramada::class, 'profilaxis_id')
            ->orderBy('fecha_programada')
            ->orderBy('id');
    }

    public function fotos()
    {
        return $this->morphMany(RegistroFoto::class, 'fotografiable')
            ->orderBy('orden')
            ->orderBy('id');
    }

    public function fechasDosisProgramadas()
    {
        $doses = $this->relationLoaded('dosisProgramadas')
            ? $this->dosisProgramadas
            : $this->dosisProgramadas()->get();
        $dates = $doses->pluck('fecha_programada')->values();

        return $dates->isEmpty() && $this->proxima_dosis
            ? collect([$this->proxima_dosis])
            : $dates;
    }
}
