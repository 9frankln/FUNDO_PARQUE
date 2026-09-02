<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Movimiento extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $fillable = [
        'fundo_id', 'tipo', 'categoria_id', 'monto', 'moneda', 'beneficiario', 'proposito',
        'fecha', 'descripcion', 'comprobante_ruta', 'comprobante_encuadre',
    ];

    protected $casts = [
        'beneficiario' => Uppercase::class,
        'descripcion' => Uppercase::class,
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'comprobante_encuadre' => 'array',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaFinanciera::class, 'categoria_id');
    }

    public function animalesVendidos()
    {
        return $this->hasMany(Animal::class, 'movimiento_venta_id');
    }

    public function compraMedicamento()
    {
        return $this->hasOne(MedicamentoLote::class, 'movimiento_id');
    }

    public function compraInsumo()
    {
        return $this->hasOne(InsumoLote::class, 'movimiento_id');
    }

    public function descripcionLegible(): ?string
    {
        $description = trim((string) $this->descripcion);

        if ($description === '' || preg_match('/^\[VENTA ANIMALES:/iu', $description) !== 1) {
            return $description !== '' ? $description : null;
        }

        $description = preg_replace('/^\[VENTA ANIMALES:[^\]]+\]\s*/iu', '', $description) ?? $description;
        $description = preg_replace('/^\[A:\s*[^\]]+\]\s*/iu', '', $description) ?? $description;
        $description = preg_replace('/^\s*-\s*/u', '', $description) ?? $description;
        $description = trim($description);

        return $description !== '' ? $description : null;
    }

    public function compradorVentaAnimal(): ?string
    {
        if (preg_match('/\[A:\s*([^\]]+)\]/iu', (string) $this->descripcion, $matches) !== 1) {
            return null;
        }

        $buyer = trim($matches[1]);

        return $buyer !== '' ? $buyer : null;
    }

    public function comprobanteEsImagen(): bool
    {
        return $this->comprobante_ruta !== null
            && preg_match('/\.(jpe?g|png|webp)$/i', $this->comprobante_ruta) === 1;
    }

    public function comprobanteEsPdf(): bool
    {
        return $this->comprobante_ruta !== null
            && str_ends_with(strtolower($this->comprobante_ruta), '.pdf');
    }

    public function esAsignacionFamiliar(): bool
    {
        return ! empty($this->beneficiario);
    }

    public function compradorVenta(): ?string
    {
        return $this->compradorVentaAnimal();
    }

    public function fotoCompartidaUrl(): ?string
    {
        if ($this->compraMedicamento?->medicamento?->foto_ruta) {
            return asset('storage/'.$this->compraMedicamento->medicamento->foto_ruta);
        }
        if ($this->compraInsumo?->insumo?->foto_ruta) {
            return asset('storage/'.$this->compraInsumo->insumo->foto_ruta);
        }

        return null;
    }

    public function fotoCompartidaEnlace(): ?string
    {
        if ($this->compraMedicamento?->medicamento_id) {
            return route('medicamentos.show', $this->compraMedicamento->medicamento_id);
        }
        if ($this->compraInsumo?->insumo_id) {
            return route('insumos.show', $this->compraInsumo->insumo_id);
        }

        return null;
    }

    public function fotoCompartidaEncuadre(): array
    {
        if ($this->compraMedicamento?->medicamento?->foto_encuadre) {
            return \App\Support\ImageFrame::normalize($this->compraMedicamento->medicamento->foto_encuadre);
        }
        if ($this->compraInsumo?->insumo?->foto_encuadre) {
            return \App\Support\ImageFrame::normalize($this->compraInsumo->insumo->foto_encuadre);
        }

        return ['x' => 50, 'y' => 50, 'zoom' => 1];
    }

    public function fotoCompartidaTitulo(): ?string
    {
        if ($this->compraMedicamento?->medicamento?->nombre) {
            return 'Foto de '.$this->compraMedicamento->medicamento->nombre;
        }
        if ($this->compraInsumo?->insumo?->nombre) {
            return 'Foto de '.$this->compraInsumo->insumo->nombre;
        }

        return null;
    }
}
