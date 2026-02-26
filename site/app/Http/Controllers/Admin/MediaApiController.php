<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Setting;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MediaApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Media::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                  ->orWhere('original_name', 'like', "%{$search}%");
            });
        }

        if ($folder = $request->get('folder')) {
            $query->where('folder', $folder);
        }

        $media = $query->orderByDesc('created_at')->get();

        return response()->json([
            'media' => $media->map(fn (Media $m) => [
                'id' => $m->id,
                'slug' => $m->slug,
                'alt' => $m->alt,
                'url' => $m->getPublicUrl(),
                'original_name' => $m->original_name,
                'folder' => $m->folder,
                'mime_type' => $m->mime_type,
                'width' => $m->width,
                'height' => $m->height,
                'size' => $m->size,
                'created_at' => $m->created_at->format('d/m/Y H:i'),
            ]),
            'folders' => Media::whereNotNull('folder')
                ->distinct()
                ->pluck('folder')
                ->sort()
                ->values(),
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'image|max:10240',
            'folder' => 'nullable|string|max:100',
        ]);

        $imageService = app(ImageService::class);
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $result = $imageService->storeAsAvif($file, 'media', $request->input('folder'));

            $media = Media::create([
                'slug' => $result['slug'],
                'original_name' => $result['original_name'],
                'path' => $result['path'],
                'mime_type' => $result['mime_type'],
                'width' => $result['width'],
                'height' => $result['height'],
                'size' => $result['size'],
                'folder' => $request->input('folder') ?: null,
            ]);

            $uploaded[] = [
                'id' => $media->id,
                'slug' => $media->slug,
                'alt' => $media->alt,
                'url' => $media->getPublicUrl(),
                'original_name' => $media->original_name,
                'folder' => $media->folder,
                'mime_type' => $media->mime_type,
                'width' => $media->width,
                'height' => $media->height,
                'size' => $media->size,
                'created_at' => $media->created_at->format('d/m/Y H:i'),
            ];
        }

        return response()->json(['uploaded' => $uploaded]);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $request->validate([
            'slug' => 'sometimes|string|unique:media,slug,' . $media->id,
            'alt' => 'nullable|string|max:255',
            'folder' => 'nullable|string|max:100',
        ]);

        $newSlug = $request->input('slug', $media->slug);
        $newFolder = $request->has('folder') ? $request->input('folder') : $media->folder;
        $slugChanged = $newSlug !== $media->slug;
        $folderChanged = $newFolder !== $media->folder;

        // Move/rename file on disk when slug or folder changes
        if (($slugChanged || $folderChanged) && $media->path) {
            $disk = Storage::disk('public');
            $oldPath = $media->path;
            $extension = pathinfo($oldPath, PATHINFO_EXTENSION);

            $newDir = $newFolder ? 'media/' . $newFolder : dirname($oldPath);
            $newPath = $newDir . '/' . $newSlug . '.' . $extension;

            if ($disk->exists($oldPath) && $newPath !== $oldPath) {
                $targetDir = dirname($disk->path($newPath));
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }

                $disk->move($oldPath, $newPath);
                $media->path = $newPath;
            }
        }

        $oldSlug = $media->slug;
        $fields = $request->only(['slug', 'alt', 'folder']);
        if (isset($media->path) && ($slugChanged || $folderChanged)) {
            $fields['path'] = $media->path;
        }
        $media->update($fields);

        // Propagate slug rename across all references
        if ($slugChanged) {
            $this->propagateSlugRename($oldSlug, $newSlug);
        }

        return response()->json(['success' => true, 'url' => $media->getPublicUrl()]);
    }

    /**
     * Propagate a media slug rename across all dynamic references.
     */
    private function propagateSlugRename(string $oldSlug, string $newSlug): void
    {
        // Settings JSON values (menu logo_slug, image_slug in submenus, etc.)
        $settings = Setting::where('type', 'json')
            ->where('value', 'like', '%' . $oldSlug . '%')
            ->get();

        foreach ($settings as $setting) {
            $updated = str_replace(
                ['"' . $oldSlug . '"', "\"$oldSlug\""],
                ['"' . $newSlug . '"', "\"$newSlug\""],
                $setting->value
            );
            if ($updated !== $setting->value) {
                $setting->update(['value' => $updated]);
                \Illuminate\Support\Facades\Cache::forget('setting.' . $setting->key);
                \Illuminate\Support\Facades\Cache::forget('settings.group.' . $setting->group);
            }
        }

        // HTML content fields (slug="old-slug" in <x-image> components)
        $contentTables = [
            'articles' => 'content',
            'pages' => 'content',
            'sens' => 'content',
        ];

        foreach ($contentTables as $table => $column) {
            DB::table($table)
                ->where($column, 'like', '%' . $oldSlug . '%')
                ->update([
                    $column => DB::raw("REPLACE($column, '\"$oldSlug\"', '\"$newSlug\"')"),
                ]);
        }
    }

    public function destroy(Media $media): JsonResponse
    {
        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }

    public function renameFolder(Request $request): JsonResponse
    {
        $request->validate([
            'old_name' => 'required|string|max:100',
            'new_name' => 'required|string|max:100',
        ]);

        $newName = trim(strtolower(preg_replace('/[^a-z0-9-]/', '-', preg_replace('/-+/', '-', $request->input('new_name')))), '-');

        if (!$newName) {
            return response()->json(['error' => 'Nom de dossier invalide'], 422);
        }

        $count = Media::where('folder', $request->input('old_name'))
            ->update(['folder' => $newName]);

        return response()->json(['success' => true, 'count' => $count, 'new_name' => $newName]);
    }
}
