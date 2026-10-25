@php
    $shopNumber = static fn ($value) => strtr(number_format($value, 0, '.', '٬'), [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ]);
@endphp
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
                    <a href="{{ route('frontend.products.all') }}" class="logo" aria-label="گرافیک شاپ، صفحه اصلی">
                        <img src="{{ asset('images/icons/Graphic Shop Logo.png') }}" alt="Graphic Shop" width="2172" height="724">
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
                    <div class="wrap-icon-header flex-w flex-r-m shop-header-actions">
                        <button type="button" class="icon-header-item cl2 hov-cl1 trans-04 p-l-22 p-r-11 icon-header-noti js-show-cart" data-notify="{{ $shopNumber($basketCount) }}" aria-label="سبد خرید، {{ $shopNumber($basketCount) }} محصول"
                            aria-controls="header-basket" aria-haspopup="dialog" aria-expanded="false">
                            <i class="zmdi zmdi-shopping-cart" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="icon-header-item cl2 hov-cl1 trans-04 icon-header-noti" data-open-wishlist data-notify="۰"
                            aria-label="علاقه‌مندی‌ها، ۰ محصول" aria-controls="header-wishlist" aria-expanded="false" aria-haspopup="dialog">
                            <i class="zmdi zmdi-favorite-outline" aria-hidden="true"></i>
                        </button>
                    </div>
                </nav>
            </div>
        </div>

        <!-- Header Mobile -->
        <div class="wrap-header-mobile">
            <!-- Logo moblie -->
            <div class="logo-mobile">
                <a href="{{ route('frontend.products.all') }}" aria-label="گرافیک شاپ، صفحه اصلی"><img src="{{ asset('images/icons/Graphic Shop Logo.png') }}" alt="Graphic Shop" width="2172" height="724"></a>
            </div>

            <!-- Icon header -->
            <div class="wrap-icon-header flex-w flex-r-m m-l-15 shop-header-actions">
                <button type="button" class="icon-header-item cl2 hov-cl1 trans-04 p-r-11 p-l-10 icon-header-noti js-show-cart" data-notify="{{ $shopNumber($basketCount) }}" aria-label="سبد خرید، {{ $shopNumber($basketCount) }} محصول"
                    aria-controls="header-basket" aria-haspopup="dialog" aria-expanded="false">
                    <i class="zmdi zmdi-shopping-cart" aria-hidden="true"></i>
                </button>

                <button type="button" class="icon-header-item cl2 hov-cl1 trans-04 icon-header-noti" data-open-wishlist data-notify="۰"
                    aria-label="علاقه‌مندی‌ها، ۰ محصول" aria-controls="header-wishlist" aria-expanded="false" aria-haspopup="dialog">
                    <i class="zmdi zmdi-favorite-outline" aria-hidden="true"></i>
                </button>
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

    </header>

    <!-- Cart -->
    <dialog class="shop-drawer cart-drawer js-panel-cart" id="header-basket" aria-labelledby="basket-title" closedby="any">
            <div class="shop-drawer-heading">
                <h2 id="basket-title">سبد خرید <span class="shop-drawer-count">{{ $shopNumber($basketCount) }}</span></h2>
                <button type="button" class="shop-drawer-icon-button js-hide-cart" aria-label="بستن سبد خرید" autofocus>
                    <i class="zmdi zmdi-close" aria-hidden="true"></i>
                </button>
            </div>

            <div class="shop-drawer-content {{ $basketProducts->isEmpty() ? 'shop-drawer-content-empty' : '' }}">
                @if ($basketProducts->isEmpty())
                    <div class="shop-drawer-empty">
                        <i class="zmdi zmdi-shopping-cart" aria-hidden="true"></i>
                        <h3>سبد خرید شما خالی است</h3>
                        <p>طرح موردعلاقه‌تان را پیدا کنید و به سبد خرید اضافه کنید.</p>
                    </div>
                @else
                <ul class="wishlist-items" aria-label="محصولات سبد خرید">
                    @foreach ($basketProducts as $basketProduct)
                        @php
                            $basketImage = $basketProduct->thumbnail_url ? \Illuminate\Support\Facades\Storage::disk('product_images')->url($basketProduct->thumbnail_url) : asset('images/product-01.jpg');
                        @endphp
                        <li class="wishlist-item">
                            <div class="wishlist-item-image">
                                <img src="{{ $basketImage }}" alt="{{ $basketProduct->title }}" width="72" height="88">
                            </div>

                            <div class="wishlist-item-details">
                                <a href="{{ route('frontend.products.show', $basketProduct->id) }}" class="wishlist-item-title">
                                    {{ $basketProduct->title }}
                                </a>

                                <span class="wishlist-item-price">
                                    {{ $shopNumber($basketProduct->price) }} تومان
                                </span>
                            </div>

                            <form action="{{ route('frontend.basket.remove', $basketProduct->id) }}" method="POST" class="cart-item-remove">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="shop-drawer-icon-button" aria-label="حذف {{ $basketProduct->title }} از سبد خرید" title="حذف از سبد خرید">
                                    <i class="zmdi zmdi-delete" aria-hidden="true"></i>
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                @endif
            </div>

                <div class="shop-drawer-footer">
                    @if ($basketProducts->isNotEmpty())
                    <div class="cart-total">
                        <span>جمع کل</span>
                        <strong>{{ $shopNumber($basketTotal) }} <span>تومان</span></strong>
                    </div>
                    <a href="{{ route('frontend.checkout') }}" class="shop-drawer-primary">ادامهٔ خرید و پرداخت <i class="zmdi zmdi-arrow-left" aria-hidden="true"></i></a>
                    <button type="button" class="shop-drawer-secondary js-hide-cart">ادامهٔ مشاهدهٔ محصولات</button>
                    @else
                    <a href="{{ route('frontend.products.all') }}" class="shop-drawer-primary">مشاهدهٔ محصولات <i class="zmdi zmdi-arrow-left" aria-hidden="true"></i></a>
                    @endif
                </div>
    </dialog>

    <dialog id="header-wishlist" class="shop-drawer wishlist-drawer" aria-labelledby="wishlist-title" aria-describedby="wishlist-description" closedby="any">
        <div class="shop-drawer-heading">
            <h2 id="wishlist-title">علاقه‌مندی‌ها <span class="shop-drawer-count" data-wishlist-count>۰</span></h2>
            <button type="button" class="shop-drawer-icon-button" data-close-wishlist aria-label="بستن علاقه‌مندی‌ها" autofocus>
                <i class="zmdi zmdi-close" aria-hidden="true"></i>
            </button>
        </div>
        <p id="wishlist-description" class="shop-drawer-description">طرح‌های موردعلاقه‌تان را برای خرید بعدی نگه دارید.</p>
        <p class="wishlist-feedback" data-wishlist-feedback role="status" aria-live="polite" aria-atomic="true"></p>
        <div class="shop-drawer-content" data-wishlist-content>
            <div class="shop-drawer-empty" data-wishlist-empty>
                <i class="zmdi zmdi-favorite-outline" aria-hidden="true"></i>
                <p>هنوز محصولی به علاقه‌مندی‌ها اضافه نکرده‌اید.</p>
                <p>روی قلب کنار هر محصول بزنید.</p>
            </div>
            <ul class="wishlist-items" data-wishlist-items aria-label="محصولات موردعلاقه"></ul>
        </div>
        <div class="shop-drawer-footer">
            <p>این فهرست در همین مرورگر ذخیره می‌شود.</p>
            <button type="button" class="shop-drawer-primary" data-close-wishlist>ادامهٔ مشاهدهٔ محصولات</button>
        </div>
    </dialog>
    <p class="wishlist-status" data-wishlist-status role="status" aria-live="polite" aria-atomic="true"></p>
