<?php

namespace App\Models;

use App\Casts\Lowercase;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TratamientoDosis extends Model
{
    use BelongsToFundo, HasFactory;

    protected $table = 'tratamiento_dosis';

    protected $fillable = [
        'fundo_id', 'sanidad_registro_id', 'numero',
        'medicamento_id', 'medicamento_nombre', 'dosis', 'cantidad_inventario',
        'unidad_inventario', 'via',
        'fecha_programada', 'fecha_aplicada', 'aplicada', 'responsable',
    ];

    protected $casts = [
        'numero' => 'integer',
        'fecha_programada' => 'date',
        'fecha_aplicada' => 'date',
        'aplicada' => 'boolean',
        'cantidad_inventario' => 'decimal:3',
        'medicamento_nombre' => Lowercase::class,
        'dosis' => Lowercase::class,
        'via' => Lowercase::class,
        'responsable' => Lowercase::class,
    ];

    public function casoClinico()
    {
        return $this->eventoSalud();
    }

    public function eventoSalud()
    {
        return $this->belongsTo(SanidadRegistro::class, 'sanidad_registro_id');
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function alerta()
    {
        return $this->hasOne(AlertaProgramada::class, 'tratamiento_dosis_id');
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MedicamentoMovimiento::class, 'tratamiento_dosis_id');
    }
}
