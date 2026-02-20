<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Models\Media;
use App\Services\ImageService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CreateMedia extends CreateRecord
{
    protected static string $resource = MediaResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        $uploadPath = $data['upload'] ?? null;

        if ($uploadPath) {
            $fullPath = Storage::disk('local')->path($uploadPath);
            $file = new UploadedFile($fullPath, basename($fullPath), mime_content_type($fullPath));

            $imageService = app(ImageService::class);
            $result = $imageService->storeAsAvif($file);

            // Override slug if user provided one
            if (!empty($data['slug']) && $data['slug'] !== $result['slug']) {
                // Rename the file
                $newSlug = $data['slug'];
                $ext = pathinfo($result['path'], PATHINFO_EXTENSION);
                $dir = dirname($result['path']);
                $newPath = $dir . '/' . $newSlug . '.' . $ext;

                Storage::disk('public')->move($result['path'], $newPath);
                $result['path'] = $newPath;
                $result['slug'] = $newSlug;
            }

            $record = Media::create([
                'slug' => $data['slug'] ?: $result['slug'],
                'alt' => $data['alt'] ?? null,
                'url' => $data['url'] ?? null,
                'original_name' => $result['original_name'],
                'path' => $result['path'],
                'mime_type' => $result['mime_type'],
                'width' => $result['width'],
                'height' => $result['height'],
                'size' => $result['size'],
            ]);

            // Cleanup temp upload
            Storage::disk('local')->delete($uploadPath);

            return $record;
        }

        return Media::create($data);
    }
}
