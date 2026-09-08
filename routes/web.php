<?php

use App\Http\Controllers\Vendor\Auth\RegisteredVendorController;
use App\Http\Controllers\Vendor\Auth\VendorLoginController;
use App\Http\Controllers\Vendor\CredentialController;
use App\Http\Controllers\Vendor\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Health check endpoint for Wasmer
Route::get('/health', function () {
    return response()->json(['status' => 'healthy'], 200);
});

Route::get('/docs', function () {
    return view('swagger');
});

Route::middleware('guest:vendor')->group(function () {
    Route::get('/register-vendor', [RegisteredVendorController::class, 'create'])->name('vendor.register');
    Route::post('/register-vendor', [RegisteredVendorController::class, 'store']);
    Route::get('/vendor-login', [VendorLoginController::class, 'create'])->name('vendor.login');
    Route::post('/vendor-login', [VendorLoginController::class, 'store']);
});

Route::middleware('auth:vendor')->prefix('vendor')->name('vendor.')->group(function () {
    Route::post('/logout', [VendorLoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/embed-key', [DashboardController::class, 'generateEmbedKey'])->name('embed-key.store');
    Route::post('/credentials', [CredentialController::class, 'store'])->name('credentials.store');
});

