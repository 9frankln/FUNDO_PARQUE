<?php

namespace App\Models;

use App\Casts\Uppercase;
use App\Traits\Auditable;
use App\Traits\BelongsToFundo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ordeno extends Model
{
    use Auditable, BelongsToFundo, HasFactory, SoftDeletes;

    protected $fillable = [
        'fundo_id', 'fecha', 'turno', 'tipo_registro',
        'litros_total', 'cantidad_vacas', 'observaciones',
    ];

    protected $casts = [
        'observaciones' => Uppercase::class,
        'fecha' => 'date',
        'litros_total' => 'decimal:2',
    ];

    public function detalles()
    {
        return $this->hasMany(OrdenoDetalle::class);
    }

    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['fechaDesde'] ?? null, fn (Builder $q, $value) => $q->where('fecha', '>=', $value))
            ->when($filters['fechaHasta'] ?? null, fn (Builder $q, $value) => $q->where('fecha', '<=', $value))
            ->when($filters['turno'] ?? null, fn (Builder $q, $value) => $q->where('turno', $value))
            ->when($filters['tipoRegistro'] ?? null, fn (Builder $q, $value) => $q->where('tipo_registro', $value))
            ->when(($filters['litrosMin'] ?? '') !== '', fn (Builder $q) => $q->where('litros_total', '>=', $filters['litrosMin']))
            ->when(($filters['litrosMax'] ?? '') !== '', fn (Builder $q) => $q->where('litros_total', '<=', $filters['litrosMax']))
            ->when($filters['observacion'] ?? null, fn (Builder $q, $value) => $q->where('observaciones', 'like', '%'.$value.'%'))
            ->when(($filters['conFoto'] ?? '') !== '', function (Builder $q) use ($filters) {
                $method = $filters['conFoto'] === '1' ? 'whereExists' : 'whereNotExists';

                $q->{$method}(fn ($photo) => $photo
                    ->selectRaw('1')
                    ->from('ordeno_fotos_diarias')
                    ->whereColumn('ordeno_fotos_diarias.fundo_id', 'ordenos.fundo_id')
                    ->whereColumn('ordeno_fotos_diarias.fecha', 'ordenos.fecha'));
            });
    }

    public static function turnoLabel(string $turno): string
    {
        return match ($turno) {
            'manana' => 'Mañana',
            'tarde' => 'Tarde',
            'noche' => 'Noche',
            default => ucfirst($turno),
        };
    }

    public static function tipoLabel(string $tipo): string
    {
        return $tipo === 'individual' ? 'Individual' : 'Lote';
    }
}
