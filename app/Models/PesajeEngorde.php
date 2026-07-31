<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesajeEngorde extends Model
{
    use HasFactory;

    protected $table = 'pesajes_engorde';

    protected $fillable = ['engorde_animal_id', 'fecha', 'peso_kg', 'observaciones'];

    protected $casts = [
        'observaciones' => Uppercase::class,
        'fecha' => 'date',
        'peso_kg' => 'decimal:2',
    ];

    public function engordeAnimal()
    {
        return $this->belongsTo(EngordeAnimal::class, 'engorde_animal_id');
    }
}
