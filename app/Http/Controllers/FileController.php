<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Upload a file.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::random(40) . '_' . time() . '.' . $extension;

            // Determine entity type from request or default to 'product'
            $entityType = $request->input('entityType', 'product');
            $entityId = $request->input('entityId', 'general');

            $path = "images/{$entityType}/{$entityId}/{$fileName}";

            Storage::disk('public')->put($path, $file->get());

            return response()->json([
                'message' => 'File uploaded successfully',
                'data' => [
                    'filename' => $path,
                    'originalName' => $originalName,
                    'url' => Storage::disk('public')->url($path),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a file.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'filename' => 'required|string',
            'width' => 'nullable|integer|min:1|max:2000',
        ]);

        try {
            $filename = $validated['filename'];

            if (!Storage::disk('public')->exists($filename)) {
                return response()->json([
                    'message' => 'File not found',
                ], 404);
            }

            $file = Storage::disk('public')->get($filename);
            $mimeType = Storage::disk('public')->mimeType($filename) ?: 'application/octet-stream';

            // If width is specified and it's an image, resize it
            if (isset($validated['width']) && str_starts_with($mimeType, 'image/')) {
                $resized = $this->resizeImage($file, $mimeType, (int) $validated['width']);
                return response($resized['contents'], 200)
                    ->header('Content-Type', $resized['mime']);
            }

            return response($file, 200)->header('Content-Type', $mimeType);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function resizeImage(string $contents, string $mimeType, int $width): array
    {
        $source = @imagecreatefromstring($contents);
        if ($source === false) {
            return ['contents' => $contents, 'mime' => $mimeType];
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $height = max(1, (int) round($sourceHeight * ($width / $sourceWidth)));
        $target = imagecreatetruecolor($width, $height);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        ob_start();
        match ($mimeType) {
            'image/png' => imagepng($target),
            'image/gif' => imagegif($target),
            default => imagejpeg($target, null, 85),
        };
        $resized = ob_get_clean() ?: $contents;

        imagedestroy($source);
        imagedestroy($target);

        return ['contents' => $resized, 'mime' => $mimeType];
    }

    /**
     * Delete a file.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'filename' => 'required|string',
            'entityType' => 'nullable|string',
        ]);

        try {
            $filename = $validated['filename'];

            if (!Storage::disk('public')->exists($filename)) {
                return response()->json([
                    'message' => 'File not found',
                ], 404);
            }

            Storage::disk('public')->delete($filename);

            return response()->json([
                'message' => 'File deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete file',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

