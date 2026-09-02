<?php

namespace App\Models;

use App\Traits\BelongsToFundoOrGlobal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicamento extends Model
{
    use BelongsToFundoOrGlobal, HasFactory;

    public const TYPES = [
        'antibiotico' => 'Antibiótico',
        'antiparasitario' => 'Antiparasitario',
        'antiinflamatorio' => 'Antiinflamatorio',
        'analgesico_anestesico' => 'Analgésico / anestésico',
        'vitamina_mineral' => 'Vitamina / mineral / reconstituyente',
        'antiseptico' => 'Antiséptico / tópico / cicatrizante',
        'suero_rehidratante' => 'Suero / rehidratante',
        'hormonal_reproductivo' => 'Hormonal / reproductivo',
        'vacuna' => 'Vacuna / biológico',
        'otro' => 'Otro fármaco veterinario',
    ];

    public const UNITS = [
        'ml' => 'ml',
        'dosis' => 'dosis',
        'tableta' => 'tabletas',
        'sobre' => 'sobres',
        'g' => 'g',
        'kg' => 'kg',
        'unidad' => 'unidades',
        'frasco' => 'frascos',
    ];

    public const STORAGE_CONDITIONS = [
        'ambiente' => 'Ambiente seco y protegido',
        'refrigerado_2_8' => 'Refrigerado (2–8 °C)',
        'congelado' => 'Congelado',
        'protegido_luz' => 'Protegido de la luz',
        'otro' => 'Otra condición indicada',
    ];

    public const ROUTES = [
        'oral' => 'Oral',
        'subcutanea' => 'Subcutánea',
        'intramuscular' => 'Intramuscular',
        'intravenosa' => 'Intravenosa',
        'topica' => 'Tópica / baño',
        'intramamaria' => 'Intramamaria',
        'ocular' => 'Ocular',
        'otra' => 'Otra vía',
    ];

    protected $fillable = [
        'fundo_id', 'nombre', 'principio_activo', 'concentracion', 'tipo', 'presentacion',
        'laboratorio', 'registro_sanitario', 'via_predeterminada', 'unidad_stock',
        'stock_minimo', 'condicion_almacenamiento', 'foto_ruta', 'foto_encuadre', 'observaciones', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'stock_minimo' => 'decimal:3',
        'foto_encuadre' => 'array',
    ];

    public function lotes()
    {
        return $this->hasMany(MedicamentoLote::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MedicamentoMovimiento::class);
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TYPES[$this->tipo] ?? ($this->tipo ?: 'Sin tipo');
    }

    public function getUnidadLabelAttribute(): string
    {
        return self::UNITS[$this->unidad_stock] ?? $this->unidad_stock;
    }
}
