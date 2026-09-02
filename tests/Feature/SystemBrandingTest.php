<?php

namespace Tests\Feature;

use App\Models\BrandingSetting;
use App\Support\SystemBranding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class SystemBrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Cache::flush();
        app(SystemBranding::class)->invalidate();
    }

    public function test_it_exposes_the_seeded_singleton_defaults_and_complete_safe_palette(): void
    {
        $branding = app(SystemBranding::class);

        $this->assertSame([
            'name' => 'AgroFundo',
            'tagline' => 'Gestión rural',
            'color' => 'emerald',
            'color_mode' => 'preset',
            'custom_color' => null,
            'logo_path' => null,
            'logo_encuadre' => ['x' => 50.0, 'y' => 50.0, 'zoom' => 1.0],
        ], $branding->toArray());
        $this->assertSame(BrandingSetting::singleton()->id, 1);
        $this->assertSame([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950], array_keys($branding->palette()));
        $this->assertSame('16 185 129', $branding->paletteRgb()[500]);
    }

    public function test_save_updates_only_the_singleton_and_refreshes_memoized_values(): void
    {
        $branding = app(SystemBranding::class);
        $this->assertSame('AgroFundo', $branding->name());

        $branding->save([
            'name' => 'Fundo Norte',
            'tagline' => 'Cultivamos futuro',
            'color' => 'violet',
            'logo_path' => 'branding/logo.png',
            'logo_encuadre' => ['x' => 28, 'y' => 71, 'zoom' => 1.4],
        ]);

        $this->assertSame('Fundo Norte', $branding->name);
        $this->assertSame('violet', $branding->color());
        $this->assertSame('branding/logo.png', BrandingSetting::singleton()->logo_path);
        $this->assertSame(['x' => 28.0, 'y' => 71.0, 'zoom' => 1.4], $branding->logoFrame());
        $this->assertDatabaseCount('branding_settings', 1);
    }

    public function test_it_rejects_arbitrary_colors_and_unsafe_logo_paths(): void
    {
        $branding = app(SystemBranding::class);

        try {
            $branding->save(['color' => '#10b981']);
            $this->fail('An arbitrary color was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertSame('emerald', $branding->color());
        }

        $this->expectException(InvalidArgumentException::class);
        $branding->save(['logo_path' => '../secret.png']);
    }

    public function test_it_generates_a_complete_safe_palette_from_a_custom_hex_color(): void
    {
        $branding = app(SystemBranding::class);
        $branding->save([
            'color_mode' => 'custom',
            'custom_color' => '#d7a48f',
        ]);

        $this->assertSame('custom', $branding->colorMode());
        $this->assertSame('#D7A48F', $branding->customColor());
        $this->assertSame('#D7A48F', $branding->palette()[500]);
        $this->assertSame([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950], array_keys($branding->palette()));
        $this->assertMatchesRegularExpression('/^\d+ \d+ \d+$/', $branding->paletteRgb()[700]);

        $this->expectException(InvalidArgumentException::class);
        $branding->save(['color_mode' => 'custom', 'custom_color' => 'red;']);
    }

    public function test_it_falls_back_when_persisted_values_are_corrupt_or_the_table_is_missing(): void
    {
        DB::table('branding_settings')->where('id', 1)->update([
            'name' => '',
            'tagline' => '',
            'color' => '#bad',
            'logo_path' => '../bad.svg',
        ]);

        $branding = app(SystemBranding::class);
        $branding->invalidate();
        $this->assertSame(config('branding.defaults'), $branding->toArray());

        Schema::drop('branding_settings');
        $branding->invalidate();
        $this->assertSame(config('branding.defaults'), $branding->toArray());
    }

    public function test_it_provides_a_public_url_and_embedded_pdf_logo_when_the_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('branding/logo.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));

        $branding = app(SystemBranding::class);
        $branding->save(['logo_path' => 'branding/logo.png']);

        $this->assertStringEndsWith('/storage/branding/logo.png', $branding->logoUrl());
        $this->assertStringStartsWith('data:image/png;base64,', $branding->logoDataUri());
    }
}
