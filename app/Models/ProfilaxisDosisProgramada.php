<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfilaxisDosisProgramada extends Model
{
    protected $table = 'profilaxis_dosis_programadas';

    protected $fillable = ['profilaxis_id', 'fecha_programada'];

    protected $casts = ['fecha_programada' => 'date'];

    public function profilaxis()
    {
        return $this->belongsTo(ProfilaxisRegistro::class, 'profilaxis_id');
    }

    public function alertas()
    {
        return $this->hasMany(AlertaProgramada::class, 'profilaxis_dosis_id');
    }
}
