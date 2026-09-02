<?php

namespace App\Models;

use App\Traits\BelongsToFundoOrGlobal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use BelongsToFundoOrGlobal, HasFactory;

    public const TYPES = [
        'material_descartable' => 'Material descartable (jeringas, agujas, guantes)',
        'antiseptico_desinfectante' => 'Antiséptico y desinfectante (alcohol, agua oxigenada, yodo)',
        'material_curacion' => 'Material de curación (gasas, algodón, vendas)',
        'instrumental_accesorio' => 'Instrumental / accesorio (termómetro, bisturí, tubos)',
        'higiene_limpieza' => 'Higiene y limpieza pecuaria',
        'otro' => 'Otro insumo / material',
    ];

    public const UNITS = [
        'unidad' => 'unidades',
        'par' => 'pares',
        'frasco' => 'frascos',
        'paquete' => 'paquetes',
        'caja' => 'cajas',
        'rollo' => 'rollos',
        'tubo' => 'tubos',
        'bolsa' => 'bolsas',
        'litro' => 'litros',
        'ml' => 'ml',
        'g' => 'g',
        'kg' => 'kg',
    ];

    public const STORAGE_CONDITIONS = [
        'ambiente' => 'Ambiente seco y protegido',
        'refrigerado_2_8' => 'Refrigerado (2–8 °C)',
        'congelado' => 'Congelado',
        'protegido_luz' => 'Protegido de la luz',
        'otro' => 'Otra condición indicada',
    ];

    protected $table = 'insumos';

    protected $fillable = [
        'fundo_id', 'nombre', 'tipo', 'presentacion', 'marca_laboratorio',
        'unidad_stock', 'stock_minimo', 'condicion_almacenamiento',
        'ubicacion_predeterminada', 'foto_ruta', 'foto_encuadre',
        'observaciones', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'stock_minimo' => 'decimal:3',
        'foto_encuadre' => 'array',
    ];

    public function lotes()
    {
        return $this->hasMany(InsumoLote::class);
    }

    public function movimientos()
    {
        return $this->hasMany(InsumoMovimiento::class);
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
