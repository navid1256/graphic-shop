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
                            کاربران / تغییر
                            <a class="btn btn-primary float-left text-white py-2 px-4" href="{{ route('admin.users.all') }}">بازگشت به صفحه کاربران</a>
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
                            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="user_id" value="{{ $user->id }}">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="admin-user-edit-name">نام و نام خانوادگی</label>
                                                <input id="admin-user-edit-name" type="text" class="form-control" name="name" placeholder="نام و نام خانوادگی را وارد کنید" value="{{ old('name', $user->name) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="admin-user-edit-email">ایمیل</label>
                                                <input id="admin-user-edit-email" type="email" class="form-control" name="email" placeholder="ایمیل را وارد کنید" value="{{ old('email', $user->email) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="admin-user-edit-mobile">موبایل</label>
                                                <input id="admin-user-edit-mobile" type="text" class="form-control" name="mobile" placeholder="موبایل را وارد کنید" value="{{ old('mobile', $user->mobile) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="admin-user-edit-role">نقش کاربری</label>
                                                <select id="admin-user-edit-role" class="form-control" name="role">
                                                    <option value="0" disabled selected>انتخاب نقش</option>
                                                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>کاربر عادی</option>
                                                    {{-- <option value="2">طراح و فروشنده</option> --}}
                                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>مدیر</option>
                                                </select>
                                            </div>
                                        </div>
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
                <!-- /.container-fluid -->
            </div>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
    @endsection
