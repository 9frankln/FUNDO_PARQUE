<?php

namespace App\Models;

use App\Support\ImageFrame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LandingBlock extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const MEDIA_ZOOM_MIN = ImageFrame::MIN_ZOOM;

    public const MEDIA_ZOOM_MAX = ImageFrame::MAX_ZOOM;

    public const SECTION_DEFINITIONS = [
        'hero' => ['label' => 'Inicio', 'order' => 0],
        'ganaderia' => ['label' => 'Ganadería', 'order' => 1],
        'equinos' => ['label' => 'Equinos', 'order' => 2],
        'construccion' => ['label' => 'Infraestructura', 'order' => 3],
        'procesos' => ['label' => 'Procesos', 'order' => 4],
        'galeria' => ['label' => 'Galería', 'order' => 5],
    ];

    protected $fillable = [
        'section',
        'title',
        'content',
        'settings',
        'order',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function media(): MorphMany
    {
        return $this->morphMany($this->getMediaModel(), 'model')
            ->orderBy('order_column')
            ->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery')
            ->useDisk('public')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->useFallbackUrl('/images/placeholder.jpg')
            ->useFallbackPath(public_path('/images/placeholder.jpg'));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(480)
            ->height(360)
            ->sharpen(10)
            ->format('webp')
            ->quality(80)
            ->nonQueued();

        $this->addMediaConversion('optimized')
            ->width(1600)
            ->height(1200)
            ->format('webp')
            ->quality(82)
            ->nonQueued();
    }

    public static function sectionDefinitions(): array
    {
        return self::SECTION_DEFINITIONS;
    }

    public static function contentSections(): array
    {
        return array_intersect_key(self::SECTION_DEFINITIONS, array_flip(['ganaderia', 'equinos', 'construccion', 'procesos']));
    }

    public static function mediaFrame(Media $media): array
    {
        $frame = ImageFrame::normalize([
            'focus_x' => $media->getCustomProperty('focus_x'),
            'focus_y' => $media->getCustomProperty('focus_y'),
            'zoom' => $media->getCustomProperty('zoom'),
        ]);

        return [
            'focus_x' => $frame['x'],
            'focus_y' => $frame['y'],
            'zoom' => $frame['zoom'],
        ];
    }

    public static function defaultContent(string $section): array
    {
        return match ($section) {
            'hero' => [
                'title' => 'Gestión agropecuaria con visión de futuro',
                'content' => 'Conoce una operación rural donde experiencia, cuidado animal, infraestructura y datos trabajan juntos.',
            ],
            'ganaderia' => [
                'title' => 'Ganadería con trazabilidad y propósito',
                'content' => 'Seguimiento responsable del ganado, bienestar animal y decisiones respaldadas por información clara.',
            ],
            'equinos' => [
                'title' => 'Cuidado especializado para equinos',
                'content' => 'Atención, sanidad y manejo continuo para preservar bienestar y desempeño.',
            ],
            'construccion' => [
                'title' => 'Infraestructura que acompaña el crecimiento',
                'content' => 'Mejoramos accesos, espacios de trabajo e instalaciones para operar con seguridad y continuidad.',
            ],
            'procesos' => [
                'title' => 'Procesos conectados de principio a fin',
                'content' => 'Organización diaria, control productivo y mejora continua para convertir trabajo de campo en resultados.',
            ],
            'galeria' => [
                'title' => 'El fundo, visto desde adentro',
                'content' => 'Personas, animales, infraestructura y jornadas que construyen nuestra historia.',
            ],
            default => ['title' => ucfirst($section), 'content' => ''],
        };
    }

    public static function defaultSettings(string $section): array
    {
        return match ($section) {
            'hero' => [
                'hero_mode' => 'carousel',
                'public_fundo_id' => null,
                'show_fundo_name' => true,
                'show_owner' => true,
                'owner_name' => 'Familia Choquenaira',
                'show_location' => false,
                'custom_location' => '',
                'show_address' => false,
                'custom_address' => '',
                'show_whatsapp' => false,
                'whatsapp_number' => '',
                'whatsapp_message' => 'Hola, deseo recibir información sobre el fundo.',
                'eyebrow' => 'Producción rural conectada',
                'cta_label' => 'Conocer el fundo',
            ],
            'ganaderia' => [
                'eyebrow' => 'Manejo responsable',
                'feature_1' => 'Trazabilidad individual',
                'feature_2' => 'Bienestar y sanidad',
                'feature_3' => 'Control productivo',
            ],
            'equinos' => [
                'eyebrow' => 'Cuidado especializado',
                'feature_1' => 'Historial sanitario',
                'feature_2' => 'Seguimiento continuo',
                'feature_3' => 'Bienestar animal',
            ],
            'construccion' => [
                'eyebrow' => 'Infraestructura rural',
                'feature_1' => 'Accesos seguros',
                'feature_2' => 'Espacios funcionales',
                'feature_3' => 'Mejora permanente',
            ],
            'procesos' => [
                'eyebrow' => 'Trabajo coordinado',
                'feature_1' => 'Operación ordenada',
                'feature_2' => 'Información centralizada',
                'feature_3' => 'Decisiones oportunas',
            ],
            'galeria' => [
                'eyebrow' => 'Registro visual',
                'max_images' => 36,
            ],
            default => [],
        };
    }
}
