<?php

namespace App\Http\Controllers;

use App\Services\PartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartController extends Controller
{
    public function __construct(private readonly PartService $partService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productId' => ['required', 'integer'],
        ]);

        $payload = $this->partService->fetchAll([
            'productId' => $validated['productId'],
        ]);

        $parts = $this->extractParts($payload);

        return response()->json([
            'parts' => $parts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validatePart($request);

        $created = $this->partService->create($data);

        return response()->json($created, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $part = $this->partService->fetchOne($id);

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

        $updated = $this->partService->update($id, $data);

        return response()->json($updated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->partService->delete($id);

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

        return $validated;
    }

    protected function extractParts(array $payload): array
    {
        if (isset($payload['parts']) && is_array($payload['parts'])) {
            return $payload['parts'];
        }

        if (isset($payload['data']['parts']) && is_array($payload['data']['parts'])) {
            return $payload['data']['parts'];
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        if (isset($payload['items']) && is_array($payload['items'])) {
            return $payload['items'];
        }

        return is_array($payload) ? array_values($payload) : [];
    }
}

