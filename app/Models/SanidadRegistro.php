<?php

namespace App\Models;

use App\Casts\Lowercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SanidadRegistro extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    public const CATEGORIAS = [
        'lesion' => 'Lesión o herida',
        'enfermedad' => 'Enfermedad o síntomas',
        'parasitos' => 'Parásitos',
        'vacunacion' => 'Vacunación',
        'suplementacion' => 'Vitaminas o suplementos',
        'procedimiento' => 'Procedimiento',
        'control' => 'Control o revisión',
        'otro' => 'Otro evento',
    ];

    public const ESTADOS_SEGUIMIENTO = [
        'en_seguimiento' => 'En seguimiento',
        'completado' => 'Completado',
        'critico' => 'Crítico',
        'cuarentena' => 'Cuarentena',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            $animalFundoId = $model->relationLoaded('animal')
                ? $model->animal?->fundo_id
                : ($model->animal_id ? \Illuminate\Support\Facades\DB::table('animales')->where('id', $model->animal_id)->value('fundo_id') : null);

            if ($animalFundoId !== null && (int) $animalFundoId !== (int) $model->fundo_id) {
                throw new \InvalidArgumentException('El animal no pertenece al fundo del registro sanitario.');
            }
        });
    }

    protected $table = 'sanidad_registros';

    protected $fillable = [
        'fundo_id', 'animal_id', 'categoria_salud', 'subtipo', 'severidad',
        'ubicacion_corporal', 'estado_seguimiento',
        'tipo_evento', 'alcance', 'tipo_intervencion',
        'producto_marca', 'proposito', 'responsable', 'retiro_carne_dias',
        'retiro_leche_horas', 'proxima_dosis',
        'fecha_evento', 'clasificacion',
        'sintomas_diagnostico', 'tratamiento', 'medicamento_id',
        'medicamento_nombre', 'dosis_via', 'estado_clinico', 'evidencia_ruta',
        'fecha_cierre', 'observaciones_cierre',
    ];

    protected $casts = [
        'sintomas_diagnostico' => Lowercase::class,
        'tratamiento' => Lowercase::class,
        'dosis_via' => Lowercase::class,
        'observaciones_cierre' => Lowercase::class,
        'producto_marca' => Lowercase::class,
        'proposito' => Lowercase::class,
        'responsable' => Lowercase::class,
        'fecha_evento' => 'date',
        'fecha_cierre' => 'date',
        'proxima_dosis' => 'date',
        'retiro_carne_dias' => 'integer',
        'retiro_leche_horas' => 'integer',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }

    public function fotos()
    {
        return $this->morphMany(RegistroFoto::class, 'fotografiable')
            ->orderBy('orden')
            ->orderBy('id');
    }

    public function dosisPlan()
    {
        return $this->hasMany(TratamientoDosis::class, 'sanidad_registro_id')
            ->orderBy('numero')
            ->orderBy('id');
    }

    public function dosisAplicadas()
    {
        return $this->dosisPlan()->where('aplicada', true);
    }

    public function dosisPendientes()
    {
        return $this->dosisPlan()->where('aplicada', false);
    }

    public function getCategoriaSaludLabelAttribute(): string
    {
        return self::CATEGORIAS[$this->categoria_salud] ?? self::CATEGORIAS['otro'];
    }

    public function getEstadoSeguimientoLabelAttribute(): string
    {
        return self::ESTADOS_SEGUIMIENTO[$this->estado_seguimiento] ?? self::ESTADOS_SEGUIMIENTO['en_seguimiento'];
    }
}
