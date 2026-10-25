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
                            <a class="nav-link drawer" data-widget="pushmenu" href="#" aria-label="باز و بسته کردن منوی مدیریت"><i class="fa fa-bars" aria-hidden="true"></i></a>
                            سفارشات
                        </h1>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">لیست سفارشات</h3>

                                <div class="card-tools">
                                    <div class="input-group input-group-sm" style="width: 150px;">
                                        <label for="admin-orders-search" class="sr-only">جستجو در سفارش‌ها</label>
                                        <input id="admin-orders-search" type="text" name="table_search" class="form-control float-right" placeholder="جستجو">

                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-default" aria-label="جستجو"><i class="fa fa-search" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-header -->
                            <div class="table table-striped table-valign-middle mb-0">
                                <table class="table table-hover mb-0">
                                    <tbody>
                                        <tr>
                                            <th>نام کاربر</th>
                                            <th>مبلغ</th>
                                            <th>کد رهگیری</th>
                                            <th>وضعیت</th>
                                            <th>تاریخ ثبت سفارش</th>
                                            <th>مشاهده سفارش</th>
                                        </tr>
                                        @foreach ($orders as $order)
                                            <tr>

                                                <td>{{ $order->user->name }}</td>
                                                <td>{{ $order->amount }} تومان</td>
                                                <td>{{ $order->ref_code }}</td>
                                                <td>
                                                    <span class="badge {{ $order->status === 'paid' ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $order->status === 'paid' ? 'پرداخت‌شده' : 'پرداخت‌نشده' }}
                                                    </span>
                                                </td>
                                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-default btn-icons" data-open-order-items aria-controls="order_items" aria-haspopup="dialog" aria-label="مشاهده اقلام سفارش {{ $order->ref_code ?: $order->id }}" title="مشاهده سبد خرید"><i class="fa fa-shopping-cart" aria-hidden="true"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                        <div class="d-flex justify-content-center">
                            <ul class="pagination mt-3">
                                {{ $orders->links() }}
                            </ul>
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

    <dialog class="admin-order-items-dialog" id="order_items" aria-labelledby="order-items-title" closedby="any">
        <div class="admin-order-items-content">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="order-items-title">آیتم‌های سبد خرید</h5>
                    <button type="button" class="close" data-close-order-items aria-label="بستن جزئیات سفارش" autofocus>
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="table table-striped table-valign-middle mb-0">
                        <table class="table table-hover mb-0">
                            <tbody>
                                <tr>
                                    <th>عنوان</th>
                                    <th>دسته بندی</th>
                                    <th>لینک دمو</th>
                                    <th>لینک دانلود</th>
                                    <th>قیمت</th>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="{{ asset('admin/dist/img/user6-128x128.jpg') }}" class="product_img" alt="تصویر نمونهٔ محصول">
                                        کارت ویزیت مشاور املاک
                                    </td>
                                    <td>کارت ویزیت</td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دمو"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دانلود"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>۳۹۰۰۰ تومان</td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="{{ asset('admin/dist/img/user6-128x128.jpg') }}" class="product_img" alt="تصویر نمونهٔ محصول">
                                        کارت ویزیت مشاور املاک
                                    </td>
                                    <td>کارت ویزیت</td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دمو"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دانلود"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>۳۹۰۰۰ تومان</td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="{{ asset('admin/dist/img/user6-128x128.jpg') }}" class="product_img" alt="تصویر نمونهٔ محصول">
                                        کارت ویزیت مشاور املاک
                                    </td>
                                    <td>کارت ویزیت</td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دمو"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دانلود"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>۳۹۰۰۰ تومان</td>
                                </tr>
                                <tr>
                                    <td>
                                        <img src="{{ asset('admin/dist/img/user6-128x128.jpg') }}" class="product_img" alt="تصویر نمونهٔ محصول">
                                        کارت ویزیت مشاور املاک
                                    </td>
                                    <td>کارت ویزیت</td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دمو"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-default btn-icons" title="لینک دانلود"><i class="fa fa-link"></i></a>
                                    </td>
                                    <td>۳۹۰۰۰ تومان</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-close-order-items>بستن</button>
                </div>
            </div>
        </div>
    </dialog>

    <script src="{{ asset('admin/js/order-items-dialog.js') }}" defer></script>
@endsection
