<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Uppercase implements CastsInboundAttributes
{
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return is_string($value) ? Str::upper($value) : $value;
    }
}
