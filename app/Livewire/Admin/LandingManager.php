<?php

namespace App\Livewire\Admin;

use App\Models\Fundo;
use App\Models\LandingBlock;
use App\Services\AuditLogger;
use App\Support\ImageFrame;
use App\Traits\AuthorizesPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class LandingManager extends Component
{
    use AuthorizesPermissions, WithFileUploads;

    private const MAX_MEDIA_PER_SECTION = 40;

    public array $blocks = [];

    public array $uploads = [];

    public array $uploadFrames = [];

    public array $sections = [];

    public bool $showFrameEditor = false;

    public ?string $frameSection = null;

    public ?int $frameMediaId = null;

    public string $framePreviewUrl = '';

    public float $frameX = 50;

    public float $frameY = 50;

    public float $frameZoom = LandingBlock::MEDIA_ZOOM_MIN;

    public function mount(): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        $this->sections = collect(LandingBlock::sectionDefinitions())
            ->mapWithKeys(fn (array $definition, string $section) => [$section => $definition['label']])
            ->all();
        $this->loadBlocks();
    }

    public function loadBlocks(): void
    {
        $existing = LandingBlock::query()->with('media')->get()->keyBy('section');

        foreach ($this->sections as $section => $label) {
            $block = $existing->get($section) ?? $this->createDefaultBlock($section);
            $content = LandingBlock::defaultContent($section);
            $this->blocks[$section] = [
                'id' => $block->id,
                'section' => $section,
                'title' => $block->title ?? '',
                'content' => $block->content ?? '',
                'is_active' => (bool) $block->is_active,
                'settings' => array_replace(LandingBlock::defaultSettings($section), $block->settings ?? []),
                'suggested_title' => $content['title'],
                'suggested_content' => $content['content'],
                'media' => $block->media->map(fn (Media $media) => [
                    'id' => $media->id,
                    'name' => $media->name,
                    'preview_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'caption' => (string) $media->getCustomProperty('caption', ''),
                    'size' => $media->size,
                    ...LandingBlock::mediaFrame($media),
                ])->values()->all(),
            ];
            $this->uploads[$section] = [];
            $this->uploadFrames[$section] = [];
        }
    }

    public function updatedUploads(mixed $value, ?string $key = null): void
    {
        $section = explode('.', (string) $key)[0] ?? '';
        if (! array_key_exists($section, $this->sections)) {
            return;
        }

        $this->uploadFrames[$section] = collect($this->uploads[$section] ?? [])
            ->map(fn (): array => ImageFrame::DEFAULT)
            ->values()
            ->all();
    }

    public function saveBlock(string $section): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        $this->ensureKnownSection($section);

        $rules = [
            "blocks.{$section}.title" => ['nullable', 'string', 'max:140'],
            "blocks.{$section}.content" => ['nullable', 'string', 'max:2500'],
            "blocks.{$section}.is_active" => ['boolean'],
            "blocks.{$section}.settings.eyebrow" => ['nullable', 'string', 'max:60'],
            "blocks.{$section}.media.*.caption" => ['nullable', 'string', 'max:120'],
            "uploads.{$section}" => ['array', 'max:8'],
            "uploads.{$section}.*" => ['image', 'mimes:jpg,jpeg,png,webp', 'max:15360', 'dimensions:min_width=480,min_height=320,max_width=8000,max_height=8000'],
            "uploadFrames.{$section}" => ['array', 'max:8'],
            ...ImageFrame::rules("uploadFrames.{$section}.*"),
        ];

        if ($section === 'hero') {
            $rules += [
                'blocks.hero.settings.hero_mode' => ['required', Rule::in(['carousel', 'single'])],
                'blocks.hero.settings.public_fundo_id' => ['nullable', 'integer', Rule::exists('fundos', 'id')->where('activo', true)],
                'blocks.hero.settings.show_fundo_name' => ['boolean'],
                'blocks.hero.settings.show_owner' => ['boolean'],
                'blocks.hero.settings.owner_name' => ['nullable', 'string', 'max:120'],
                'blocks.hero.settings.show_location' => ['boolean'],
                'blocks.hero.settings.custom_location' => [Rule::requiredIf(fn () => (bool) ($this->blocks['hero']['settings']['show_location'] ?? false)), 'nullable', 'string', 'max:120'],
                'blocks.hero.settings.show_address' => ['boolean'],
                'blocks.hero.settings.custom_address' => [Rule::requiredIf(fn () => (bool) ($this->blocks['hero']['settings']['show_address'] ?? false)), 'nullable', 'string', 'max:200'],
                'blocks.hero.settings.show_whatsapp' => ['boolean'],
                'blocks.hero.settings.whatsapp_number' => [
                    Rule::requiredIf(fn () => (bool) ($this->blocks['hero']['settings']['show_whatsapp'] ?? false)),
                    'nullable',
                    'string',
                    'max:25',
                    'regex:/^[+()\d\s-]+$/',
                    function (string $attribute, mixed $value, $fail): void {
                        $digits = preg_replace('/\D+/', '', (string) $value);
                        if (strlen($digits) < 8 || strlen($digits) > 15) {
                            $fail('Ingresa un número válido con código de país.');
                        }
                    },
                ],
                'blocks.hero.settings.whatsapp_message' => ['nullable', 'string', 'max:180'],
                'blocks.hero.settings.cta_label' => ['nullable', 'string', 'max:40'],
            ];
        } elseif ($section === 'galeria') {
            $rules['blocks.galeria.settings.max_images'] = ['required', 'integer', 'min:8', 'max:48'];
        } else {
            foreach (range(1, 3) as $number) {
                $rules["blocks.{$section}.settings.feature_{$number}"] = ['nullable', 'string', 'max:70'];
            }
        }

        $this->validate($rules, [
            "uploads.{$section}.max" => 'Sube máximo 8 imágenes por vez.',
            "uploads.{$section}.*.image" => 'Todos los archivos deben ser imágenes válidas.',
            "uploads.{$section}.*.mimes" => 'Usa imágenes JPG, PNG o WebP.',
            "uploads.{$section}.*.max" => 'Cada imagen puede pesar máximo 15 MB.',
            "uploads.{$section}.*.dimensions" => 'Cada imagen debe medir entre 480×320 y 8000×8000 píxeles.',
        ]);

        $block = $this->blockFor($section);
        $pendingUploads = collect($this->uploads[$section] ?? [])->filter()->values();
        $pendingFrames = collect($this->uploadFrames[$section] ?? [])->values();
        if ($block->media()->count() + $pendingUploads->count() > self::MAX_MEDIA_PER_SECTION) {
            throw ValidationException::withMessages([
                "uploads.{$section}" => 'Máximo 40 imágenes por sección. Elimina algunas antes de subir más.',
            ]);
        }

        $data = $this->blocks[$section];
        if ($section === 'hero') {
            $data['settings']['whatsapp_number'] = preg_replace('/\D+/', '', (string) ($data['settings']['whatsapp_number'] ?? ''));
        }
        DB::transaction(function () use ($block, $section, $data): void {
            $block->update([
                'title' => filled($data['title'] ?? null) ? trim($data['title']) : null,
                'content' => filled($data['content'] ?? null) ? trim($data['content']) : null,
                'is_active' => (bool) ($data['is_active'] ?? false),
                'settings' => array_replace(LandingBlock::defaultSettings($section), $data['settings'] ?? []),
            ]);

            $mediaById = $block->media()->get()->keyBy('id');
            foreach ($data['media'] ?? [] as $mediaData) {
                $media = $mediaById->get((int) ($mediaData['id'] ?? 0));
                if ($media) {
                    $media->setCustomProperty('caption', trim((string) ($mediaData['caption'] ?? '')))->save();
                }
            }
        });

        foreach ($pendingUploads as $index => $file) {
            $frame = ImageFrame::normalize($pendingFrames->get($index));
            $block->addMedia($file)
                ->withCustomProperties([
                    'focus_x' => $frame['x'],
                    'focus_y' => $frame['y'],
                    'zoom' => $frame['zoom'],
                ])
                ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                ->toMediaCollection('gallery');
        }

        app(AuditLogger::class)->record(
            'landing.seccion_actualizada',
            'ajustes',
            'Actualizó sección pública '.$this->sections[$section].'.',
            metadata: ['seccion' => $section, 'visible' => (bool) ($data['is_active'] ?? false), 'imagenes_nuevas' => $pendingUploads->count()],
        );

        $this->loadBlocks();
        $this->dispatchToast('Sección guardada', 'Contenido e imágenes publicados correctamente.');
    }

    public function toggleActive(string $section): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        $this->ensureKnownSection($section);
        $newValue = ! (bool) ($this->blocks[$section]['is_active'] ?? false);
        $this->blockFor($section)->update(['is_active' => $newValue]);
        $this->blocks[$section]['is_active'] = $newValue;

        app(AuditLogger::class)->record(
            'landing.visibilidad_actualizada',
            'ajustes',
            ($newValue ? 'Publicó' : 'Ocultó').' sección '.$this->sections[$section].'.',
            metadata: ['seccion' => $section, 'visible' => $newValue],
        );
    }

    public function resetSectionDefaults(string $section): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        $this->ensureKnownSection($section);
        $content = LandingBlock::defaultContent($section);
        $this->blocks[$section]['title'] = $content['title'];
        $this->blocks[$section]['content'] = $content['content'];
        $this->blocks[$section]['settings'] = array_replace(
            $this->blocks[$section]['settings'] ?? [],
            LandingBlock::defaultSettings($section),
        );
    }

    public function setAsCover(string $section, int $mediaId): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        $block = $this->blockFor($section);
        $this->mediaForBlock($block, $mediaId);
        $ids = $block->media()->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $id === $mediaId)
            ->prepend($mediaId)
            ->values()
            ->all();

        DB::transaction(function () use ($ids): void {
            Media::setNewOrder($ids);
        });

        $this->applyMediaOrderToState($section, $ids);
        app(AuditLogger::class)->record('landing.portada_actualizada', 'ajustes', 'Cambió la portada de una sección pública.', metadata: ['seccion' => $section, 'media_id' => $mediaId]);
        $this->dispatchToast('Portada actualizada', 'Imagen principal de sección cambiada.');
    }

    public function moveMedia(string $section, int $mediaId, string $direction): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        abort_unless(in_array($direction, ['up', 'down'], true), 422);
        $block = $this->blockFor($section);
        $this->mediaForBlock($block, $mediaId);
        $ids = $block->media()->pluck('id')->map(fn ($id) => (int) $id)->values();
        $index = $ids->search($mediaId, true);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index !== false && $target >= 0 && $target < $ids->count()) {
            [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
            Media::setNewOrder($ids->all());
            $this->applyMediaOrderToState($section, $ids->all());
        }
    }

    public function deleteMedia(string $section, int $mediaId): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        $block = $this->blockFor($section);
        $media = $this->mediaForBlock($block, $mediaId);
        $media->delete();
        $this->blocks[$section]['media'] = collect($this->blocks[$section]['media'] ?? [])
            ->reject(fn (array $item) => (int) $item['id'] === $mediaId)
            ->values()
            ->all();
        app(AuditLogger::class)->record('landing.imagen_eliminada', 'ajustes', 'Eliminó imagen de la web pública.', metadata: ['seccion' => $section, 'media_id' => $mediaId]);
        $this->dispatchToast('Imagen eliminada', 'Archivo y conversiones retirados.');
    }

    public function openFrameEditor(string $section, int $mediaId): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        $block = $this->blockFor($section);
        $media = $this->mediaForBlock($block, $mediaId);
        $frame = LandingBlock::mediaFrame($media);

        $this->frameSection = $section;
        $this->frameMediaId = $mediaId;
        $this->framePreviewUrl = $media->hasGeneratedConversion('optimized') ? $media->getUrl('optimized') : $media->getUrl();
        $this->frameX = $frame['focus_x'];
        $this->frameY = $frame['focus_y'];
        $this->frameZoom = $frame['zoom'];
        $this->showFrameEditor = true;
    }

    public function saveFrame(): void
    {
        $this->authorizePermission('gestion_web', 'actualizar');
        abort_unless($this->frameSection && $this->frameMediaId, 422);

        $data = $this->validate([
            'frameX' => ['required', 'numeric', 'between:0,100'],
            'frameY' => ['required', 'numeric', 'between:0,100'],
            'frameZoom' => ['required', 'numeric', 'between:'.LandingBlock::MEDIA_ZOOM_MIN.','.LandingBlock::MEDIA_ZOOM_MAX],
        ]);
        $section = $this->frameSection;
        $media = $this->mediaForBlock($this->blockFor($section), $this->frameMediaId);
        $frame = [
            'focus_x' => round((float) $data['frameX'], 1),
            'focus_y' => round((float) $data['frameY'], 1),
            'zoom' => round((float) $data['frameZoom'], 2),
        ];

        $media->setCustomProperty('focus_x', $frame['focus_x'])
            ->setCustomProperty('focus_y', $frame['focus_y'])
            ->setCustomProperty('zoom', $frame['zoom'])
            ->save();

        $this->blocks[$section]['media'] = collect($this->blocks[$section]['media'] ?? [])
            ->map(fn (array $item) => (int) $item['id'] === $media->id ? array_replace($item, $frame) : $item)
            ->values()
            ->all();

        $this->closeFrameEditor();
        app(AuditLogger::class)->record('landing.encuadre_actualizado', 'ajustes', 'Ajustó el encuadre de una imagen pública.', metadata: ['seccion' => $section, 'media_id' => $media->id, ...$frame]);
        $this->dispatchToast('Encuadre guardado', 'La posición y el zoom se aplicaron en la web pública.');
    }

    public function closeFrameEditor(): void
    {
        $this->showFrameEditor = false;
        $this->frameSection = null;
        $this->frameMediaId = null;
        $this->framePreviewUrl = '';
    }

    public function render()
    {
        $fundos = Fundo::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        $publicFundoId = (int) ($this->blocks['hero']['settings']['public_fundo_id'] ?? 0);
        $publicFundo = $fundos->firstWhere('id', $publicFundoId) ?? $fundos->first();

        return view('livewire.admin.landing-manager', compact('fundos', 'publicFundo'))
            ->layout('layouts.app', ['header' => 'Gestión Web Pública']);
    }

    private function createDefaultBlock(string $section): LandingBlock
    {
        $content = LandingBlock::defaultContent($section);
        $definition = LandingBlock::sectionDefinitions()[$section];

        return LandingBlock::create([
            'section' => $section,
            'title' => $content['title'],
            'content' => $content['content'],
            'settings' => LandingBlock::defaultSettings($section),
            'order' => $definition['order'],
            'is_active' => true,
        ]);
    }

    private function blockFor(string $section): LandingBlock
    {
        $this->ensureKnownSection($section);

        return LandingBlock::query()->where('section', $section)->firstOrFail();
    }

    private function mediaForBlock(LandingBlock $block, int $mediaId): Media
    {
        return $block->media()->whereKey($mediaId)->firstOrFail();
    }

    private function applyMediaOrderToState(string $section, array $ids): void
    {
        $media = collect($this->blocks[$section]['media'] ?? [])
            ->keyBy(fn (array $item) => (int) $item['id']);

        $this->blocks[$section]['media'] = collect($ids)
            ->map(fn ($id) => $media->get((int) $id))
            ->filter()
            ->values()
            ->all();
    }

    private function ensureKnownSection(string $section): void
    {
        abort_unless(array_key_exists($section, $this->sections), 404);
    }

    private function dispatchToast(string $title, string $text): void
    {
        $this->dispatch('swal:toast', title: $title, text: $text, icon: 'success');
    }
}
