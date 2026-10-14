<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productId' => ['required', 'integer'],
        ]);

        return response()->json([
            'parts' => Part::where('product_id', $validated['productId'])->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePart($request);

        return response()->json(Part::create($data), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $part = Part::find($id);

        if (!$part) {
            return response()->json(['message' => 'Part not found'], 404);
        }

        return response()->json($part);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $data = $this->validatePart($request, true);

        $part = Part::findOrFail($id);
        $part->update($data);
        return response()->json($part->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        Part::findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    protected function validatePart(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => $isUpdate ? ['sometimes', 'string', 'max:255'] : ['required', 'string', 'max:255'],
            'price' => $isUpdate ? ['sometimes', 'numeric', 'min:0'] : ['required', 'numeric', 'min:0'],
            'type' => $isUpdate ? ['sometimes', 'integer'] : ['required', 'integer'],
            'condition' => ['nullable', 'integer'],
            'info' => ['nullable'],
            'product.id' => $isUpdate ? ['sometimes', 'integer'] : ['required', 'integer'],
            'productId' => ['sometimes', 'integer'],
        ];

        $validated = $request->validate($rules);

        if (isset($validated['product']['id'])) {
            $validated['product_id'] = $validated['product']['id'];
        } elseif (isset($validated['productId'])) {
            $validated['product_id'] = $validated['productId'];
        }

        unset($validated['product'], $validated['productId']);
        return $validated;
    }
}

