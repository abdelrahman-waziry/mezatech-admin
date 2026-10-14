<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::query()->get()->map->toArray()->values()->all();

        return response()->json(['tags' => $tags]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);

        return response()->json(Tag::create($data), 201);
    }

    public function show(string $id): JsonResponse
    {
        $tag = Tag::find($id);

        return $tag
            ? response()->json($tag)
            : response()->json(['message' => 'Tag not found'], 404);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);

        $tag = Tag::findOrFail($id);
        $tag->update($data);
        return response()->json($tag);
    }

    public function destroy(string $id): JsonResponse
    {
        Tag::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}