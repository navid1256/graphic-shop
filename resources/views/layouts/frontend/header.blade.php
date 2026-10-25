<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <title>سون گرافیک</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--===============================================================================================-->
    <link rel="icon" type="image/png" href="{{ asset('images/icons/favicon.png') }}" />
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('fonts/font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('fonts/iconic/css/material-design-iconic-font.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('fonts/linearicons-v1.0.0/icon-font.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/animate/animate.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/css-hamburgers/hamburgers.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/animsition/css/animsition.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/select2/select2.min.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/daterangepicker/daterangepicker.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/slick/slick.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/MagnificPopup/magnific-popup.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('vendor/perfect-scrollbar/perfect-scrollbar.css') }}">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/util.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <!--===============================================================================================-->
</head>

<body class="animsition">

    <!-- Header -->
    <header class="header-v4">
        <!-- Header desktop -->
        <div class="container-menu-desktop">
            <!-- Topbar -->
            <div class="top-bar">
                <div class="content-topbar flex-sb-m h-full container">
                    <div class="left-top-bar">
                        لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ
                    </div>

                    <div class="right-top-bar flex-w h-full">
                        <a href="#" class="flex-c-m trans-04 p-lr-25">
                            راهنمای خرید و قوانین
                        </a>

                        <a href="#" class="flex-c-m trans-04 p-lr-25">
                            اکانت من
                        </a>
                    </div>
                </div>
            </div>

            <div class="wrap-menu-desktop how-shadow1">
                <nav class="limiter-menu-desktop container">

                    <!-- Logo desktop -->
                    <a href="/" class="logo">
                        <img src="{{ asset('images/icons/logo-01.png') }}" alt="IMG-LOGO">
                    </a>

                    <!-- Menu desktop -->
                    <div class="menu-desktop">
                        <ul class="main-menu">

                            <li class="active-menu">
                                <a href="/">صفحه اصلی</a>
                            </li>
                            <li>
                                <a href="/">تماس با ما</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Icon header -->
                    <div class="wrap-icon-header flex-w flex-r-m">
                        <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 js-show-modal-search">
                            <i class="zmdi zmdi-search"></i>
                        </div>

                        <button type="button" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="{{ $basketCount }}" aria-label="سبد خرید، {{ $basketCount }} محصول"
                            aria-controls="header-basket">
                            <i class="zmdi zmdi-shopping-cart" aria-hidden="true"></i>
                        </button>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Header Mobile -->
        <div class="wrap-header-mobile">
            <!-- Logo moblie -->
            <div class="logo-mobile">
                <a href="index.html"><img src="{{ asset('images/icons/logo-01.png') }}" alt="IMG-LOGO"></a>
            </div>

            <!-- Icon header -->
            <div class="wrap-icon-header flex-w flex-r-m m-l-15">
                <div class="icon-header-item cl2 hov-cl1 trans-04 p-l-11 js-show-modal-search">
                    <i class="zmdi zmdi-search"></i>
                </div>

                <button type="button" class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart" data-notify="{{ $basketCount }}" aria-label="سبد خرید، {{ $basketCount }} محصول"
                    aria-controls="header-basket">
                    <i class="zmdi zmdi-shopping-cart" aria-hidden="true"></i>
                </button>

                <a href="#" class="dis-block icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti" data-notify="0">
                    <i class="zmdi zmdi-favorite-outline"></i>
                </a>
            </div>

            <!-- Button show menu -->
            <div class="btn-show-menu-mobile hamburger hamburger--squeeze">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </div>
        </div>


        <!-- Menu Mobile -->
        <div class="menu-mobile">
            <ul class="main-menu-m">
                <li>
                    <a href="/">صفحه اصلی</a>
                </li>
                <li>
                    <a href="/">تماس با ما</a>
                </li>
            </ul>
        </div>

        <!-- Modal Search -->
        <div class="modal-search-header flex-c-m trans-04 js-hide-modal-search">
            <div class="container-search-header">
                <button class="flex-c-m btn-hide-modal-search trans-04 js-hide-modal-search">
                    <img src="{{ asset('images/icons/icon-close2.png') }}" alt="CLOSE">
                </button>

                <form class="wrap-search-header flex-w p-l-15" action="{{ route('frontend.products.all') }}" method="GET">
                    <button type="submit" class="flex-c-m trans-04" aria-label="جستجو در محصولات">
                        <i class="zmdi zmdi-search" aria-hidden="true"></i>
                    </button>
                    <label for="header-product-search" class="visually-hidden">جستجو در محصولات</label>
                    <input id="header-product-search" class="plh3" type="text" name="search" placeholder="متن خود را اینجا بنویسید ...">
                </form>
            </div>
        </div>
    </header>

    <!-- Cart -->
    <div class="wrap-header-cart js-panel-cart" id="header-basket">
        <div class="s-full js-hide-cart"></div>

        <div class="header-cart flex-col-l p-l-65 p-r-25">
            <div class="header-cart-title flex-w flex-sb-m p-b-8">
                <span class="mtext-103 cl2">
                    سبد خرید
                </span>

                <button type="button" class="fs-35 lh-10 cl2 p-lr-5 pointer hov-cl1 trans-04 js-hide-cart" aria-label="بستن سبد خرید">
                    <i class="zmdi zmdi-close" aria-hidden="true"></i>
                </button>
            </div>

            <div class="header-cart-content flex-w js-pscroll">
                <ul class="header-cart-wrapitem w-full">
                    @forelse ($basketProducts as $basketProduct)
                        @php
                            $basketImage = $basketProduct->thumbnail_url ? \Illuminate\Support\Facades\Storage::disk('product_images')->url($basketProduct->thumbnail_url) : asset('images/product-01.jpg');
                        @endphp
                        <li class="header-cart-item flex-w flex-t m-b-12">
                            <div class="header-cart-item-img">
                                <img src="{{ $basketImage }}" alt="{{ $basketProduct->title }}">
                            </div>

                            <div class="header-cart-item-txt p-t-8">
                                <a href="{{ route('frontend.products.show', $basketProduct->id) }}" class="header-cart-item-name m-b-18 hov-cl1 trans-04">
                                    {{ $basketProduct->title }}
                                </a>

                                <span class="header-cart-item-info">
                                    {{ number_format($basketProduct->price) }} تومان
                                </span>
                            </div>

                            <form action="{{ route('frontend.basket.remove', $basketProduct->id) }}" method="POST" class="header-cart-item-remove p-t-8">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex-c-m w-full fs-22 cl2 hov-cl1 trans-04" aria-label="حذف {{ $basketProduct->title }} از سبد خرید" title="حذف از سبد خرید">
                                    <i class="zmdi zmdi-delete" aria-hidden="true"></i>
                                </button>
                            </form>
                        </li>
                    @empty
                        <li class="stext-102 cl6 p-tb-20">
                            سبد خرید شما خالی است.
                        </li>
                    @endforelse
                </ul>

                <div class="w-full">
                    <div class="header-cart-total w-full p-tb-40">
                        جمع کل: {{ number_format($basketTotal) }} تومان
                    </div>

                    <div class="header-cart-buttons flex-w w-full">
                        <a href="{{ route('frontend.checkout') }}" class="flex-c-m stext-101 cl0 size-107 w-100 bg3 bor2 hov-btn3 p-lr-15 trans-04 m-l-8 m-b-10">
                            مشاهده سبد خرید و پرداخت
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
