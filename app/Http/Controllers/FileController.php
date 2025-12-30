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
            'request' => 'nullable|file',
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

            Storage::disk('public')->put($path, file_get_contents($file));

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
            $mimeType = Storage::disk('public')->mimeType($filename);

            // If width is specified and it's an image, resize it
            if (isset($validated['width']) && str_starts_with($mimeType, 'image/')) {
                // For now, return the original file
                // In production, you might want to use intervention/image or similar
                return response($file, 200)->header('Content-Type', $mimeType);
            }

            return response($file, 200)->header('Content-Type', $mimeType);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve file',
                'error' => $e->getMessage(),
            ], 500);
        }
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

