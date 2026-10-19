<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Api\ProductController;
use Modules\Shop\Http\Middleware\Admin;

Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::middleware(['auth:sanctum', Admin::class])->group(function (): void {
    Route::apiResource('products', ProductController::class)->only(['store', 'update', 'destroy']);
});
