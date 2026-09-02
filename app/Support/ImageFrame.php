<?php

namespace App\Support;

final class ImageFrame
{
    public const DEFAULT = [
        'x' => 50.0,
        'y' => 50.0,
        'zoom' => 1.0,
    ];

    public const MIN_ZOOM = 0.3;

    public const MAX_ZOOM = 4.0;

    public static function normalize(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : null;
        }

        $frame = is_array($value)
            ? $value
            : (is_object($value) ? get_object_vars($value) : []);
        $x = self::numeric($frame['x'] ?? $frame['focus_x'] ?? null, self::DEFAULT['x']);
        $y = self::numeric($frame['y'] ?? $frame['focus_y'] ?? null, self::DEFAULT['y']);
        $zoom = self::numeric($frame['zoom'] ?? null, self::DEFAULT['zoom']);

        return [
            'x' => round(min(100, max(0, $x)), 1),
            'y' => round(min(100, max(0, $y)), 1),
            'zoom' => round(min(self::MAX_ZOOM, max(self::MIN_ZOOM, $zoom)), 2),
        ];
    }

    public static function rules(string $prefix): array
    {
        return [
            $prefix => ['required', 'array:x,y,zoom'],
            $prefix.'.x' => ['required', 'numeric', 'between:0,100'],
            $prefix.'.y' => ['required', 'numeric', 'between:0,100'],
            $prefix.'.zoom' => ['required', 'numeric', 'between:'.self::MIN_ZOOM.','.self::MAX_ZOOM],
        ];
    }

    private static function numeric(mixed $value, float $default): float
    {
        return is_numeric($value) ? (float) $value : $default;
    }
}
