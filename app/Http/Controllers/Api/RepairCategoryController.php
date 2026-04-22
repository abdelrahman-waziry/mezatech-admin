<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairCategory;
use Illuminate\Http\Request;

class RepairCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(RepairCategory::with('subcategories')->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(RepairCategory $repairCategory)
    {
        return response()->json($repairCategory->load('subcategories'));
    }
}
