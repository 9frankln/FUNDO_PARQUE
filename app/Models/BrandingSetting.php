<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandingSetting extends Model
{
    protected $fillable = ['name', 'tagline', 'color', 'color_mode', 'custom_color', 'logo_path', 'logo_encuadre'];

    protected $casts = [
        'logo_encuadre' => 'array',
    ];

    public static function singleton(): static
    {
        return static::query()->firstOrCreate(['id' => 1], config('branding.defaults'));
    }

    public static function saveSingleton(array $attributes): static
    {
        return static::query()->updateOrCreate(['id' => 1], $attributes);
    }
}
