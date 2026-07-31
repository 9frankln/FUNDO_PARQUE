<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Model;

class AlertaProgramada extends Model
{
    use BelongsToFundo;

    protected $table = 'alertas_programadas';

    protected $fillable = [
        'fundo_id', 'animal_id', 'profilaxis_dosis_id', 'tipo', 'fecha_alerta',
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

    public function dosisProfilaxis()
    {
        return $this->belongsTo(ProfilaxisDosisProgramada::class, 'profilaxis_dosis_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('leida', false)
            ->where('fecha_alerta', '<=', now());
    }
}
