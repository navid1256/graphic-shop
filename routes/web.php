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
        Route::delete('{product_id}/delete', [ProductsController::class, 'delete'])->name('admin.products.delete');
        Route::get('{product_id}/edit', [ProductsController::class, 'edit'])->name('admin.products.edit');
        Route::put('{product_id}/update', [ProductsController::class, 'update'])->name('admin.products.update');


        Route::get('{product_id}/thumbnail', [ProductsController::class, 'thumbnail'])->name('admin.products.thumbnail');
        Route::get('{product_id}/download/demo', [ProductsController::class, 'downloadDemo'])->name('admin.products.download.demo');
        Route::get('{product_id}/download/source', [ProductsController::class, 'downloadSource'])->name('admin.products.download.source');
    });

    Route::prefix('users')->group(function () {
        Route::get('', [\App\Http\Controllers\Admin\UsersController::class, 'all'])->name('admin.users.all');
        Route::get('create', [\App\Http\Controllers\Admin\UsersController::class, 'create'])->name('admin.users.create');
        Route::post('', [\App\Http\Controllers\Admin\UsersController::class, 'store'])->name('admin.users.store');
        Route::get('{user_id}/edit', [\App\Http\Controllers\Admin\UsersController::class, 'edit'])->name('admin.users.edit');
        Route::put('{user_id}/update', [\App\Http\Controllers\Admin\UsersController::class, 'update'])->name('admin.users.update');
        Route::delete('{user_id}/delete', [\App\Http\Controllers\Admin\UsersController::class, 'delete'])->name('admin.users.delete');
    });
});
