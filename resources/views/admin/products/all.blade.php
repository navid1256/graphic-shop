@extends('layouts.admin.master')

@section('content')
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 mt-4">
                    <div class="col-12">
                        <h1 class="m-0 text-dark">
                            <a class="nav-link drawer" data-widget="pushmenu" href="#">
                                <i class="fa fa-bars"></i>
                            </a>
                            محصولات
                            <a class="btn btn-primary float-left text-white py-2 px-4" href="{{ route('admin.products.create') }}">افزودن محصول جدید</a>
                        </h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <div class="container-fluid">
                @include('error-handling.message')

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">لیست محصولات</h3>
                                <form action="{{ route('admin.products.all') }}" method="GET" class="card-tools">
                                    <div class="input-group input-group-sm" style="width: 180px;">
                                        <input type="search" name="search" value="{{ $search }}" class="form-control float-right" placeholder="جستجو">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" aria-label="جستجو">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-valign-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>آیدی</th>
                                            <th>عنوان</th>
                                            <th>دسته‌بندی</th>
                                            <th>مالک طرح</th>
                                            <th>توضیحات</th>
                                            <th>لینک دمو</th>
                                            <th>لینک دانلود</th>
                                            <th>قیمت</th>
                                            <th>تاریخ ایجاد</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($products as $product)
                                            <tr>
                                                <td>{{ $product->id }}</td>
                                                <td>
                                                    @if ($product->thumbnail_url)
                                                        <img src="{{ route('admin.products.thumbnail', $product->id) }}" class="product_img" alt="" width="48" height="48" loading="lazy">
                                                    @endif
                                                    {{ $product->title }}
                                                </td>
                                                <td>{{ $product->category?->title ?? '—' }}</td>
                                                <td>{{ $product->owner?->name ?? '—' }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 80) }}</td>
                                                <td>
                                                    @if ($product->demo_url)
                                                        <a href="{{ route('admin.products.download.demo', $product->id) }}" class="btn btn-default btn-icons" title="لینک دمو">

                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($product->source_url)
                                                        <a href="{{ route('admin.products.download.source', $product->id) }}" class="btn btn-default btn-icons" title="لینک دانلود">
                                                            <i class="fa fa-link"></i>
                                                        </a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ number_format($product->price) }} تومان</td>
                                                <td>{{ $product->created_at->format('Y-m-d') }}</td>
                                                <td>

                                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-default btn-icons" aria-label="ویرایش">
                                                        <i class="fa fa-edit"></i>
                                                    </a>

                                                    <form action="{{ route('admin.products.delete', $product->id) }}" method="post">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="btn btn-default btn-icons" aria-label="حذف">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>

                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center">
                                                    {{ $search ? 'محصولی پیدا نشد.' : 'محصولی ثبت نشده است.' }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
