<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    use HasFactory;

    protected $fillable = ['modulo', 'accion'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'rol_permisos', 'permiso_id', 'rol_id');
    }
}
