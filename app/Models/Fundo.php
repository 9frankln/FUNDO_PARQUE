<?php

namespace App\Models;

use App\Casts\Uppercase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fundo extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre', 'ruc', 'direccion', 'departamento',
        'provincia', 'distrito', 'logo_ruta', 'activo',
    ];

    protected $casts = [
        'nombre' => Uppercase::class,
        'activo' => 'boolean',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'fundo_user')
            ->withPivot('es_administrador')
            ->withTimestamps();
    }

    public function animales()
    {
        return $this->hasMany(Animal::class);
    }

    public function ordenos()
    {
        return $this->hasMany(Ordeno::class);
    }

    public function fotosOrdenoDiarias()
    {
        return $this->hasMany(OrdenoFotoDiaria::class);
    }

    public function databaseBackups()
    {
        return $this->hasMany(DatabaseBackup::class);
    }
}
