<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MediaFile;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
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

    /**
     * Uploads land in the public /storage folder on the same origin as the admin
     * panel, so only inert file types are accepted. SVG, HTML and PHP are refused:
     * they could run script in the admin's browser or on the server.
     */
    private const ALLOWED_EXTENSIONS = 'jpg,jpeg,png,webp,gif,avif,pdf,doc,docx';

    private const MAX_KB = 20480; // 20 MB, matches the server's upload_max_filesize

    public function upload(Request $request): JsonResponse
    {
        $rule = ['file', 'max:'.self::MAX_KB, 'mimes:'.self::ALLOWED_EXTENSIONS, 'extensions:'.self::ALLOWED_EXTENSIONS];
        $messages = [
            '*.mimes' => 'শুধু ছবি (JPG, PNG, WebP, GIF, AVIF), PDF বা Word ফাইল আপলোড করা যাবে।',
            '*.extensions' => 'শুধু ছবি (JPG, PNG, WebP, GIF, AVIF), PDF বা Word ফাইল আপলোড করা যাবে।',
            '*.max' => 'ফাইল ২০ MB-এর বেশি হতে পারবে না।',
        ];

        // Check for multiple files
        $multipleFiles = $request->file('files');
        if (! $multipleFiles && is_array($request->file('file'))) {
            $multipleFiles = $request->file('file');
        }

        if ($multipleFiles && is_array($multipleFiles)) {
            $key = $request->file('files') ? 'files' : 'file';
            $request->validate([
                $key => 'required|array|max:50',
                "{$key}.*" => $rule,
            ], [
                "{$key}.*.mimes" => $messages['*.mimes'],
                "{$key}.*.extensions" => $messages['*.extensions'],
                "{$key}.*.max" => $messages['*.max'],
            ]);

            $created = [];
            foreach ($multipleFiles as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $created[] = $this->storeFile($request, $file, pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            }

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'upload_media_bulk',
                'model_type' => MediaFile::class,
                'model_id' => null,
                'description' => 'Uploaded '.count($created).' media files',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => count($created).' media files uploaded successfully',
                'data' => $created,
            ], 201);
        }

        $request->validate([
            'file' => array_merge(['required'], $rule),
            'alt_text' => 'nullable|string|max:255',
        ], [
            'file.mimes' => $messages['*.mimes'],
            'file.extensions' => $messages['*.extensions'],
            'file.max' => $messages['*.max'],
        ]);

        $file = $request->file('file');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $media = $this->storeFile($request, $file, $request->input('alt_text', $originalName));

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

    /** Save a validated upload under uploads/ with a safe name and an extension taken from its real content. */
    private function storeFile(Request $request, UploadedFile $file, string $altText): MediaFile
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $slug = Str::slug($originalName) ?: 'file';
        $fileName = $slug.'-'.time().'-'.Str::random(4).'.'.$extension;

        $path = $file->storeAs('uploads', $fileName, 'public');

        return MediaFile::create([
            'name' => $originalName,
            'file_name' => $fileName,
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'alt_text' => $altText,
            'uploaded_by' => $request->user()?->id,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $media = MediaFile::find($id);
        if (! $media) {
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
        if (! $media) {
            return response()->json(['success' => false, 'message' => 'Media file not found'], 404);
        }

        if (! str_starts_with($media->mime_type, 'image/')) {
            return response()->json(['success' => false, 'message' => 'File is not an image'], 422);
        }

        $fullPath = Storage::disk($media->disk)->path($media->path);
        if (! file_exists($fullPath)) {
            return response()->json(['success' => false, 'message' => 'Image file not found on disk'], 404);
        }

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->decodePath($fullPath);

            $width = (int) $request->input('width', 1200);
            if ($width > 0 && $width < $image->width()) {
                $image->scale(width: $width);
            }

            $webpName = pathinfo($media->file_name, PATHINFO_FILENAME).'-opt.webp';
            $webpRelativePath = 'uploads/optimized/'.$webpName;
            $webpFullPath = Storage::disk($media->disk)->path($webpRelativePath);

            if (! file_exists(dirname($webpFullPath))) {
                mkdir(dirname($webpFullPath), 0755, true);
            }

            $image->encode(new WebpEncoder(quality: 85))->save($webpFullPath);

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
            Log::error('Media optimization error: '.$e->getMessage(), [
                'exception' => $e,
                'media_id' => $id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Image optimization failed. Please check server logs.',
            ], 500);
        }
    }
}
