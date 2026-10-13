<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Products\StoreRequest;
use App\Http\Requests\Admin\Products\UpdateRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Utilities\ImageUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Stmt\TryCatch;

class ProductsController extends Controller
{
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.add', compact('categories'));
    }

    public function all()
    {
        $query = Product::with(['category', 'owner'])->latest();

        if (request()->filled('search')) {
            $query->where('title', 'like', '%' . request('search') . '%');
        }

        $products = $query->paginate(10)->withQueryString();

        return view('admin.products.all', [
            'products' => $products,
            'search' => request('search', ''),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        // Validate the request data
        $validatedData = $request->validated();
        $admin = User::where('email', 'admin@yahoo.com')->first();
        $createdProduct = Product::create([
            'title' => $validatedData['title'],
            'price' => $validatedData['price'],
            'category_id' => $validatedData['category_id'],
            'description' => $validatedData['description'],
            'owner_id' => $admin->id,
        ]);
        try {
            $this->uploadImages($createdProduct, $validatedData);
            return to_route('admin.products.create')
                ->with('success', 'محصول با موفقیت ایجاد شد.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'image_upload' => 'آپلود تصاویر انجام نشد. دوباره تلاش کنید.',
                ]);
        }
    }

    public function downloadDemo($product_id)
    {
        $product = Product::findOrFail($product_id);
        $demoFilePath = $product->demo_url;

        if (!$demoFilePath || !Storage::disk('product_images')->exists($demoFilePath)) {
            abort(404, 'Demo file not found.');
        }

        return response()->download(
            Storage::disk('product_images')->path($demoFilePath),
            basename($demoFilePath)
        );
    }

    public function downloadSource($product_id)
    {
        $product = Product::findOrFail($product_id);
        $sourceFilePath = $product->source_url;

        if (!$sourceFilePath || !Storage::disk('product_images')->exists($sourceFilePath)) {
            abort(404, 'Source file not found.');
        }

        return response()->download(
            Storage::disk('product_images')->path($sourceFilePath),
            basename($sourceFilePath)
        );
    }

    public function delete($product_id)
    {
        $product = Product::findOrFail($product_id);

        $product->delete();

        return back()->with('success', 'محصول حذف شد');
    }

    public function edit($product_id)
    {
        $product = Product::findOrFail($product_id);
        $categories = Category::all();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateRequest $request, $product_id)
    {
        $validatedData = $request->validated();

        $product = Product::findOrFail($product_id);
        $product->update([
            'title' => $validatedData['title'],
            'price' => $validatedData['price'],
            'category_id' => $validatedData['category_id'],
            'description' => $validatedData['description'],
        ]);

        try {
            $this->uploadImages($product, $validatedData);

            return to_route('admin.products.edit', $product->id)
                ->with('success', 'محصول با موفقیت ویرایش شد.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'image_upload' => 'آپلود تصاویر انجام نشد. دوباره تلاش کنید.',
                ]);
        }
    }



    public function thumbnail($product_id)
    {
        $product = Product::findOrFail($product_id);
        $thumbnailPath = $product->thumbnail_url;

        abort_unless($thumbnailPath && Storage::disk('product_images')->exists($thumbnailPath), 404);

        return response()->file(Storage::disk('product_images')->path($thumbnailPath));
    }

    private function uploadImages(Product $product, array $validatedData): void
    {
        $images = [];

        foreach (['thumbnail_url', 'demo_url', 'source_url'] as $field) {
            $file = $validatedData[$field] ?? null;

            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $images[$field] = $file;
            }
        }

        if ($images === []) {
            return;
        }

        $imagePaths = ImageUploader::uploadMany(
            $images,
            (string) $product->id
        );

        $product->update($imagePaths);
    }
}
