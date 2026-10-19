<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Modules\Shop\Http\Controllers\Api\OrderController;
use Modules\Shop\Http\Middleware\Admin;

Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login');
Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::middleware(Admin::class)->prefix('admin')->group(function (): void {
        Route::get('orders', [OrderController::class, 'admin'])->name('admin.orders.index');
        Route::put('orders/{order}/status', [OrderController::class, 'status'])->name('admin.orders.status');
    });
});
