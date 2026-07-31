<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionSistema extends Model
{
    use BelongsToFundo;

    protected $table = 'configuracion_sistema';

    protected $fillable = ['fundo_id', 'clave', 'valor'];

    public static function obtener(string $clave, $default = null)
    {
        $config = static::where('clave', $clave)->first();

        return $config ? $config->valor : $default;
    }

    public static function establecer(string $clave, $valor): void
    {
        static::updateOrCreate(
            ['clave' => $clave, 'fundo_id' => session('fundo_id')],
            ['valor' => $valor]
        );
    }
}
