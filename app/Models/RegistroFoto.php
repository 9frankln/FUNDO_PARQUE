<?php

namespace App\Models;

use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroFoto extends Model
{
    use BelongsToFundo, HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (self $foto): void {
            if ($foto->ruta && \Illuminate\Support\Facades\Storage::disk('local')->exists($foto->ruta)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($foto->ruta);
            }
        });
    }

    protected $fillable = [
        'fundo_id', 'ruta', 'encuadre', 'orden',
    ];

    protected $casts = [
        'orden' => 'integer',
        'encuadre' => 'array',
    ];

    public function fotografiable()
    {
        return $this->morphTo();
    }
}
