<?php

namespace App\Support;

use InvalidArgumentException;

final class BrandPalette
{
    private const MIXES = [
        50 => ['white', 0.94],
        100 => ['white', 0.86],
        200 => ['white', 0.70],
        300 => ['white', 0.50],
        400 => ['white', 0.25],
        500 => ['base', 0.00],
        600 => ['black', 0.18],
        700 => ['black', 0.36],
        800 => ['black', 0.52],
        900 => ['black', 0.66],
        950 => ['black', 0.80],
    ];

    public static function normalize(?string $hex): ?string
    {
        $hex = is_string($hex) ? strtoupper(trim($hex)) : '';

        return preg_match('/^#[0-9A-F]{6}$/', $hex) === 1 ? $hex : null;
    }

    /** @return array<int, string> */
    public static function fromHex(string $hex): array
    {
        $hex = self::normalize($hex);
        if ($hex === null) {
            throw new InvalidArgumentException('El color personalizado debe usar el formato #RRGGBB.');
        }

        $base = self::hexToRgb($hex);
        $palette = [];

        foreach (self::MIXES as $shade => [$target, $weight]) {
            $rgb = match ($target) {
                'white' => self::mix($base, [255, 255, 255], $weight),
                'black' => self::mix($base, [0, 0, 0], $weight),
                default => $base,
            };

            if (in_array($shade, [600, 700], true)) {
                $rgb = self::ensureWhiteContrast($rgb);
            }

            $palette[$shade] = self::rgbToHex($rgb);
        }

        return $palette;
    }

    /** @return array{int, int, int} */
    private static function hexToRgb(string $hex): array
    {
        return [
            hexdec(substr($hex, 1, 2)),
            hexdec(substr($hex, 3, 2)),
            hexdec(substr($hex, 5, 2)),
        ];
    }

    /** @param array{int, int, int} $source @param array{int, int, int} $target @return array{int, int, int} */
    private static function mix(array $source, array $target, float $weight): array
    {
        return array_map(
            fn (int $value, int $targetValue): int => (int) round($value + (($targetValue - $value) * $weight)),
            $source,
            $target,
        );
    }

    /** @param array{int, int, int} $rgb @return array{int, int, int} */
    private static function ensureWhiteContrast(array $rgb): array
    {
        while (self::contrastWithWhite($rgb) < 4.5) {
            $rgb = self::mix($rgb, [0, 0, 0], 0.08);
        }

        return $rgb;
    }

    /** @param array{int, int, int} $rgb */
    private static function contrastWithWhite(array $rgb): float
    {
        $channels = array_map(function (int $channel): float {
            $value = $channel / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        }, $rgb);
        $luminance = (0.2126 * $channels[0]) + (0.7152 * $channels[1]) + (0.0722 * $channels[2]);

        return 1.05 / ($luminance + 0.05);
    }

    /** @param array{int, int, int} $rgb */
    private static function rgbToHex(array $rgb): string
    {
        return sprintf('#%02X%02X%02X', ...$rgb);
    }
}
