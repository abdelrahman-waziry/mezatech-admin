<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairSubcategory;
use Illuminate\Http\Request;

class RepairSubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(RepairSubcategory::with(['category', 'prices'])->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(RepairSubcategory $repairSubcategory)
    {
        return response()->json($repairSubcategory->load(['category', 'prices']));
    }
}
