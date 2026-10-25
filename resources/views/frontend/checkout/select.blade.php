@extends('layouts.frontend.master')

@section('content')
    <div class="container">
        <nav class="bread-crumb flex-w p-t-30" aria-label="مراحل خرید">
            <a href="{{ route('frontend.checkout') }}" class="mtext-106 cl8 hov-cl1 trans-04">
                سبد خرید و اطلاعات کاربری
                <i class="fa fa-angle-left m-l-9 m-r-10" aria-hidden="true"></i>
            </a>
            <span class="mtext-106 cl4" aria-current="step">انتخاب درگاه پرداخت</span>
        </nav>
    </div>

    <section class="bg0 p-t-75 p-b-85" aria-labelledby="gateway-title" lang="fa">
        <div class="container">
            <div class="row">
                <div class="col-md-10 col-lg-8 m-lr-auto">
                    <div class="bor10 p-lr-40 p-t-30 p-b-40 p-lr-15-sm">
                        <h1 id="gateway-title" class="mtext-109 cl2 p-b-15">انتخاب درگاه پرداخت</h1>
                        <p id="gateway-help" class="stext-111 cl6 p-b-30">
                            یکی از درگاه‌های زیر را انتخاب کنید تا به صفحهٔ پرداخت آن منتقل شوید.
                        </p>

                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif

                        <form action="{{ route('frontend.payment.pay') }}" method="post">
                            @csrf
                            <input type="hidden" name="family" value="{{ $customer['family'] }}">
                            <input type="hidden" name="mobile" value="{{ $customer['mobile'] }}">
                            <input type="hidden" name="email" value="{{ $customer['email'] ?? '' }}">

                            <fieldset aria-describedby="gateway-help" class="p-b-30">
                                <legend class="stext-111 cl2 p-b-15">درگاه پرداخت (الزامی)</legend>
                                <div class="gateway-options">
                                    @foreach (['zarinpal' => 'زرین‌پال', 'zibal' => 'زیبال'] as $provider => $label)
                                        <div class="gateway-option">
                                            <input type="radio" id="gateway-{{ $provider }}" name="provider" value="{{ $provider }}" required
                                                @checked(old('provider', config('payments.default', 'zibal')) === $provider)>
                                            <label for="gateway-{{ $provider }}" class="gateway-label">
                                                <i class="zmdi zmdi-card fs-30" aria-hidden="true"></i>
                                                <span>
                                                    <strong class="mtext-106">{{ $label }}</strong>
                                                    <span class="gateway-description stext-111">پرداخت با کارت‌های عضو شتاب</span>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </fieldset>

                            <div class="flex-w flex-sb-m p-t-20 p-b-30 bor12">
                                <span class="mtext-101 cl2">مبلغ قابل پرداخت:</span>
                                <strong class="mtext-110 cl2">{{ number_format($total) }} تومان</strong>
                            </div>
                            <button type="submit" class="flex-c-m stext-101 cl0 size-116 bg3 bor14 hov-btn3 p-lr-15 trans-04 pointer m-t-30">
                                پرداخت {{ number_format($total) }} تومان
                            </button>
                            <a href="{{ route('frontend.checkout') }}" class="flex-c-m stext-111 cl6 hov-cl1 p-t-20">
                                بازگشت و ویرایش اطلاعات
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
