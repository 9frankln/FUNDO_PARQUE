<?php

namespace Tests\Unit;

use App\Support\ImageFrame;
use PHPUnit\Framework\TestCase;

class ImageFrameTest extends TestCase
{
    public function test_it_normalizes_defaults_aliases_rounding_and_limits(): void
    {
        $this->assertSame(ImageFrame::DEFAULT, ImageFrame::normalize(null));
        $this->assertSame(
            ['x' => 0.0, 'y' => 100.0, 'zoom' => 4.0],
            ImageFrame::normalize(['focus_x' => -12, 'focus_y' => 130, 'zoom' => 8])
        );
        $this->assertSame(
            ['x' => 12.3, 'y' => 45.7, 'zoom' => 1.24],
            ImageFrame::normalize('{"x":12.34,"y":45.67,"zoom":1.236}')
        );
    }

    public function test_it_builds_complete_livewire_validation_rules(): void
    {
        $rules = ImageFrame::rules('fotoEncuadre');

        $this->assertSame(
            ['fotoEncuadre', 'fotoEncuadre.x', 'fotoEncuadre.y', 'fotoEncuadre.zoom'],
            array_keys($rules)
        );
        $this->assertContains('between:0,100', $rules['fotoEncuadre.x']);
        $this->assertContains('between:0.3,4', $rules['fotoEncuadre.zoom']);
    }
}
