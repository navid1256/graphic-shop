<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Http\Requests\Admin\Categories\StoreRequest;

class CategoriesController extends Controller
{
    public function create()
    {
        return view('admin.category.create');
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        Category::create($validated);
        if (!$validated) {
            Session::flash('failed', 'دسته بندی ایجاد نشد. لطفا دوباره تلاش کنید.');
        }
        Session::flash('success', 'دسته بندی با موفقیت ایجاد شد.');

        return to_route('admin.categories.create');
    }
}
