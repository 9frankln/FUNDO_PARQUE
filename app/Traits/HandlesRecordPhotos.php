<?php

namespace App\Traits;

use App\Models\RegistroFoto;
use App\Support\ImageFrame;
use App\Support\ImageOptimizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

trait HandlesRecordPhotos
{
    public array $fotos = [];

    public array $fotoEncuadres = [];

    public array $existingPhotos = [];

    public array $existingPhotoFrames = [];

    #[Locked]
    public array $changedExistingPhotoIds = [];

    public array $removedPhotoIds = [];

    public function updatedFotos(): void
    {
        $this->validate($this->recordPhotoRules(), $this->recordPhotoMessages());

        $frames = [];
        foreach (array_keys($this->fotos) as $index) {
            $frames[$index] = ImageFrame::normalize($this->fotoEncuadres[$index] ?? null);
        }

        $this->fotoEncuadres = $frames;
    }

    public function removeNewPhoto(int $index): void
    {
        if (! array_key_exists($index, $this->fotos)) {
            return;
        }

        unset($this->fotos[$index], $this->fotoEncuadres[$index]);

        $frames = [];
        foreach (array_keys($this->fotos) as $photoIndex) {
            $frames[] = ImageFrame::normalize($this->fotoEncuadres[$photoIndex] ?? null);
        }

        $this->fotos = array_values($this->fotos);
        $this->fotoEncuadres = $frames;
        $this->resetValidation(['fotos', 'fotoEncuadres']);
    }

    public function removeExistingPhoto(int $photoId): void
    {
        $exists = collect($this->existingPhotos)->contains(fn ($photo) => (int) $photo['id'] === $photoId);
        if (! $exists) {
            return;
        }

        $this->removedPhotoIds[] = $photoId;
        $this->removedPhotoIds = array_values(array_unique($this->removedPhotoIds));
        $this->existingPhotos = array_values(array_filter(
            $this->existingPhotos,
            fn ($photo) => (int) $photo['id'] !== $photoId
        ));
        unset($this->existingPhotoFrames[$photoId]);
        $this->changedExistingPhotoIds = array_values(array_diff($this->changedExistingPhotoIds, [$photoId]));
        $this->resetValidation(['fotos', 'existingPhotoFrames']);
    }

    public function updatedExistingPhotoFrames(mixed $value, ?string $key = null): void
    {
        $photoIds = $key === null
            ? array_keys($this->existingPhotoFrames)
            : [explode('.', $key)[0]];
        $allowedIds = collect($this->existingPhotos)->pluck('id')->map(fn ($id) => (int) $id);

        foreach ($photoIds as $photoId) {
            $photoId = (int) $photoId;
            if ($allowedIds->contains($photoId)) {
                $this->changedExistingPhotoIds[] = $photoId;
            }
        }

        $this->changedExistingPhotoIds = array_values(array_unique($this->changedExistingPhotoIds));
    }

    protected function loadRecordPhotos(Model $record): void
    {
        $photos = $record->fotos()
            ->orderBy('orden')
            ->get();

        $this->existingPhotos = $photos->map(function (RegistroFoto $photo): array {
            $frame = ImageFrame::normalize($photo->encuadre);

            return [
                'id' => $photo->id,
                'path' => $photo->ruta,
                'url' => route('record-photo.show', $photo),
                'frame' => $frame,
            ];
        })->all();
        $this->existingPhotoFrames = collect($this->existingPhotos)
            ->mapWithKeys(fn (array $photo) => [$photo['id'] => $photo['frame']])
            ->all();
        $this->changedExistingPhotoIds = [];
    }

    protected function recordPhotoRules(): array
    {
        $available = max(0, 3 - count($this->existingPhotos));

        return [
            'fotos' => ['array', 'list', 'max:'.$available],
            'fotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
            'fotoEncuadres' => ['array', 'list', 'max:3'],
            ...ImageFrame::rules('fotoEncuadres.*'),
            'existingPhotoFrames' => ['array', 'max:3'],
            ...ImageFrame::rules('existingPhotoFrames.*'),
        ];
    }

    protected function recordPhotoMessages(): array
    {
        return [
            'fotos.max' => 'Puedes guardar máximo 3 imágenes por registro.',
            'fotos.*.image' => 'Selecciona imágenes válidas.',
            'fotos.*.mimes' => 'Usa imágenes JPG, PNG o WebP.',
            'fotos.*.max' => 'Cada imagen optimizada debe pesar máximo 2 MB.',
            'fotos.*.dimensions' => 'Una imagen supera las dimensiones permitidas.',
        ];
    }

    protected function storeRecordPhotos(string $directory): array
    {
        $paths = [];

        try {
            foreach ($this->fotos as $index => $photo) {
                $paths[$index] = ImageOptimizer::store($photo, $directory, 'fotos.'.$index, 1280, 1024 * 1024, 'local');
            }
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }

        return $paths;
    }

    protected function attachRecordPhotos(Model $record, array $paths): void
    {
        $record->newQuery()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
        $photos = $record->fotos()->orderBy('orden')->orderBy('id')->lockForUpdate()->get();
        if ($photos->count() + count($paths) > 3) {
            throw ValidationException::withMessages([
                'fotos' => 'Puedes guardar máximo 3 imágenes por registro.',
            ]);
        }

        $changedIds = array_map('intval', $this->changedExistingPhotoIds);
        foreach ($photos->values() as $order => $photo) {
            $photoId = (string) $photo->getKey();
            if (in_array((int) $photoId, $changedIds, true) && array_key_exists($photoId, $this->existingPhotoFrames)) {
                $photo->encuadre = ImageFrame::normalize($this->existingPhotoFrames[$photoId]);
            }
            $photo->orden = $order;
            if ($photo->isDirty()) {
                $photo->save();
            }
        }

        $nextOrder = $photos->count();

        foreach ($paths as $index => $path) {
            $record->fotos()->create([
                'fundo_id' => $record->fundo_id,
                'ruta' => $path,
                'orden' => $nextOrder + $index,
                'encuadre' => ImageFrame::normalize($this->fotoEncuadres[$index] ?? null),
            ]);
        }
    }

    protected function removeMarkedRecordPhotos(Model $record): array
    {
        if ($this->removedPhotoIds === []) {
            return [];
        }

        $photos = $record->fotos()->whereKey($this->removedPhotoIds)->get();
        $paths = $photos->pluck('ruta')->all();
        $record->fotos()->whereKey($photos->pluck('id'))->delete();

        return $paths;
    }

    protected function deleteUnreferencedRecordPhotos(array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            if (! RegistroFoto::withoutGlobalScopes()->where('ruta', $path)->exists()) {
                Storage::disk('local')->delete($path);
            }
        }
    }

    protected function deleteStoredRecordPhotos(array $paths): void
    {
        Storage::disk('local')->delete($paths);
    }
}
