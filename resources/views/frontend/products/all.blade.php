@extends('layouts.frontend.master')

@section('content')
    @php
        $filterParameters = request()->query();
    @endphp

    <!-- Product -->
    <div class="bg0 m-t-23 p-b-140">
        <div class="container">
            <div class="flex-w flex-sb-m p-b-52">
                <div class="flex-w flex-l-m filter-tope-group m-tb-10">
                    <button class="mtext-106 cl6 hov1 bor3 trans-04 m-l-32 m-tb-5 how-active1" data-filter="*">
                        همه دسته بندی ها
                    </button>
                    @foreach ($categories as $category)
                        <button class="mtext-106 cl6 hov1 bor3 trans-04 m-l-32 m-tb-5" data-filter=".isotope-item{{ $category->id }}">
                            {{ $category->title }}
                        </button>
                    @endforeach
                </div>

                <div class="flex-w flex-c-m m-tb-10">
                    <div class="flex-c-m stext-106 cl6 size-104 bor4 pointer hov-btn3 trans-04 m-tb-4 js-show-filter">
                        <i class="icon-filter cl2 m-l-6 fs-15 trans-04 zmdi zmdi-filter-list"></i>
                        <i class="icon-close-filter cl2 m-l-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
                        فیلتر کردن
                    </div>

                    <div class="flex-c-m stext-106 cl6 size-105 bor4 pointer hov-btn3 trans-04 m-tb-4 m-r-8 js-show-search">
                        <i class="icon-search cl2 m-l-6 fs-15 trans-04 zmdi zmdi-search"></i>
                        <i class="icon-close-search cl2 m-l-6 fs-15 trans-04 zmdi zmdi-close dis-none"></i>
                        جستجو
                    </div>
                </div>

                <!-- Search product -->
                <div class="dis-none panel-search w-full p-t-10 p-b-15">
                    <form action="{{ route('frontend.products.all') }}" method="GET" class="bor8 dis-flex p-l-15">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <input type="hidden" name="price" value="{{ request('price') }}">
                        <input type="hidden" name="sort" value="{{ request('sort', 'default') }}">

                        <button type="submit" class="size-113 flex-c-m fs-16 cl2 hov-cl1 trans-04" aria-label="جستجو">
                            <i class="zmdi zmdi-search"></i>
                        </button>

                        <label for="catalog-product-search" class="visually-hidden">جستجو در محصولات</label>
                        <input id="catalog-product-search" class="mtext-107 cl2 size-114 plh2 p-r-15" type="search" name="search" value="{{ $search }}" placeholder="متن خود را اینجا بنویسید و enter بزنید ...">
                    </form>
                </div>

                <!-- Filter -->
                <div class="dis-none panel-filter w-full p-t-10">
                    <div class="wrap-filter flex-w bg6 w-full p-lr-40 p-t-27 p-lr-15-sm">

                        <div class="filter-col1 p-r-15 p-b-27">
                            <div class="mtext-102 cl2 p-b-15">
                                نوع محصول
                            </div>

                            <ul>
                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_diff_key($filterParameters, ['type' => true])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('type') ? '' : 'filter-link-active' }}">
                                        همه
                                    </a>
                                </li>
                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['type' => 'free'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('type') === 'free' ? 'filter-link-active' : '' }}">
                                        رایگان
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['type' => 'paid'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('type') === 'paid' ? 'filter-link-active' : '' }}">
                                        پولی
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="filter-col2 p-r-15 p-b-27">
                            <div class="mtext-102 cl2 p-b-15">
                                قیمت
                            </div>

                            <ul>
                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_diff_key($filterParameters, ['price' => true])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('price') ? '' : 'filter-link-active' }}">
                                        همه
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['price' => '10-100'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('price') === '10-100' ? 'filter-link-active' : '' }}">
                                        ۱۰ الی ۱۰۰ هزار تومان
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['price' => '101-200'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('price') === '101-200' ? 'filter-link-active' : '' }}">
                                        ۱۰۱ الی ۲۰۰ هزار تومان
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['price' => '201-300'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('price') === '201-300' ? 'filter-link-active' : '' }}">
                                        ۲۰۱ الی ۳۰۰ هزار تومان
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div class="filter-col3 p-r-15 p-b-27 mr-auto">
                            <div class="mtext-102 cl2 p-b-15">
                                مرتب سازی براساس
                            </div>

                            <ul>
                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['sort' => 'default'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('sort', 'default') === 'default' ? 'filter-link-active' : '' }}">
                                        پیش فرض
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['sort' => 'popular'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('sort') === 'popular' ? 'filter-link-active' : '' }}">
                                        محبوبیت
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['sort' => 'latest'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('sort') === 'latest' ? 'filter-link-active' : '' }}">
                                        جدیدترین
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['sort' => 'price_asc'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('sort') === 'price_asc' ? 'filter-link-active' : '' }}">
                                        قیمت:‌ کم به زیاد
                                    </a>
                                </li>

                                <li class="p-b-6">
                                    <a href="{{ route('frontend.products.all', array_merge($filterParameters, ['sort' => 'price_desc'])) }}"
                                        class="filter-link stext-106 trans-04 {{ request('sort') === 'price_desc' ? 'filter-link-active' : '' }}">
                                        قیمت:‌زیاد به کم
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>

            <div class="row isotope-grid">
                @forelse ($products as $product)
                    @php
                        $quickViewImage = $product->demo_url
                            ? \Illuminate\Support\Facades\Storage::disk('product_images')->url($product->demo_url)
                            : ($product->thumbnail_url
                                ? \Illuminate\Support\Facades\Storage::disk('product_images')->url($product->thumbnail_url)
                                : asset('images/product-detail-01.jpg'));
                    @endphp
                    <div class="col-sm-6 col-md-4 col-lg-3 p-b-35 isotope-item isotope-item{{ $product->category_id }}">
                        <div class="block2">
                            <div class="block2-pic hov-img0">
                                <a href="{{ route('frontend.products.show', $product->id) }}">
                                    @if ($product->demo_url)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('product_images')->url($product->demo_url) }}" alt="{{ $product->title }}">
                                    @else
                                        <img src="{{ asset('images/product-01.jpg') }}" alt="{{ $product->title }}">
                                    @endif
                                </a>

                                <a href="{{ route('frontend.products.show', $product->id) }}" class="block2-btn flex-c-m stext-103 cl2 size-102 bg0 bor2 hov-btn1 p-lr-15 trans-04 js-show-modal1" data-product-title="{{ $product->title }}"
                                    data-product-price="{{ number_format($product->price) }} تومان" data-product-description="{{ $product->description }}" data-product-image="{{ $quickViewImage }}">
                                    مشاهده سریع
                                </a>
                            </div>

                            <div class="block2-txt flex-w flex-t p-t-14">
                                <div class="block2-txt-child1 flex-col-l">
                                    <a href="{{ route('frontend.products.show', $product->id) }}" class="mtext-106 cl4 hov-cl1 trans-04 js-name-b2 p-b-6">
                                        {{ $product->title }}
                                    </a>

                                    <span class="stext-105 cl3">
                                        {{ number_format($product->price) }} تومان
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 p-b-35">
                        <p class="stext-105 cl3">هنوز محصولی ثبت نشده است.</p>
                    </div>
                @endforelse
            </div>
            <!-- Load more -->
            <div class="flex-c-m flex-w w-full p-t-45">
                <a href="#" class="flex-c-m stext-101 cl5 size-103 bg2 bor1 hov-btn1 p-lr-15 trans-04">
                    مشاهده بیشتر
                </a>
            </div>
        </div>
    </div>

    <div class="wrap-modal1 js-modal1 p-t-60 p-b-20" role="dialog" aria-modal="true" aria-labelledby="quick-view-title">
        <div class="overlay-modal1 js-hide-modal1"></div>

        <div class="container">
            <div class="bg0 p-t-60 p-b-30 p-lr-15-lg how-pos3-parent">
                <button class="how-pos3 hov3 trans-04 js-hide-modal1" type="button" aria-label="بستن پنجره">
                    <img src="{{ asset('images/icons/icon-close.png') }}" alt="">
                </button>

                <div class="row">
                    <div class="col-md-6 col-lg-7 p-b-30">
                        <div class="wrap-pic-w">
                            <img class="js-quick-view-image" src="{{ asset('images/product-detail-01.jpg') }}" alt="">
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-5 p-b-30">
                        <div class="p-r-50 p-t-5 p-lr-0-lg">
                            <h4 class="mtext-105 cl2 p-b-14 js-quick-view-title" id="quick-view-title"></h4>
                            <span class="mtext-106 cl2 js-quick-view-price"></span>
                            <p class="stext-102 cl3 p-t-23 js-quick-view-description"></p>

                            <div class="p-t-33">
                                <a class="flex-c-m stext-101 cl0 size-101 bg1 bor1 hov-btn1 p-lr-15 trans-04 js-quick-view-link" href="#">
                                    مشاهده جزئیات محصول
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
