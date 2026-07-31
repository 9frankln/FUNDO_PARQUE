<?php

namespace App\Traits;

use App\Models\Fundo;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToFundo
{
    protected static function bootBelongsToFundo(): void
    {
        static::addGlobalScope('fundo', function (Builder $query) {
            if (session()->has('fundo_id')) {
                $query->where($query->getModel()->getTable().'.fundo_id', session('fundo_id'));
            }
        });

        static::creating(function ($model) {
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
