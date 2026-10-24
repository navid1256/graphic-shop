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
                    <i class="fa fa-check-circle text-success d-block m-b-50 fs-80" aria-hidden="true"></i>
                    <h1 class="text-success">پرداخت شما با موفقیت انجام شد</h1>
                    <p class="pt-4 fs-21" role="status">
                        مبلغ پرداخت‌شده: {{ number_format($paymentInfo['amount_rials'] / 10) }} تومان
                    </p>
                    <p class="pt-4 fs-21">
                        @if (!empty($paymentInfo['reference_id']))
                            کد پیگیری: <bdi>{{ $paymentInfo['reference_id'] }}</bdi>
                        @else
                            شناسه تراکنش: <bdi>{{ $paymentInfo['transaction_id'] }}</bdi>
                        @endif
                    </p>
                    @if ($emailDeliveryError)
                        <p class="pt-4 text-danger" role="alert">{{ $emailDeliveryError }}</p>
                    @else
                        <p class="pt-4 text-success" role="status">فایل‌های خرید به ایمیل شما ارسال شدند.</p>
                    @endif
                </div>
                <div class="col-12 m-b-50 mt-5">
                    <a href="{{ route('frontend.products.all') }}" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04">بازگشت به فروشگاه</a>
                </div>
            </div>
        </div>
    </div>
@endsection
