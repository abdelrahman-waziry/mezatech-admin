<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    public function storeHardware(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'timestamp' => 'nullable|date',
            'summary' => 'nullable|array',
            'battery' => 'nullable|array',
            'display' => 'nullable|array',
            'components' => 'nullable|array',
        ]);

        $device = \App\Models\DiagnosticDevice::firstOrCreate(
            ['uuid' => $validated['device_id']]
        );

        $report = \App\Models\HardwareReport::create([
            'diagnostic_device_id' => $device->id,
            'timestamp' => $validated['timestamp'] ?? now(),
            'overall_status' => $validated['summary']['overallStatus'] ?? null,
            'battery_health' => $validated['summary']['batteryHealth'] ?? null,
            'cycle_count' => $validated['summary']['cycleCount'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'battery_data' => $validated['battery'] ?? null,
            'display_data' => $validated['display'] ?? null,
            'components_data' => $validated['components'] ?? null,
        ]);

        return response()->json(['message' => 'Hardware report saved successfully', 'report_id' => $report->id], 201);
    }

    public function storeCosmetic(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'timestamp' => 'nullable|date',
            'grade' => 'nullable|string',
            'label' => 'nullable|string',
            'color' => 'nullable|string',
            'description' => 'nullable|string',
            'overall_score' => 'nullable|numeric',
            'total_defects' => 'nullable|integer',
            'defect_summary' => 'nullable|array',
            'image_scores' => 'nullable|array',
            'images' => 'nullable|array',
        ]);

        $device = \App\Models\DiagnosticDevice::firstOrCreate(
            ['uuid' => $validated['device_id']]
        );

        $savedImages = [];
        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $view => $base64Data) {
                // Remove prefix if exists (e.g. data:image/jpeg;base64,)
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                    $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $extension = strtolower($type[1]); // jpg, png, etc.
                } else {
                    $extension = 'jpg';
                }

                $imageName = 'cosmetic/' . $device->uuid . '/' . time() . '_' . $view . '.' . $extension;
                \Illuminate\Support\Facades\Storage::disk('public')->put($imageName, base64_decode($base64Data));
                $savedImages[$view] = \Illuminate\Support\Facades\Storage::url($imageName);
            }
        }

        $report = \App\Models\CosmeticReport::create([
            'diagnostic_device_id' => $device->id,
            'timestamp' => $validated['timestamp'] ?? now(),
            'grade' => $validated['grade'] ?? null,
            'overall_score' => $validated['overall_score'] ?? null,
            'total_defects' => $validated['total_defects'] ?? null,
            'color' => $validated['color'] ?? null,
            'label' => $validated['label'] ?? null,
            'description' => $validated['description'] ?? null,
            'defect_summary' => $validated['defect_summary'] ?? null,
            'image_scores' => $validated['image_scores'] ?? null,
            'images' => $savedImages,
        ]);

        return response()->json(['message' => 'Cosmetic report saved successfully', 'report_id' => $report->id], 201);
    }
}
