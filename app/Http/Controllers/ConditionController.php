<?php

namespace App\Http\Controllers;

use App\Models\Condition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConditionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $conditions = Condition::all();

        return response()->json([
            'data' => $conditions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:conditions,name',
            'description' => 'nullable|string',
            'price_modifier' => 'required|numeric|min:0|max:999999.99',
        ]);

        $condition = Condition::create($validated);

        return response()->json([
            'message' => 'Condition created successfully',
            'data' => $condition,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Condition $condition): JsonResponse
    {
        return response()->json([
            'data' => $condition,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Condition $condition): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:conditions,name,'.$condition->id,
            'description' => 'nullable|string',
            'price_modifier' => 'sometimes|required|numeric|min:0|max:999999.99',
        ]);

        $condition->update($validated);

        return response()->json([
            'message' => 'Condition updated successfully',
            'data' => $condition->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Condition $condition): JsonResponse
    {
        $condition->delete();

        return response()->json([
            'message' => 'Condition deleted successfully',
        ]);
    }
}









