<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MediaFile;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MediaFile::latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('file_name', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            if ($type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($type === 'document') {
                $query->where('mime_type', 'not like', 'image/%');
            }
        }

        $perPage = (int) $request->input('per_page', 24);
        $files = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $files->items(),
            'meta' => [
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'per_page' => $files->perPage(),
                'total' => $files->total(),
            ],
        ]);
    }

    public function upload(Request $request): JsonResponse
    {
        // Check for multiple files
        $multipleFiles = $request->file('files');
        if (!$multipleFiles && is_array($request->file('file'))) {
            $multipleFiles = $request->file('file');
        }

        if ($multipleFiles && is_array($multipleFiles)) {
            $created = [];
            foreach ($multipleFiles as $file) {
                if (!$file || !$file->isValid()) continue;

                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $fileName = Str::slug($originalName) . '-' . time() . '-' . Str::random(4) . '.' . $extension;

                $path = $file->storeAs('uploads', $fileName, 'public');

                $media = MediaFile::create([
                    'name' => $originalName,
                    'file_name' => $fileName,
                    'disk' => 'public',
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'alt_text' => $originalName,
                    'uploaded_by' => $request->user()?->id,
                ]);

                $created[] = $media;
            }

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'upload_media_bulk',
                'model_type' => MediaFile::class,
                'model_id' => null,
                'description' => 'Uploaded ' . count($created) . ' media files',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => count($created) . ' media files uploaded successfully',
                'data' => $created,
            ], 201);
        }

        $request->validate([
            'file' => 'required|file|max:51200', // 50MB max
            'alt_text' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = Str::slug($originalName) . '-' . time() . '-' . Str::random(4) . '.' . $extension;

        // Store file in public disk under 'uploads/'
        $path = $file->storeAs('uploads', $fileName, 'public');

        $media = MediaFile::create([
            'name' => $originalName,
            'file_name' => $fileName,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $request->input('alt_text', $originalName),
            'uploaded_by' => $request->user()?->id,
        ]);

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'upload_media',
            'model_type' => MediaFile::class,
            'model_id' => $media->id,
            'description' => "Uploaded media file '{$media->file_name}'",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Media file uploaded successfully',
            'data' => $media,
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $media = MediaFile::find($id);
        if (!$media) {
            return response()->json(['success' => false, 'message' => 'File not found'], 404);
        }

        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        $media->delete();

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'delete_media',
            'model_type' => MediaFile::class,
            'model_id' => $id,
            'description' => "Deleted media file #{$id}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Media file deleted',
        ]);
    }

    public function optimize(Request $request, int $id): JsonResponse
    {
        $media = MediaFile::find($id);
        if (!$media) {
            return response()->json(['success' => false, 'message' => 'Media file not found'], 404);
        }

        if (!str_starts_with($media->mime_type, 'image/')) {
            return response()->json(['success' => false, 'message' => 'File is not an image'], 422);
        }

        $fullPath = Storage::disk($media->disk)->path($media->path);
        if (!file_exists($fullPath)) {
            return response()->json(['success' => false, 'message' => 'Image file not found on disk'], 404);
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($fullPath);

            $width = (int) $request->input('width', 1200);
            if ($width > 0 && $width < $image->width()) {
                $image->scale(width: $width);
            }

            $webpName = pathinfo($media->file_name, PATHINFO_FILENAME) . '-opt.webp';
            $webpRelativePath = 'uploads/optimized/' . $webpName;
            $webpFullPath = Storage::disk($media->disk)->path($webpRelativePath);

            if (!file_exists(dirname($webpFullPath))) {
                mkdir(dirname($webpFullPath), 0755, true);
            }

            $image->toWebp(85)->save($webpFullPath);

            return response()->json([
                'success' => true,
                'message' => 'Image optimized successfully to WebP',
                'data' => [
                    'original_url' => $media->url,
                    'optimized_url' => Storage::disk($media->disk)->url($webpRelativePath),
                    'optimized_path' => $webpRelativePath,
                    'format' => 'webp',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Optimization failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
