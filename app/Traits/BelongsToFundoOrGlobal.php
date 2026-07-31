<?php

namespace App\Traits;

use App\Models\Fundo;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToFundoOrGlobal
{
    protected static function bootBelongsToFundoOrGlobal(): void
    {
        static::addGlobalScope('fundo', function (Builder $query): void {
            if (! session()->has('fundo_id')) {
                return;
            }

            $column = $query->getModel()->qualifyColumn('fundo_id');
            $query->where(fn (Builder $scope) => $scope
                ->where($column, session('fundo_id'))
                ->orWhereNull($column));
        });

        static::creating(function ($model): void {
            if (session()->has('fundo_id') && ! $model->fundo_id) {
                $model->fundo_id = session('fundo_id');
            }
        });
    }

    public function fundo()
    {
        return $this->belongsTo(Fundo::class);
    }
}
