<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ProductController;
use Modules\Shop\Http\Middleware\Admin;

Route::get('products', [ProductController::class, 'index'])->name('products.index');
Route::middleware(['auth', Admin::class])->group(function () { // D23: mọi thao tác ghi Product trong nhóm phải đã login và là ADMIN; GET index bên ngoài vẫn public.
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});
