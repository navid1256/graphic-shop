<?php

use App\Models\Category;
use Illuminate\Support\Facades\Route;


Route::get('products/all', function () {
    return view('frontend.products.all');
});

Route::get('admin', function () {
    return view('admin.index');
});

Route::get('admin/users', function () {
    return view('admin.users.index');
})->name('admin.users');
