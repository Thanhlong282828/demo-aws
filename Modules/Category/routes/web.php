<?php

use Illuminate\Support\Facades\Route;
use Modules\Category\Http\Controllers\CategoryController;
use Modules\Shop\Http\Middleware\Admin;

Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('categories/{category}', [CategoryController::class, 'show'])->whereNumber('category')->name('categories.show');
Route::middleware(['auth', Admin::class])->group(function () { // D23: Category read public, create/update/delete bắt buộc ADMIN ở backend.
    Route::resource('categories', CategoryController::class)->except(['index', 'show']);
});
