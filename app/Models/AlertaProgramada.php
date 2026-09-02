<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Model;

class AlertaProgramada extends Model
{
    use BelongsToFundo;

    protected $table = 'alertas_programadas';

    protected $fillable = [
        'fundo_id', 'animal_id', 'tratamiento_dosis_id', 'tipo', 'fecha_alerta',
        'mensaje', 'leida',
    ];

    protected $casts = [
        'fecha_alerta' => 'date',
        'leida' => 'boolean',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function dosisTratamiento()
    {
        return $this->belongsTo(TratamientoDosis::class, 'tratamiento_dosis_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('leida', false)
            ->where('fecha_alerta', '<=', now());
    }
}
