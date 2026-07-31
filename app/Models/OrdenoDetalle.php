<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenoDetalle extends Model
{
    use HasFactory;

    protected $table = 'ordeno_detalles';

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
