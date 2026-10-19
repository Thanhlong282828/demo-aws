<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\Api\CategoryController;
use Modules\Shop\Http\Middleware\Admin;

Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::middleware(['auth:sanctum', Admin::class])->group(function (): void {
    Route::apiResource('categories', CategoryController::class)->only(['store', 'update', 'destroy']);
});
