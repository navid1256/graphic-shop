<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Category;
use App\Http\Requests\Admin\Categories\StoreRequest;
use App\Http\Requests\Admin\Categories\UpdateRequest;

class CategoriesController extends Controller
{
    public function create()
    {
        return view('admin.categories.create');
    }

    public function all()
    {
        $categories = Category::paginate(5);
        return view('admin.categories.all', compact('categories'));
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();

        Category::create($validated);

        Session::flash('success', 'دسته بندی با موفقیت ایجاد شد.');

        return to_route('admin.categories.create');
    }

    public function delete($category_id)
    {
        $category = Category::findOrFail($category_id);
        $category->delete();

        Session::flash('success', 'دسته بندی با موفقیت حذف شد.');

        return to_route('admin.categories.all');
    }

    public function edit($category_id)
    {
        $category = Category::findOrFail($category_id);
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateRequest $request, $category_id)
    {
        $category = Category::findOrFail($category_id);
        $category->update($request->validated());

        Session::flash('success', 'دسته بندی با موفقیت بروزرسانی شد.');

        return to_route('admin.categories.edit', $category_id);
    }
}
