<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeviceTypeController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ConditionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\AdminController;

Route::middleware('api')->prefix('v1')->group(function () {
   // Brand Routes
    Route::apiResource('brands', BrandController::class);

    // Condition Routes
    Route::apiResource('conditions', ConditionController::class);

    // Product Routes
    Route::apiResource('products', ProductController::class);
    Route::post('products/calculate', [ProductController::class, 'calculatePrice']);

    // Variant Routes
    Route::apiResource('variants', VariantController::class);

    // Part Routes
    Route::apiResource('parts', PartController::class);

    // File Routes
    Route::post('files', [FileController::class, 'store']);
    Route::get('files', [FileController::class, 'index']);
    Route::delete('files', [FileController::class, 'destroy']);

    // Admin Routes
    Route::post('admin/login', [AdminController::class, 'login']);
});
