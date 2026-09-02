<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class ProduccionQueso extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $table = 'producciones_queso';

    protected $fillable = [
        'fundo_id', 'fecha', 'unidades', 'peso_total_kg', 'litros_leche_usados',
        'foto_ruta', 'foto_encuadre', 'observaciones',
    ];

    protected $casts = [
        'observaciones' => Uppercase::class,
        'fecha' => 'date',
        'peso_total_kg' => 'decimal:2',
        'litros_leche_usados' => 'decimal:2',
        'foto_encuadre' => 'array',
    ];

    public function presentaciones()
    {
        return $this->hasMany(ProduccionQuesoPresentacion::class, 'produccion_queso_id');
    }

    public function getLitrosPorKgAttribute(): ?float
    {
        if (! $this->litros_leche_usados || ! $this->peso_total_kg || (float) $this->peso_total_kg <= 0) {
            return null;
        }

        return round((float) $this->litros_leche_usados / (float) $this->peso_total_kg, 2);
    }

    public function getRendimientoPorcentajeAttribute(): ?float
    {
        if (! $this->litros_leche_usados || (float) $this->litros_leche_usados <= 0 || ! $this->peso_total_kg) {
            return null;
        }

        return round(((float) $this->peso_total_kg / (float) $this->litros_leche_usados) * 100, 1);
    }

    protected static function booted(): void
    {
        $forgetDashboard = fn (self $production) => Cache::forget('queso.dashboard.v1.'.$production->fundo_id);

        static::saved($forgetDashboard);
        static::deleted($forgetDashboard);
        static::restored($forgetDashboard);
    }
}
