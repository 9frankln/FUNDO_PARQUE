<?php

namespace App\Models;

use App\Traits\BelongsToFundoOrGlobal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaFinanciera extends Model
{
    use BelongsToFundoOrGlobal, HasFactory;

    protected $table = 'categorias_financieras';

    protected $fillable = ['fundo_id', 'tipo', 'nombre', 'activo'];

    protected $casts = ['activo' => 'boolean'];

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'categoria_id');
    }
}
