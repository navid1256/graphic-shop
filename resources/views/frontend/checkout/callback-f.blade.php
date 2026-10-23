@extends('layouts.frontend.master')

@section('content')
    <!-- breadcrumb -->
    <div class="container">
        <div class="bread-crumb flex-w p-t-30">
            <a href="{{ route('frontend.products.all') }}" class="mtext-106 cl8 hov-cl1 trans-04">
                خانه
                <i class="fa fa-angle-left m-l-9 m-r-10" aria-hidden="true"></i>
            </a>

            <span class="mtext-106 cl4">
                نتیجه پرداخت
            </span>
        </div>
    </div>


    <!-- Shoping Cart -->
    <div class="bg0 p-t-75 p-b-85">
        <div class="container">
            <div class="row">
                <div class="col-12 my-5 text-center">
                    <i class="fa fa-times-circle text-danger d-block m-b-50 fs-80" aria-hidden="true"></i>
                    <h1 class="text-danger">پرداخت شما تأیید نشد</h1>
                    <p class="pt-4 fs-21" role="alert">{{ $errors->first('payment') ?: 'نتیجه پرداخت موفقی برای نمایش وجود ندارد.' }}</p>
                    <a href="{{ route('frontend.checkout') }}" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 m-t-30">بازگشت به سبد خرید</a>
                </div>
            </div>
        </div>
    </div>
@endsection
