<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RepairPrice;
use Illuminate\Http\Request;

class RepairPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(RepairPrice::with('subcategory')->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(RepairPrice $repairPrice)
    {
        return response()->json($repairPrice->load('subcategory'));
    }
}
