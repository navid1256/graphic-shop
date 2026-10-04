<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoriesController;

Route::get('admin', function () {
    return view('admin.index');
})->name('admin.index');

Route::get('admin/users', function () {
    return view('admin.users.index');
})->name('admin.users');

Route::prefix('admin')->group(function () {
    Route::prefix('categories')->group(function () {
        Route::get('create', [CategoriesController::class, 'create'])->name('admin.categories.create');
        Route::get('', [CategoriesController::class, 'all'])->name('admin.categories.all');
        Route::post('', [CategoriesController::class, 'store'])->name('admin.categories.store');
    });
});
