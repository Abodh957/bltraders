<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/add-shop', [AuthController::class, 'addShop']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Banner API (public)
Route::get('/banners', [BannerController::class, 'index']);
Route::get('/banners/{id}', [BannerController::class, 'show']);

// Banner API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/banners', [BannerController::class, 'store']);
    Route::post('/banners/{id}', [BannerController::class, 'update']);
    Route::delete('/banners/{id}', [BannerController::class, 'destroy']);
    Route::post('/banners-bulk-delete', [BannerController::class, 'bulkDelete']);
});

// Brand API (public)
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);

// Brand API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/brands', [BrandController::class, 'store']);
    Route::post('/brands/{id}', [BrandController::class, 'update']);
    Route::delete('/brands/{id}', [BrandController::class, 'destroy']);
    Route::post('/brands-bulk-delete', [BrandController::class, 'bulkDelete']);
});

// Store API (public)
Route::get('/stores', [StoreController::class, 'index']);
Route::get('/stores/{id}', [StoreController::class, 'show']);

// Store API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/stores', [StoreController::class, 'store']);
    Route::post('/stores/{id}', [StoreController::class, 'update']);
    Route::delete('/stores/{id}', [StoreController::class, 'destroy']);
});

// Category API (public) — filter by ?store_id=1
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Category API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
});

// Sub Category API (public) — filter by ?store_id=1 or ?category_id=1
Route::get('/sub-categories', [SubCategoryController::class, 'index']);
Route::get('/sub-categories/{id}', [SubCategoryController::class, 'show']);

// Sub Category API (protected)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/sub-categories', [SubCategoryController::class, 'store']);
    Route::post('/sub-categories/{id}', [SubCategoryController::class, 'update']);
    Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'destroy']);
});
