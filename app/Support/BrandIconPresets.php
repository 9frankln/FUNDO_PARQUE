<?php

namespace App\Support;

class BrandIconPresets
{
    public static function all(): array
    {
        return [
            'destacados' => [
                'label' => 'Íconos Imponentes de Fundo',
                'icons' => [
                    [
                        'key' => 'toro_imponente',
                        'name' => 'Toro Embistiendo Imponente',
                        'svg' => '<svg viewBox="0 0 160 160" fill="none" stroke="currentColor" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M 12 56 C 20 54 28 58 32 64 C 28 66 22 64 16 60 Z M 16 68 C 22 72 26 78 30 84 C 36 90 44 90 48 84 C 44 80 42 74 40 70"/><path d="M 32 64 C 40 48 58 38 88 44 C 114 48 132 58 140 56 C 146 54 148 42 138 28 C 136 22 132 18 134 14 C 138 10 144 14 142 22 C 140 28 142 34 146 38 C 152 44 154 54 148 64 C 142 74 136 78 138 90 C 140 102 144 116 142 128"/><path d="M 40 70 C 44 78 46 88 44 98 C 42 108 38 116 38 126 C 42 126 46 124 50 120 C 56 112 58 102 60 92 C 64 98 68 106 66 116 C 64 124 60 128 64 128 C 72 128 78 118 82 108 C 88 100 94 98 104 100 C 114 102 122 106 128 116 C 130 120 134 128 138 128 C 144 128 146 116 144 104 C 140 92 138 82 140 70"/><path d="M 58 92 C 54 98 52 106 56 114 C 58 118 62 122 62 126"/></svg>',
                    ],
                    [
                        'key' => 'caballo_2patas',
                        'name' => 'Caballo Erguido (2 Patas)',
                        'svg' => '<svg viewBox="0 0 100 100" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M58 14c-4-2-10 0-14 4-4 4-8 4-12 2-4-2-8 0-10 4s0 8 2 11c2 3 4 7 3 11-1 3-3 6-6 8s-6 4-8 7c-2 3-2 7 0 10 2 3 5 4 8 3 4-1 8-4 11-8 2-3 5-5 8-5 4 0 7 3 9 7 2 4 5 7 9 8 4 1 8-1 10-4s2-7 0-10c-2-3-4-7-4-11 0-4 3-7 6-9 4-2 7-6 7-11 0-5-4-9-9-9-3 0-6 2-8 5-2 2-5 3-8 2Z"/><path d="M64 48c4 8 10 14 16 18M42 62c-4 10-10 18-16 24M56 68c2 8 6 16 10 22" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>',
                    ],
                    [
                        'key' => 'gallo_fina_estampa',
                        'name' => 'Gallo Fina Estampa',
                        'svg' => '<svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M30 32c-6-12 0-22 10-22 6 0 10 6 10 6s6-6 12 0c6 6 3 12 0 16l18 10c6 3 9 12 6 18-6 12-22 22-34 22s-18-12-22-24l-12-9 12-3Z" fill="currentColor" fill-opacity="0.15"/><path d="M42 72v18M54 72v18M36 90h12M48 90h12"/><circle cx="48" cy="28" r="3" fill="currentColor"/></svg>',
                    ],
                    [
                        'key' => 'planta_brote',
                        'name' => 'Plántula Agrícola',
                        'svg' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M16 52h32"/><path d="M32 52V26"/><path d="M32 26C18 26 12 12 12 12c18 0 20 10 20 14Z" fill="currentColor" opacity="0.85"/><path d="M32 30C46 30 52 16 52 16c-18 0-20 10-20 14Z" fill="currentColor" opacity="0.85"/></svg>',
                    ],
                    [
                        'key' => 'vehiculo_4x4',
                        'name' => 'Camioneta 4x4 Fundo',
                        'svg' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M8 34l6-14h20l8 14h14v14H8V34Z" fill="currentColor" fill-opacity="0.15"/><circle cx="20" cy="48" r="6" stroke-width="3.5"/><circle cx="44" cy="48" r="6" stroke-width="3.5"/><path d="M22 22h12v12H22Z"/></svg>',
                    ],
                    [
                        'key' => 'cuy_andino',
                        'name' => 'Cuy / Cobayo Andino',
                        'svg' => '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><path d="M12 40c0-12 10-22 24-22 10 0 18 6 20 14 2 8-4 16-14 16H20c-5 0-8-3-8-8Z" fill="currentColor" fill-opacity="0.15"/><circle cx="44" cy="30" r="2.5" fill="currentColor"/></svg>',
                    ],
                ],
            ],
        ];
    }

    public static function getSvg(string $key): ?string
    {
        foreach (self::all() as $category) {
            foreach ($category['icons'] as $icon) {
                if ($icon['key'] === $key) {
                    return $icon['svg'];
                }
            }
        }

        return null;
    }
}
