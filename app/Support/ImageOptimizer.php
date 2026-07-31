<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageOptimizer
{
    private const MAX_DIMENSION = 1600;

    private const MAX_BYTES = 2 * 1024 * 1024;

    private const MAX_PIXELS = 40_000_000;

    public static function store(
        UploadedFile $file,
        string $directory,
        string $attribute = 'foto',
        int $maxDimension = self::MAX_DIMENSION,
        int $maxBytes = self::MAX_BYTES,
        string $disk = 'public'
    ): string {
        $sourcePath = $file->getRealPath();
        $imageInfo = $sourcePath ? @getimagesize($sourcePath) : false;

        if (! $imageInfo) {
            throw ValidationException::withMessages([$attribute => 'No se pudo leer la imagen.']);
        }

        [$width, $height, $type] = $imageInfo;
        $directory = trim($directory, '/');

        if ($width * $height > self::MAX_PIXELS) {
            throw ValidationException::withMessages([$attribute => 'La imagen no puede superar 40 megapíxeles.']);
        }

        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw ValidationException::withMessages([$attribute => 'Usa una imagen JPG, PNG o WebP.']);
        }

        if (! function_exists('imagewebp')) {
            throw ValidationException::withMessages([$attribute => 'El servidor no puede optimizar imágenes WebP.']);
        }

        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($sourcePath),
            default => false,
        };

        if (! $image) {
            throw ValidationException::withMessages([$attribute => 'No se pudo procesar la imagen.']);
        }

        $contents = null;

        try {
            if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $orientation = @exif_read_data($sourcePath)['Orientation'] ?? 1;
                $image = self::applyOrientation($image, (int) $orientation);
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $scale = min(1, $maxDimension / max($width, $height));
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));

            for ($attempt = 0; $attempt < 6; $attempt++) {
                $resized = imagecreatetruecolor($targetWidth, $targetHeight);
                if (! $resized) {
                    throw ValidationException::withMessages([$attribute => 'No se pudo redimensionar la imagen.']);
                }

                try {
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);
                    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                    imagefill($resized, 0, 0, $transparent);
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
                    $contents = self::encode($resized, $maxBytes);
                } finally {
                    imagedestroy($resized);
                }

                if ($contents !== null) {
                    break;
                }

                $targetWidth = max(1, (int) round($targetWidth * 0.82));
                $targetHeight = max(1, (int) round($targetHeight * 0.82));
            }
        } finally {
            imagedestroy($image);
        }

        if ($contents === null) {
            throw ValidationException::withMessages([$attribute => 'No se pudo optimizar la imagen al tamaño requerido.']);
        }

        return self::write($directory, $contents, $attribute, $disk);
    }

    private static function applyOrientation(\GdImage $image, int $orientation): \GdImage
    {
        if ($orientation === 2) {
            imageflip($image, IMG_FLIP_HORIZONTAL);

            return $image;
        }

        if ($orientation === 4) {
            imageflip($image, IMG_FLIP_VERTICAL);

            return $image;
        }

        $degrees = match ($orientation) {
            3 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($degrees === 0) {
            return $image;
        }

        $background = imagecolorallocatealpha($image, 0, 0, 0, 127);
        $rotated = imagerotate($image, $degrees, $background);
        if (! $rotated) {
            return $image;
        }

        imagesavealpha($rotated, true);
        if (in_array($orientation, [5, 7], true)) {
            imageflip($rotated, IMG_FLIP_HORIZONTAL);
        }
        imagedestroy($image);

        return $rotated;
    }

    private static function encode(\GdImage $image, int $maxBytes): ?string
    {
        foreach ([84, 78, 72, 66, 60] as $quality) {
            ob_start();
            $encoded = imagewebp($image, null, $quality);
            $contents = (string) ob_get_clean();

            if ($encoded && $contents !== '' && strlen($contents) <= $maxBytes) {
                return $contents;
            }
        }

        return null;
    }

    private static function write(string $directory, string $contents, string $attribute, string $disk): string
    {
        $path = $directory.'/'.Str::uuid().'.webp';
        if ($contents === '' || ! Storage::disk($disk)->put($path, $contents)) {
            throw ValidationException::withMessages([$attribute => 'No se pudo guardar la imagen.']);
        }

        return $path;
    }
}
