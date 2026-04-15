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

    // Analytics Ingestion Routes (Public)
    Route::prefix('analytics')->group(function () {
        Route::post('requests', [\App\Http\Controllers\Api\AnalyticsIngestionController::class, 'storeRequest']);
        Route::post('events', [\App\Http\Controllers\Api\AnalyticsIngestionController::class, 'storeEvent']);
    });

    // Trade-in Request Routes (Protected)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('trade-in-requests', \App\Http\Controllers\TradeInRequestController::class);
    });

    // Analytics Admin Routes (Protected)
    Route::middleware('auth:sanctum')->prefix('admin/analytics')->group(function () {
        Route::get('performance', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'performance']);
        Route::get('traffic', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'traffic']);
        Route::get('endpoints/top', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'topEndpoints']);
        Route::get('tradeins/summary', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'tradeinsSummary']);
        Route::get('tradeins/demand', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'tradeinsDemand']);
        Route::get('tradeins/conditions', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'tradeinsConditions']);
        Route::get('geography', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'geography']);
        Route::get('devices', [\App\Http\Controllers\Api\Admin\AnalyticsQueryController::class, 'devices']);
    });
});
