<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function storeAsAvif(UploadedFile $file, string $directory = 'media'): array
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
        $relativePath = $directory . '/' . date('Y/m') . '/' . $filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $dir = dirname($absolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (function_exists('imageavif')) {
            imageavif($image, $absolutePath, 70);
        } else {
            // Fallback to WebP if AVIF not available
            $relativePath = str_replace('.avif', '.webp', $relativePath);
            $absolutePath = str_replace('.avif', '.webp', $absolutePath);
            imagewebp($image, $absolutePath, 80);
        }

        imagedestroy($image);

        $size = filesize($absolutePath);

        return [
            'slug' => $slug,
            'original_name' => $file->getClientOriginalName(),
            'path' => $relativePath,
            'mime_type' => function_exists('imageavif') ? 'image/avif' : 'image/webp',
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
            'image/avif' => function_exists('imagecreatefromavif') ? imagecreatefromavif($path) : null,
            default => null,
        };
    }

    private function loadPng(string $path): \GdImage
    {
        $image = imagecreatefrompng($path);
        $width = imagesx($image);
        $height = imagesy($image);

        // Flatten alpha onto white background for AVIF
        $flat = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefill($flat, 0, 0, $white);
        imagealphablending($flat, true);
        imagecopy($flat, $image, 0, 0, 0, 0, $width, $height);
        imagedestroy($image);

        return $flat;
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
