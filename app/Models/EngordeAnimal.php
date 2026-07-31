<?php

namespace App\Models;

use App\Traits\Auditable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EngordeAnimal extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    public const STATE_LABELS = [
        'engorde_activo' => 'Engorde activo',
        'listo_venta' => 'Listo para venta',
        'vendido' => 'Vendido',
        'baja' => 'Baja',
    ];

    protected $table = 'engorde_animales';

    protected $fillable = [
        'lote_id', 'animal_id', 'categoria', 'peso_inicial', 'peso_actual',
        'estado', 'fecha_ingreso', 'fecha_salida', 'observaciones',
    ];

    protected $casts = [
        'peso_inicial' => 'decimal:2',
        'peso_actual' => 'decimal:2',
        'fecha_ingreso' => 'date',
        'fecha_salida' => 'date',
    ];

    public function lote()
    {
        return $this->belongsTo(LoteEngorde::class, 'lote_id');
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class)->withTrashed();
    }

    public function pesajes()
    {
        return $this->hasMany(PesajeEngorde::class, 'engorde_animal_id');
    }

    public function ultimoPesaje()
    {
        return $this->hasOne(PesajeEngorde::class, 'engorde_animal_id')
            ->ofMany(['fecha' => 'max', 'id' => 'max']);
    }

    public function gananciaDiariaKg(): float
    {
        $dias = $this->fecha_ingreso->diffInDays(now());

        return $dias > 0 ? round(($this->peso_actual - $this->peso_inicial) / $dias, 2) : 0;
    }

    public function reportMetrics(?CarbonInterface $asOf = null): array
    {
        $asOf ??= now();
        $lastWeight = $this->relationLoaded('ultimoPesaje')
            ? $this->getRelation('ultimoPesaje')
            : $this->ultimoPesaje()->first();
        $lot = $this->relationLoaded('lote') ? $this->getRelation('lote') : $this->lote()->first();
        $initialWeight = (float) ($this->peso_inicial ?? 0);
        $referenceWeight = $lastWeight
            ? (float) $lastWeight->peso_kg
            : (float) ($this->peso_actual ?? $this->peso_inicial ?? 0);
        $gainKg = $referenceWeight - $initialWeight;
        $gainPercentage = $initialWeight > 0 ? ($gainKg / $initialWeight) * 100 : null;
        $endDate = $this->fecha_salida
            ?? ($lot?->estado === 'cerrado' ? $lot->fecha_fin : null)
            ?? $asOf;
        $daysInFattening = $this->fecha_ingreso && ! $this->fecha_ingreso->isAfter($endDate)
            ? (int) floor($this->fecha_ingreso->diffInDays($endDate))
            : 0;
        $measuredDays = $lastWeight && $this->fecha_ingreso && ! $this->fecha_ingreso->isAfter($lastWeight->fecha)
            ? (int) floor($this->fecha_ingreso->diffInDays($lastWeight->fecha))
            : 0;

        return [
            'initial_weight' => $initialWeight,
            'reference_weight' => $referenceWeight,
            'last_weight' => $lastWeight,
            'gain_kg' => $gainKg,
            'gain_percentage' => $gainPercentage,
            'days_in_fattening' => $daysInFattening,
            'average_daily_gain' => $measuredDays > 0 ? $gainKg / $measuredDays : null,
            'state_label' => self::STATE_LABELS[$this->estado] ?? ucfirst(str_replace('_', ' ', $this->estado)),
        ];
    }
}
