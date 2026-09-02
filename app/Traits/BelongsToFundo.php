<?php

namespace App\Traits;

use App\Models\Scopes\FundoScope;
use App\Services\Security\FundoContext;

trait BelongsToFundo
{
    public static function bootBelongsToFundo(): void
    {
        static::addGlobalScope(new FundoScope);

        static::creating(function ($model) {
            if (empty($model->fundo_id)) {
                $fundoId = FundoContext::get();
                if ($fundoId !== null) {
                    $model->fundo_id = $fundoId;
                }
            }
        });
    }
}
