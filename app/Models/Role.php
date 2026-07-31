<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = ['fundo_id', 'nombre', 'descripcion', 'es_protegido'];

    protected function casts(): array
    {
        return [
            'es_protegido' => 'boolean',
        ];
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'rol_permisos', 'rol_id', 'permiso_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'user_roles', 'rol_id', 'user_id');
    }
}
