<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccessoryCategory;
use Illuminate\Http\Request;

class AccessoryCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(AccessoryCategory::with('accessories')->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(AccessoryCategory $accessoryCategory)
    {
        return response()->json($accessoryCategory->load('accessories'));
    }
}
