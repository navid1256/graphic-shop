<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CategoriesController;
use App\Http\Controllers\Admin\ProductsController;

Route::get('admin', function () {
    return view('admin.index');
})->name('admin.index');

Route::get('password', function () {
    return password_hash('admin', PASSWORD_DEFAULT);
})->name('admin.password');

Route::get('admin/users', function () {
    return view('admin.users.index');
})->name('admin.users');

Route::prefix('admin')->group(function () {
    Route::prefix('categories')->group(function () {
        Route::get('create', [CategoriesController::class, 'create'])->name('admin.categories.create');
        Route::get('', [CategoriesController::class, 'all'])->name('admin.categories.all');
        Route::post('', [CategoriesController::class, 'store'])->name('admin.categories.store');
        Route::put('{category_id}/update', [CategoriesController::class, 'update'])->name('admin.categories.update');
        Route::delete('{category_id}/delete', [CategoriesController::class, 'delete'])->name('admin.categories.delete');
        Route::get('{category_id}/edit', [CategoriesController::class, 'edit'])->name('admin.categories.edit');
    });

    Route::prefix('product')->group(function () {
        Route::get('create', [ProductsController::class, 'create'])->name('admin.products.create');
        Route::post('', [ProductsController::class, 'store'])->name('admin.products.store');
        Route::get('', [ProductsController::class, 'all'])->name('admin.products.all');
    });
});
