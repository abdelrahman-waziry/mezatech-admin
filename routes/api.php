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

    // Diagnostics Ingestion Routes (Public)
    Route::prefix('diagnostics')->group(function () {
        Route::post('hardware', [\App\Http\Controllers\Api\DiagnosticController::class, 'storeHardware']);
        Route::post('cosmetic', [\App\Http\Controllers\Api\DiagnosticController::class, 'storeCosmetic']);
    });

    // Protected Catalog Routes (Repairs & Accessories)
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('repair-categories', \App\Http\Controllers\Api\RepairCategoryController::class)->only(['index', 'show']);
        Route::apiResource('repair-subcategories', \App\Http\Controllers\Api\RepairSubcategoryController::class)->only(['index', 'show']);
        Route::apiResource('repair-prices', \App\Http\Controllers\Api\RepairPriceController::class)->only(['index', 'show']);
        
        Route::apiResource('accessory-categories', \App\Http\Controllers\Api\AccessoryCategoryController::class)->only(['index', 'show']);
        Route::apiResource('accessories', \App\Http\Controllers\Api\AccessoryController::class)->only(['index', 'show']);
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

    // Audit Logs Routes (Protected, Super Admin Only)
    // EnsureFrontendRequestsAreStateful allows the Filament web session cookie to satisfy auth:sanctum
    Route::middleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, 'auth:sanctum', \App\Http\Middleware\EnsureSuperAdmin::class])->prefix('admin/audit-logs')->group(function () {
        Route::get('summary', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'summary']);
        Route::get('export', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'export']);
        Route::get('/', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'index']);
        Route::get('{uuid}', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'show']);
        Route::delete('{uuid}', [\App\Http\Controllers\Api\Admin\AuditLogController::class, 'destroy']);
    });
});
