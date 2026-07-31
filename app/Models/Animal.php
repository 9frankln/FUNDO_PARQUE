<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Animal extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    public const REPRODUCTIVE_STATES = [
        'vacia' => 'Vacía',
        'gestante' => 'Gestante',
        'lactante' => 'Lactante',
        'seca' => 'Seca',
        'en_produccion' => 'En producción',
    ];

    public const ADMISSION_TYPES = [
        'compra' => 'Compra',
        'parto' => 'Nacimiento / parto',
        'donacion' => 'Donación',
        'traslado' => 'Traslado',
        'prestamo' => 'Préstamo',
    ];

    public const INACTIVE_REASONS = [
        'venta' => 'Venta',
        'muerte' => 'Muerte',
        'sacrificio' => 'Sacrificio',
        'traslado' => 'Traslado a otro fundo',
        'extravio' => 'Extravío',
        'devolucion' => 'Devolución',
        'otro' => 'Otro motivo',
    ];

    public const MIN_MILKING_AGE_MONTHS = 24;

    protected $table = 'animales';

    protected $fillable = [
        'fundo_id', 'especie_id', 'raza_id', 'arete', 'nombre', 'genero',
        'peso', 'foto_ruta', 'foto_encuadre', 'estado_productivo', 'estado_reproductivo',
        'tipo_alta', 'precio_compra', 'fecha_alta', 'apta_ordeno', 'activo', 'observaciones',
        'motivo_baja', 'fecha_baja', 'detalle_baja', 'comprador_baja', 'movimiento_venta_id',
        'codigo_prefijo', 'codigo_anio', 'codigo_secuencia',
        'fecha_nacimiento', 'edad_estimada_meses_alta',
    ];

    protected $casts = [
        'arete' => Uppercase::class,
        'nombre' => Uppercase::class,
        'observaciones' => Uppercase::class,
        'detalle_baja' => Uppercase::class,
        'comprador_baja' => Uppercase::class,
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'fecha_nacimiento' => 'date',
        'codigo_anio' => 'integer',
        'codigo_secuencia' => 'integer',
        'edad_estimada_meses_alta' => 'integer',
        'peso' => 'decimal:2',
        'precio_compra' => 'decimal:2',
        'foto_encuadre' => 'array',
        'apta_ordeno' => 'boolean',
        'activo' => 'boolean',
    ];

    public function especie()
    {
        return $this->belongsTo(Especie::class);
    }

    public function raza()
    {
        return $this->belongsTo(Raza::class);
    }

    public function engordes()
    {
        return $this->hasMany(EngordeAnimal::class);
    }

    public function ordenoDetalles()
    {
        return $this->hasMany(OrdenoDetalle::class);
    }

    public function sanidadRegistros()
    {
        return $this->hasMany(SanidadRegistro::class);
    }

    public function partosMadre()
    {
        return $this->hasMany(Parto::class, 'animal_madre_id');
    }

    public function partosCria()
    {
        return $this->hasMany(Parto::class, 'cria_animal_id');
    }

    public function alertas()
    {
        return $this->hasMany(AlertaProgramada::class);
    }

    public function movimientoVenta()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_venta_id')->withTrashed();
    }

    public function edadMeses(?CarbonInterface $date = null): ?int
    {
        $date ??= today();

        if ($this->fecha_nacimiento) {
            if ($this->fecha_nacimiento->isAfter($date)) {
                return null;
            }

            return (int) floor($this->fecha_nacimiento->diffInMonths($date));
        }

        if ($this->edad_estimada_meses_alta !== null && $this->fecha_alta) {
            $elapsed = $this->fecha_alta->isAfter($date)
                ? 0
                : (int) floor($this->fecha_alta->diffInMonths($date));

            return $this->edad_estimada_meses_alta + $elapsed;
        }

        return null;
    }

    public function getEdadTextoAttribute(): string
    {
        if ($this->fecha_nacimiento) {
            $birthDate = $this->fecha_nacimiento->copy()->startOfDay();
            $referenceDate = today()->startOfDay();
            if ($birthDate->isAfter($referenceDate)) {
                return 'Sin edad registrada';
            }

            $interval = $birthDate->diff($referenceDate);
            $parts = [];
            if ($interval->y > 0) {
                $parts[] = $interval->y.' '.($interval->y === 1 ? 'año' : 'años');
            }
            if ($interval->m > 0) {
                $parts[] = $interval->m.' '.($interval->m === 1 ? 'mes' : 'meses');
            }
            if ($interval->d > 0 || $parts === []) {
                $parts[] = $interval->d.' '.($interval->d === 1 ? 'día' : 'días');
            }

            return implode(', ', $parts);
        }

        $months = $this->edadMeses();
        if ($months === null) {
            return 'Sin edad registrada';
        }

        $years = intdiv($months, 12);
        $remainingMonths = $months % 12;
        $parts = [];

        if ($years > 0) {
            $parts[] = $years.' '.($years === 1 ? 'año' : 'años');
        }
        if ($remainingMonths > 0 || $years === 0) {
            $parts[] = $remainingMonths.' '.($remainingMonths === 1 ? 'mes' : 'meses');
        }

        return 'Aprox. '.implode(', ', $parts);
    }

    public function isBovineFemale(): bool
    {
        $speciesCode = mb_strtoupper((string) $this->especie?->codigo_animal);
        $speciesName = mb_strtolower((string) $this->especie?->nombre);

        return $this->genero === 'hembra' && ($speciesCode === 'BOV' || $speciesName === 'bovino');
    }

    public function isMatureBovineFemale(?CarbonInterface $date = null): bool
    {
        $months = $this->edadMeses($date);

        return $this->isBovineFemale()
            && $months !== null
            && $months >= self::MIN_MILKING_AGE_MONTHS;
    }

    public function canBeMarkedForMilking(?CarbonInterface $date = null): bool
    {
        return $this->isMatureBovineFemale($date);
    }

    public function getTipoAltaLabelAttribute(): string
    {
        return self::ADMISSION_TYPES[$this->tipo_alta] ?? '-';
    }

    public function getClasificacionEdadAttribute(): string
    {
        $months = $this->edadMeses();
        if ($months === null) {
            return $this->genero === 'hembra' ? 'Hembra' : 'Macho';
        }

        $species = mb_strtolower((string) $this->especie?->nombre);

        return match ($species) {
            'bovino' => match (true) {
                $months < 6 => $this->genero === 'hembra' ? 'Ternera lactante' : 'Ternero lactante',
                $months < 12 => $this->genero === 'hembra' ? 'Ternera' : 'Ternero',
                $months < 24 => $this->genero === 'hembra' ? 'Novillona' : 'Torete',
                default => $this->genero === 'hembra' ? 'Vaca' : 'Toro',
            },
            'equino' => match (true) {
                $months < 12 => $this->genero === 'hembra' ? 'Potranca' : 'Potro',
                $months < 36 => 'Equino joven',
                default => $this->genero === 'hembra' ? 'Yegua' : 'Caballo',
            },
            'ovino' => match (true) {
                $months < 6 => $this->genero === 'hembra' ? 'Cordera' : 'Cordero',
                $months < 12 => $this->genero === 'hembra' ? 'Borrega' : 'Borrego',
                default => $this->genero === 'hembra' ? 'Oveja' : 'Carnero',
            },
            'porcino' => match (true) {
                $months < 2 => $this->genero === 'hembra' ? 'Lechona' : 'Lechón',
                $months < 8 => 'Porcino joven',
                default => $this->genero === 'hembra' ? 'Cerda' : 'Verraco',
            },
            'caprino' => match (true) {
                $months < 6 => $this->genero === 'hembra' ? 'Cabrita' : 'Cabrito',
                $months < 12 => 'Caprino joven',
                default => $this->genero === 'hembra' ? 'Cabra' : 'Macho cabrío',
            },
            'cuy' => match (true) {
                $months < 1 => 'Cría de cuy',
                $months < 4 => 'Cuy joven',
                default => $this->genero === 'hembra' ? 'Cuy adulta' : 'Cuy adulto',
            },
            'ave' => match (true) {
                $months < 2 => 'Polluelo',
                $months < 6 => 'Ave joven',
                default => 'Ave adulta',
            },
            'camélido' => match (true) {
                $months < 12 => 'Cría de camélido',
                $months < 24 => 'Camélido joven',
                default => $this->genero === 'hembra' ? 'Camélido adulta' : 'Camélido adulto',
            },
            default => match (true) {
                $months < 12 => 'Cría '.($this->genero === 'hembra' ? 'hembra' : 'macho'),
                $months < 24 => $this->genero === 'hembra' ? 'Hembra joven' : 'Macho joven',
                default => $this->genero === 'hembra' ? 'Hembra adulta' : 'Macho adulto',
            },
        };
    }

    public function getDenticionEstimadaAttribute(): ?string
    {
        if (mb_strtolower((string) $this->especie?->nombre) !== 'bovino') {
            return null;
        }

        $months = $this->edadMeses();
        if ($months === null) {
            return null;
        }

        return match (true) {
            $months < 18 => 'Sin dentición permanente estimada',
            $months < 30 => 'Compatible con etapa de 2 dientes',
            $months < 36 => 'Compatible con etapa de 4 dientes',
            $months < 42 => 'Compatible con etapa de 6 dientes',
            default => 'Compatible con boca llena',
        };
    }

    public function getEstadoReproductivoLabelAttribute(): string
    {
        return self::REPRODUCTIVE_STATES[$this->estado_reproductivo] ?? '-';
    }

    public function getMotivoBajaLabelAttribute(): string
    {
        return self::INACTIVE_REASONS[$this->motivo_baja] ?? 'Motivo no registrado';
    }

    public static function productiveStateForAge(?int $months): string
    {
        return match (true) {
            $months === null, $months < 12 => 'cria',
            $months < 24 => 'recria',
            default => 'produccion',
        };
    }
}
