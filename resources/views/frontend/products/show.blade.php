@extends('layouts.frontend.master')

@section('content')
    <!-- breadcrumb -->
    <div class="container">
        <div class="bread-crumb flex-w p-t-30">
            <a href="{{ route('frontend.products.all') }}" class="mtext-106 cl8 hov-cl1 trans-04">
                خانه
                <i class="fa fa-angle-left m-l-9 m-r-10" aria-hidden="true"></i>
            </a>

            <a href="{{ route('frontend.products.all') }}" class="mtext-106 cl8 hov-cl1 trans-04">
                {{ $product->category->title }}
                <i class="fa fa-angle-left m-l-9 m-r-10" aria-hidden="true"></i>
            </a>

            <span class="mtext-106 cl4">
                {{ $product->title }}
            </span>
        </div>
    </div>


    <!-- Product Detail -->
    <section class="sec-product-detail bg0 p-t-65 p-b-60">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-lg-7 p-b-30">
                    <div class="p-l-25 p-l-0-lg">
                        <div class="wrap-slick3 flex-sb flex-w">
                            <div class="wrap-slick3-dots"></div>
                            <div class="wrap-slick3-arrows flex-sb-m flex-w"></div>

                            <div class="slick3 gallery-lb">
                                @php
                                    $productImage = $product->thumbnail_url
                                        ? \Illuminate\Support\Facades\Storage::disk('product_images')->url($product->thumbnail_url)
                                        : asset('images/product-detail-01.jpg');
                                @endphp
                                <div class="item-slick3" data-thumb="{{ $productImage }}">
                                    <div class="wrap-pic-w pos-relative">
                                        <img src="{{ $productImage }}" alt="{{ $product->title }}">

                                        <a class="flex-c-m size-108 how-pos1 bor0 fs-16 cl10 bg0 hov-btn3 trans-04" href="{{ $productImage }}">
                                            <i class="fa fa-expand"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-5 p-b-30">
                    <div class="p-r-50 p-t-5 p-lr-0-lg">
                        <h4 class="mtext-105 cl2 js-name-detail p-b-14">
                            {{ $product->title }}
                        </h4>

                        <span class="mtext-106 cl2">
                            {{ number_format($product->price) }} تومان
                        </span>

                        <p class="stext-102 cl3 p-t-23">
                            {{ $product->description }}
                        </p>

                        <!--  -->
                        <div class="p-t-33">

                            <div class="flex-w flex-r-m p-b-10">
                                <form action="{{ route('frontend.basket.add', $product->id) }}" method="POST" class="flex-w flex-m respon6-next">
                                    @csrf
                                    <button type="submit" class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04">
                                        افزودن به سبد خرید
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bor10 m-t-50 p-t-43 p-b-40">
                <!-- Tab01 -->
                <div class="tab01">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item p-b-10">
                            <a class="nav-link active" data-toggle="tab" href="#description" role="tab">توضیحات</a>
                        </li>

                        <li class="nav-item p-b-10">
                            <a class="nav-link" data-toggle="tab" href="#information" role="tab">مشخصات فایل</a>
                        </li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content p-t-43">
                        <!-- - -->
                        <div class="tab-pane fade show active" id="description" role="tabpanel">
                            <div class="how-pos2 p-lr-15-md">
                                <p class="mtext-106 cl6" style="line-height: 40px">
                                    {{ $product->description }}
                                </p>
                            </div>
                        </div>

                        <!-- - -->
                        <div class="tab-pane fade" id="information" role="tabpanel">
                            <div class="row">
                                <div class="col-sm-10 col-md-8 col-lg-6 m-lr-auto">
                                    <ul class="p-lr-28 p-lr-15-sm">
                                        <li class="flex-w flex-t p-b-7">
                                            <span class="mtext-106 cl3 size-205">
                                                صاحب طرح
                                            </span>

                                            <span class="mtext-106 size-206">
                                                {{ $product->owner->name }}
                                            </span>
                                        </li>

                                        <li class="flex-w flex-t p-b-7">
                                            <span class="mtext-106 cl3 size-205">
                                                تاریخ ایجاد
                                            </span>

                                            <span class="mtext-106 cl6 size-206">
                                                {{ $product->created_at->format('Y/m/d') }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </section>


    @if ($similarProducts->isNotEmpty())
        <section class="sec-relate-product bg0 p-t-45 p-b-105">
            <div class="container">
                <div class="p-b-45">
                    <h3 class="ltext-106 cl5 txt-center">
                        طرح های مشابه
                    </h3>
                </div>

                <div class="wrap-slick2">
                    <div class="slick2">
                        @foreach ($similarProducts as $similarProduct)
                            @php
                                $similarProductImage = $similarProduct->thumbnail_url
                                    ? \Illuminate\Support\Facades\Storage::disk('product_images')->url($similarProduct->thumbnail_url)
                                    : asset('images/product-01.jpg');
                            @endphp
                            <div class="item-slick2 p-l-15 p-r-15 p-t-15 p-b-15">
                                <div class="block2">
                                    <div class="block2-pic hov-img0">
                                        <a href="{{ route('frontend.products.show', $similarProduct->id) }}">
                                            <img src="{{ $similarProductImage }}" alt="{{ $similarProduct->title }}">
                                        </a>
                                    </div>

                                    <div class="block2-txt flex-w flex-t p-t-14">
                                        <div class="block2-txt-child1 flex-col-l">
                                            <a href="{{ route('frontend.products.show', $similarProduct->id) }}" class="mtext-106 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                                {{ $similarProduct->title }}
                                            </a>

                                            <span class="stext-105 cl3">
                                                {{ number_format($similarProduct->price) }} تومان
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
