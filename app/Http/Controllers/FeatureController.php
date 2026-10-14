<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeatureController extends Controller
{
    public function index(): JsonResponse
    {
        $features = Feature::with('values')->get()->map->toArray()->values()->all();

        return response()->json(['features' => $features]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'productId' => ['nullable', 'integer'],
            'values' => ['nullable', 'array'],
            'values.*.value' => ['required_with:values', 'string', 'max:255'],
            'values.*.image' => ['nullable', 'string'],
        ]);

        $feature = DB::transaction(function () use ($data): Feature {
            $feature = Feature::create([
                'name' => $data['name'],
                'product_id' => $data['productId'] ?? null,
                'values_summary' => collect($data['values'] ?? [])->pluck('value')->implode(', '),
            ]);
            $feature->values()->createMany($data['values'] ?? []);
            return $feature->load('values');
        });

        return response()->json($feature, 201);
    }

    public function show(string $id): JsonResponse
    {
        $feature = Feature::with('values')->find($id);

        return $feature
            ? response()->json($feature)
            : response()->json(['message' => 'Feature not found'], 404);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'productId' => ['sometimes', 'nullable', 'integer'],
            'values' => ['sometimes', 'array'],
            'values.*.value' => ['required_with:values', 'string', 'max:255'],
            'values.*.image' => ['nullable', 'string'],
        ]);

        $feature = Feature::findOrFail($id);
        DB::transaction(function () use ($feature, $data): void {
            $feature->update([
                'name' => $data['name'] ?? $feature->name,
                'product_id' => $data['productId'] ?? $feature->product_id,
                'values_summary' => array_key_exists('values', $data)
                    ? collect($data['values'])->pluck('value')->implode(', ')
                    : $feature->values_summary,
            ]);
            if (array_key_exists('values', $data)) {
                $feature->values()->delete();
                $feature->values()->createMany($data['values']);
            }
        });
        return response()->json($feature->load('values'));
    }

    public function destroy(string $id): JsonResponse
    {
        Feature::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}