<?php

namespace App\Http\Controllers;

use App\Models\Fundo;
use App\Models\LandingBlock;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PublicLandingController extends Controller
{
    public function __invoke()
    {
        $allBlocks = LandingBlock::query()
            ->with('media')
            ->orderBy('order')
            ->get()
            ->keyBy('section');
        $blocks = $allBlocks->filter(fn (LandingBlock $block) => $block->is_active);

        $heroBlock = $allBlocks->get('hero');
        $heroVisible = ! $heroBlock || $heroBlock->is_active;
        $hero = $heroVisible ? $heroBlock : null;
        $heroSettings = array_replace(LandingBlock::defaultSettings('hero'), $heroBlock?->settings ?? []);
        $publicFundoColumns = ['id', 'nombre', 'activo'];
        $publicFundo = Fundo::query()
            ->select($publicFundoColumns)
            ->where('activo', true)
            ->when($heroSettings['public_fundo_id'] ?? null, fn ($query, $id) => $query->whereKey((int) $id))
            ->first()
            ?? Fundo::query()->select($publicFundoColumns)->where('activo', true)->oldest('id')->first();

        $contentBlocks = collect(array_keys(LandingBlock::contentSections()))
            ->mapWithKeys(fn (string $section) => [$section => $blocks->get($section)])
            ->filter();
        $galleryBlock = $allBlocks->get('galeria');
        $galleryVisible = ! $galleryBlock || $galleryBlock->is_active;
        $maxGalleryImages = min(48, max(8, (int) ($galleryBlock?->settings['max_images'] ?? 36)));
        $galleryItems = $galleryVisible
            ? $this->galleryItems($contentBlocks, $galleryBlock)->take($maxGalleryImages)->values()
            : collect();

        $heroItems = $heroVisible ? $this->mediaItems(collect([$hero])->filter(), 'hero') : collect();
        if ($heroVisible && $heroItems->isEmpty()) {
            $heroItems = $galleryItems
                ->groupBy('category')
                ->map->first()
                ->values()
                ->merge($galleryItems)
                ->unique('id')
                ->take(6)
                ->values();
        }
        if (($heroSettings['hero_mode'] ?? 'carousel') === 'single') {
            $heroItems = $heroItems->take(1)->values();
        }

        return view('welcome', [
            'blocks' => $blocks,
            'hero' => $hero,
            'heroVisible' => $heroVisible,
            'heroSettings' => $heroSettings,
            'heroItems' => $heroItems,
            'contentBlocks' => $contentBlocks,
            'galleryBlock' => $galleryBlock,
            'galleryVisible' => $galleryVisible,
            'galleryItems' => $galleryItems,
            'publicFundo' => $publicFundo,
            'publicFundoName' => $publicFundo?->nombre ?? 'Nuestro fundo',
        ]);
    }

    private function galleryItems(Collection $contentBlocks, ?LandingBlock $galleryBlock): Collection
    {
        $blocks = $galleryBlock
            ? $contentBlocks->merge(['galeria' => $galleryBlock])
            : $contentBlocks;

        return $this->mediaItems($blocks);
    }

    private function mediaItems(Collection $blocks, ?string $forcedCategory = null): Collection
    {
        return $blocks->flatMap(function (LandingBlock $block) use ($forcedCategory): Collection {
            $category = $forcedCategory ?? $block->section;
            $label = LandingBlock::sectionDefinitions()[$category]['label'] ?? ucfirst($category);

            return $block->media->map(fn (Media $media) => [
                'id' => (string) $media->id,
                'full' => $media->hasGeneratedConversion('optimized') ? $media->getUrl('optimized') : $media->getUrl(),
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                'category' => $category,
                'category_label' => $label,
                'caption' => $media->getCustomProperty('caption') ?: $label,
                ...LandingBlock::mediaFrame($media),
            ]);
        })->values();
    }
}
