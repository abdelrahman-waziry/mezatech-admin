<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Health check endpoint for Wasmer
Route::get('/health', function () {
    return response()->json(['status' => 'healthy'], 200);
});
