<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function storeAsAvif(UploadedFile $file, string $directory = 'media', ?string $folder = null): array
    {
        $image = $this->loadImage($file);
        if (!$image) {
            throw new \RuntimeException('Impossible de charger l\'image.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $slug = $this->uniqueSlug($slug);

        $filename = $slug . '.avif';
        $subDir = $folder ?: date('Y/m');
        $relativePath = $directory . '/' . $subDir . '/' . $filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagesavealpha($image, true);
        imageavif($image, $absolutePath, 70);
        imagedestroy($image);

        $size = filesize($absolutePath);

        return [
            'slug' => $slug,
            'original_name' => $file->getClientOriginalName(),
            'path' => $relativePath,
            'mime_type' => 'image/avif',
            'width' => $width,
            'height' => $height,
            'size' => $size,
        ];
    }

    private function loadImage(UploadedFile $file): ?\GdImage
    {
        $path = $file->getPathname();
        $mime = $file->getMimeType();

        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => $this->loadPng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/avif' => imagecreatefromavif($path),
            default => null,
        };
    }

    private function loadPng(string $path): \GdImage
    {
        $image = imagecreatefrompng($path);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    private function uniqueSlug(string $slug): string
    {
        $original = $slug;
        $counter = 1;

        while (\App\Models\Media::where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
