@extends('layouts.admin.master')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 mt-4">
                    <div class="col-12">
                        <h1 class="m-0 text-dark">
                            <a class="nav-link drawer" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
                            محصولات / افزودن
                            <a class="btn btn-primary float-left text-white py-2 px-4" href="{{ route('admin.products.all') }}">بازگشت به صفحه محصولات</a>
                        </h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @include('error-handling.message')
                <div class="row mt-5">
                    <div class="col-md-12">
                        <div class="card card-defualt">
                            <!-- form start -->
                            <form action="{{ route('admin.products.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="admin-product-create-title">عنوان</label>
                                                <input id="admin-product-create-title" type="text" class="form-control" name="title" placeholder="نامک را وارد کنید">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="admin-product-create-category-id">دسته بندی</label>
                                                <select id="admin-product-create-category-id" class="form-control" name="category_id">
                                                    <option value="" disabled selected>انتخاب دسته بندی</option>
                                                    @foreach ($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="admin-product-create-price">قیمت</label>
                                                <input id="admin-product-create-price" type="text" class="form-control" name="price" placeholder="قیمت را وارد کنید">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="admin-product-create-thumbnail-url">تصویر شاخص</label>
                                                <input id="admin-product-create-thumbnail-url" class="form-control" type="file" name="thumbnail_url">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="admin-product-create-demo-url">تصویر محصول</label>
                                                <input id="admin-product-create-demo-url" class="form-control" type="file" name="demo_url">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="admin-product-create-source-url">سورس اصلی محصول</label>
                                                <input id="admin-product-create-source-url" class="form-control" type="file" name="source_url">
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <label for="editor" id="product-description-label">توضیحات</label>
                                        <textarea name="description" id="editor">لطفا متن مورد نظر خودتان را وارد کنید</textarea>
                                    </div>
                                </div>
                                <!-- /.card-body -->

                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary float-left">ذخیره کردن</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
@endsection
