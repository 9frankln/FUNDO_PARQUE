<?php

namespace App\Support;

use App\Models\BrandingSetting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

class SystemBranding
{
    private ?array $settings = null;

    public function __construct(private readonly CacheRepository $cache) {}

    public function name(): string
    {
        return $this->settings()['name'];
    }

    public function tagline(): string
    {
        return $this->settings()['tagline'];
    }

    public function color(): string
    {
        return $this->settings()['color'];
    }

    public function colorMode(): string
    {
        return $this->settings()['color_mode'];
    }

    public function customColor(): ?string
    {
        return $this->settings()['custom_color'];
    }

    public function logoPath(): ?string
    {
        return $this->settings()['logo_path'];
    }

    public function logoFrame(): array
    {
        return $this->settings()['logo_encuadre'];
    }

    public function palette(?string $color = null): array
    {
        $palettes = config('branding.palettes', []);
        if ($color === null && $this->colorMode() === 'custom' && $this->customColor() !== null) {
            return BrandPalette::fromHex($this->customColor());
        }

        $selected = $color ?? $this->color();

        return is_array($palettes[$selected] ?? null)
            ? $palettes[$selected]
            : ($palettes['emerald'] ?? []);
    }

    public function paletteRgb(?string $color = null): array
    {
        return collect($this->palette($color))->map(function (string $hex): string {
            $value = ltrim($hex, '#');

            return hexdec(substr($value, 0, 2)).' '.hexdec(substr($value, 2, 2)).' '.hexdec(substr($value, 4, 2));
        })->all();
    }

    public function logoUrl(): ?string
    {
        $path = $this->logoPath();
        if ($path === null) {
            return null;
        }

        try {
            $disk = Storage::disk('public');

            return $disk->exists($path) ? $disk->url($path) : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function logoDataUri(): ?string
    {
        $path = $this->logoPath();
        if ($path === null) {
            return null;
        }

        try {
            $disk = Storage::disk('public');
            if (! $disk->exists($path)) {
                return null;
            }

            $contents = $disk->get($path);
            $mime = $disk->mimeType($path);
            if (! is_string($contents) || ! in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'], true)) {
                return null;
            }

            return 'data:'.$mime.';base64,'.base64_encode($contents);
        } catch (Throwable) {
            return null;
        }
    }

    public function save(array $attributes): BrandingSetting
    {
        $allowed = array_intersect_key($attributes, array_flip(['name', 'tagline', 'color', 'color_mode', 'custom_color', 'logo_path', 'logo_encuadre']));
        $values = array_merge($this->settings(), $allowed);
        $values['name'] = $this->requiredText($values['name'], 'name');
        $values['tagline'] = $this->requiredText($values['tagline'], 'tagline');

        if (! array_key_exists($values['color'], config('branding.palettes', []))) {
            throw new InvalidArgumentException('The branding color must be one of the configured palettes.');
        }

        if (! in_array($values['color_mode'], ['preset', 'custom'], true)) {
            throw new InvalidArgumentException('The branding color mode must be preset or custom.');
        }

        $values['custom_color'] = BrandPalette::normalize($values['custom_color']);
        if ($values['color_mode'] === 'custom' && $values['custom_color'] === null) {
            throw new InvalidArgumentException('The custom branding color must use #RRGGBB format.');
        }

        $values['logo_path'] = $this->validLogoPath($values['logo_path']);
        $values['logo_encuadre'] = $values['logo_path'] === null
            ? null
            : ImageFrame::normalize($values['logo_encuadre'] ?? null);
        $model = BrandingSetting::saveSingleton($values);
        $this->settings = $this->sanitize($model->getAttributes());
        $this->putCache($this->settings);

        return $model;
    }

    public function invalidate(): void
    {
        $this->settings = null;

        try {
            $this->cache->forget(config('branding.cache_key'));
        } catch (Throwable) {
            // Branding must not make the application depend on cache availability.
        }
    }

    public function toArray(): array
    {
        return $this->settings();
    }

    public function __get(string $property): mixed
    {
        return match ($property) {
            'name' => $this->name(),
            'tagline' => $this->tagline(),
            'color' => $this->color(),
            'color_mode', 'colorMode' => $this->colorMode(),
            'custom_color', 'customColor' => $this->customColor(),
            'logo_path', 'logoPath' => $this->logoPath(),
            'logo_encuadre', 'logoFrame' => $this->logoFrame(),
            'logo_url', 'logoUrl' => $this->logoUrl(),
            'logo_data_uri', 'logoDataUri' => $this->logoDataUri(),
            'palette' => $this->palette(),
            'palette_rgb', 'paletteRgb' => $this->paletteRgb(),
            default => null,
        };
    }

    private function settings(): array
    {
        if ($this->settings !== null) {
            return $this->settings;
        }

        try {
            $cached = $this->cache->get(config('branding.cache_key'));
            if (is_array($cached)) {
                return $this->settings = $this->sanitize($cached);
            }
        } catch (Throwable) {
            // Continue with the database and then the configured defaults.
        }

        try {
            $this->settings = $this->sanitize(BrandingSetting::query()->find(1)?->getAttributes() ?? []);
            $this->putCache($this->settings);
        } catch (Throwable) {
            $this->settings = $this->defaults();
        }

        return $this->settings;
    }

    private function sanitize(array $values): array
    {
        $defaults = $this->defaults();
        $palettes = config('branding.palettes', []);
        $name = is_string($values['name'] ?? null) ? trim($values['name']) : '';
        $tagline = is_string($values['tagline'] ?? null) ? trim($values['tagline']) : '';
        $color = is_string($values['color'] ?? null) ? $values['color'] : '';
        $colorMode = in_array($values['color_mode'] ?? null, ['preset', 'custom'], true) ? $values['color_mode'] : 'preset';
        $customColor = BrandPalette::normalize($values['custom_color'] ?? null);
        if ($colorMode === 'custom' && $customColor === null) {
            $colorMode = 'preset';
        }

        return [
            'name' => $name !== '' && mb_strlen($name) <= 255 ? $name : $defaults['name'],
            'tagline' => $tagline !== '' && mb_strlen($tagline) <= 255 ? $tagline : $defaults['tagline'],
            'color' => array_key_exists($color, $palettes) ? $color : $defaults['color'],
            'color_mode' => $colorMode,
            'custom_color' => $customColor,
            'logo_path' => $this->sanitizeLogoPath($values['logo_path'] ?? null),
            'logo_encuadre' => ImageFrame::normalize($values['logo_encuadre'] ?? $defaults['logo_encuadre']),
        ];
    }

    private function defaults(): array
    {
        $defaults = array_merge([
            'name' => 'AgroFundo',
            'tagline' => 'Gestión rural',
            'color' => 'emerald',
            'color_mode' => 'preset',
            'custom_color' => null,
            'logo_path' => null,
            'logo_encuadre' => ImageFrame::DEFAULT,
        ], config('branding.defaults', []));

        $defaults['logo_encuadre'] = ImageFrame::normalize($defaults['logo_encuadre']);

        return $defaults;
    }

    private function requiredText(mixed $value, string $field): string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '' || mb_strlen($value) > 255) {
            throw new InvalidArgumentException("The branding {$field} must be a non-empty string of at most 255 characters.");
        }

        return $value;
    }

    private function validLogoPath(mixed $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = $this->sanitizeLogoPath($path);
        if ($path === null) {
            throw new InvalidArgumentException('The branding logo_path must be a relative path on the public disk.');
        }

        return $path;
    }

    private function sanitizeLogoPath(mixed $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim(str_replace('\\', '/', $path), '/');

        return $path !== '' && mb_strlen($path) <= 255 && ! str_contains($path, '..') && ! str_contains($path, '://')
            ? $path
            : null;
    }

    private function putCache(array $settings): void
    {
        try {
            $this->cache->forever(config('branding.cache_key'), $settings);
        } catch (Throwable) {
            // In-memory settings remain available when the cache store is unavailable.
        }
    }
}
