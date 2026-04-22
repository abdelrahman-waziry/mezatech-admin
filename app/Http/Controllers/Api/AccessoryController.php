<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accessory;
use Illuminate\Http\Request;

class AccessoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Accessory::with('category')->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(Accessory $accessory)
    {
        return response()->json($accessory->load('category'));
    }
}
