<?php

namespace App\Services\Media;

use Filament\Forms\Components\BaseFileUpload;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;
use League\Flysystem\UnableToCheckFileExistence;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class WebpImageUploadService
{
    public function generateWebpFilename(): string
    {
        return Str::ulid().'.webp';
    }

    public function generateSlugWebpFilename(string $slug): string
    {
        $safeSlug = Str::slug($slug) ?: (string) Str::ulid();
        $timestamp = now()->format('YmdHis');

        return "{$safeSlug}-{$timestamp}.webp";
    }

    public function storeForFilament(BaseFileUpload $component, TemporaryUploadedFile $file, int $quality = 80): ?string
    {
        $filename = $component->getUploadedFileNameForStorage($file);
        $directory = trim((string) $component->getDirectory(), '/');
        $storedPath = $directory === '' ? $filename : "{$directory}/{$filename}";
        $visibility = $this->normalizeVisibility($component->getVisibility());

        $isStored = $this->storeAsWebp(
            file: $file,
            disk: $component->getDisk(),
            path: $storedPath,
            visibility: $visibility,
            quality: $quality,
        );

        if (! $isStored) {
            return null;
        }

        return $storedPath;
    }

    public function storeAsWebp(
        TemporaryUploadedFile $file,
        Filesystem $disk,
        string $path,
        ?string $visibility = 'public',
        int $quality = 80,
    ): bool {
        try {
            if (! $file->exists()) {
                return false;
            }
        } catch (UnableToCheckFileExistence) {
            return false;
        }

        if (! function_exists('imagewebp')) {
            return false;
        }

        $realPath = $file->getRealPath();

        if ($realPath === false) {
            return false;
        }

        $imageData = file_get_contents($realPath);

        if ($imageData === false) {
            return false;
        }

        $image = imagecreatefromstring($imageData);

        if ($image === false) {
            return false;
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $isConverted = imagewebp($image, null, $this->normalizeQuality($quality));
        $webpContent = ob_get_clean();

        imagedestroy($image);

        if ((! $isConverted) || ($webpContent === false)) {
            return false;
        }

        return (bool) $disk->put($path, $webpContent, [
            'visibility' => $this->normalizeVisibility($visibility),
        ]);
    }

    protected function normalizeQuality(int $quality): int
    {
        return max(0, min(100, $quality));
    }

    protected function normalizeVisibility(?string $visibility): string
    {
        return in_array($visibility, ['public', 'private'], true)
            ? $visibility
            : 'public';
    }
}
