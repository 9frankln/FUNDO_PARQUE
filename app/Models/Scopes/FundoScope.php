<?php

namespace App\Models\Scopes;

use App\Services\Security\FundoContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FundoScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $fundoId = FundoContext::get();

        if ($fundoId !== null) {
            $builder->where($model->getTable().'.fundo_id', $fundoId);
        }
    }
}
