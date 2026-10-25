# Graphic Shop | فروشگاه محصولات گرافیکی

فروشگاه فارسی محصولات گرافیکی با **Laravel 12** و **Blade**؛ شامل مرور و جست‌وجوی محصولات، سبد خرید، انتخاب درگاه زیبال یا زرین‌پال، تأیید پرداخت و ارسال فایل منبع محصولات به ایمیل خریدار.

این پروژه آموزشی و در حال توسعه است. بخش [وضعیت فعلی](#وضعیت-فعلی) محدودیت‌های پیاده‌سازی را توضیح می‌دهد.

## امکانات

- نمایش جزئیات محصول، دسته‌بندی، فروشنده و محصولات مشابه.
- جست‌وجو بر اساس عنوان، فیلتر محصولات رایگان و پولی و بازهٔ قیمت.
- مرتب‌سازی بر اساس تاریخ، قیمت و تعداد فروش سفارش‌های پرداخت‌شده.
- افزودن و حذف محصول از سبد خرید با کوکی؛ نمایش تعداد و مجموع سبد در هدر.
- checkout در دو مرحله: اطلاعات خریدار و انتخاب درگاه.
- محاسبهٔ مبلغ از قیمت فعلی دیتابیس، تبدیل تومان به ریال و بررسی callback درگاه.
- صفحه‌های مجزای نتیجهٔ موفق و ناموفق پرداخت.
- ارسال فایل‌های `source_url` به‌صورت پیوست ایمیل بعد از تأیید پرداخت.
- پنل مدیریت محصولات، دسته‌بندی‌ها و کاربران، و لیست سفارش‌ها و پرداخت‌ها.
- آپلود تصاویر، نمایش thumbnail و دریافت فایل دمو و منبع از پنل مدیریت.
- تست‌های Feature برای سبد خرید، checkout، درگاه‌ها، ایمیل، تصاویر و وضعیت سفارش‌ها.

## فناوری‌ها و پیش‌نیازها

| بخش | ابزار |
| --- | --- |
| Backend | PHP 8.2 یا بالاتر، Laravel 12، Eloquent |
| رابط کاربری | Blade، Bootstrap RTL، jQuery |
| آیکن‌ها | Font Awesome و Material Design Iconic Font |
| دیتابیس محلی | MySQL یا MariaDB؛ مناسب برای اجرا با XAMPP |
| تست | PHPUnit 11 و SQLite حافظه‌ای |
| ابزارهای توسعهٔ frontend | Vite 7 و Tailwind CSS 4 |
| مدیریت وابستگی | Composer 2 و npm |

برای ابزارهای Vite از Node.js سازگار، مانند **22.12 یا بالاتر**، استفاده کنید. [راهنمای Vite](https://vite.dev/guide/)

PHP باید افزونه‌های موردنیاز Laravel، درایور دیتابیس انتخابی و برای اجرای تست‌ها `pdo_sqlite` را داشته باشد. دستور `php -v` باید PHP موردنظر شما را نشان دهد؛ در ویندوز مسیر PHP مربوط به XAMPP را در `PATH` بررسی کنید.

## نصب و اجرای محلی

دستورها را در ریشهٔ پروژه اجرا کنید. نمونه‌های این بخش برای **PowerShell در ویندوز** نوشته شده‌اند.

### ۱. دریافت پروژه و وابستگی‌های PHP

```powershell
git clone https://github.com/navid1256/graphic-shop.git
cd graphic-shop
composer install
Copy-Item .env.example .env
php artisan key:generate
```

اگر پروژه را از قبل دارید، فایل `.env` موجود را جایگزین نکنید.

### ۲. تنظیم دیتابیس و آدرس برنامه

MySQL را در XAMPP روشن کنید و در phpMyAdmin یک دیتابیس خالی به نام `graphic_shop` با charsetِ `utf8mb4` بسازید. سپس `.env` را تنظیم کنید:

```dotenv
APP_NAME="Graphic Shop"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=graphic_shop
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
```

نام کاربری و رمز دیتابیس را با محیط خودتان هماهنگ کنید. فایل `.env.example` در حال حاضر SQLite را به‌عنوان اتصال پیش‌فرض دارد؛ برای اجرای MySQL، مقدار `DB_CONNECTION` را تغییر دهید.

### ۳. ساخت جدول‌ها و لینک storage

```powershell
php artisan config:clear
php artisan migrate
php artisan storage:link
```

برای دیتابیس دارای اطلاعات، ابتدا بکاپ بگیرید. migration یکسان‌سازی وضعیت‌ها، مقدارهای قدیمی سفارش و پرداخت را به `paid` و `unpaid` تبدیل می‌کند. `migrate:fresh` برای راه‌اندازی عادی لازم نیست و جدول‌های موجود را حذف می‌کند.

### ۴. فایل‌های قالب

قالب اصلی، CSS و JavaScript را مستقیماً از پوشهٔ `public` بارگذاری می‌کند. کتابخانه‌های frontend در `public/vendor` به‌دلیل تنظیم `.gitignore` داخل مخزن ثبت نشده‌اند؛ در کلون تازه باید این پوشه را از قالب اصلی تهیه کنید.

در ساختار محلی این پروژه، اگر پوشهٔ قالب `graphic-shop-ui` کنار پروژه قرار دارد:

```powershell
Copy-Item -Path ..\graphic-shop-ui\vendor -Destination .\public\vendor -Recurse
```

این دستور برای زمانی است که `public/vendor` هنوز وجود ندارد. پس از کپی، مسیرهایی مثل `public/vendor/bootstrap/css/bootstrap.min.css` و `public/vendor/jquery/jquery-3.2.1.min.js` باید موجود باشند. این پوشه با `vendor` ریشهٔ پروژه که Composer می‌سازد متفاوت است.

برای کار با فایل‌های `resources/css` و `resources/js` نیز می‌توانید ابزارهای Vite را نصب و اجرا کنید:

```powershell
npm install
npm run dev
```

قالب‌های فعلی فروشگاه و ادمین به `@vite` متصل نیستند؛ اجرای Vite جایگزین فایل‌های قالب در `public/vendor` نمی‌شود.

### ۵. اجرای برنامه و ساخت اولین محصول

```powershell
php artisan serve
```

فروشگاه در `http://127.0.0.1:8000` و پنل مدیریت در `http://127.0.0.1:8000/admin` در دسترس است. در اجرای Apache، DocumentRoot باید پوشهٔ `public` باشد و `APP_URL` با آدرس واقعی سایت مطابقت داشته باشد.

برای ساخت اولین محصول:

1. در `/admin/users/create` یک کاربر با نقش `admin`، شمارهٔ موبایل یکتا و ایمیل **`admin@yahoo.com`** بسازید. کنترلر ثبت محصول فعلاً مالک محصول را با همین ایمیل پیدا می‌کند.
2. در `/admin/categories/create` یک دسته‌بندی اضافه کنید.
3. در `/admin/product/create` محصول، قیمت و سه تصویر thumbnail، دمو و منبع را ثبت کنید.

Seeder فعلی ادمین آماده نمی‌سازد؛ مسیر بالا را برای آماده‌سازی دادهٔ اولیه استفاده کنید. فیلدهای `demo_url` و `source_url` در نسخهٔ فعلی آپلود **تصویر** هستند و فایل‌هایی مانند ZIP را نمی‌پذیرند.

## تنظیم درگاه‌های پرداخت

تنظیمات در [config/payments.php](config/payments.php) قرار دارند. متغیرهای زیر را در `.env` اضافه کنید.

### زیبال

```dotenv
PAYMENT_GATEWAY=zibal
ZIBAL_MERCHANT=zibal
```

مقدار `zibal` برای merchant آزمایشی استفاده می‌شود. برای پرداخت عملیاتی، merchant مربوط به پذیرندهٔ خودتان را وارد کنید.

### زرین‌پال

```dotenv
ZARINPAL_MERCHANT_ID=your-merchant-uuid
ZARINPAL_SANDBOX=true
ZARINPAL_AUTO_VERIFY=false
```

`your-merchant-uuid` را با شناسهٔ معتبر خودتان جایگزین کنید؛ provider فعلی قالب UUID را اعتبارسنجی می‌کند. برای انتخاب پیش‌فرض زرین‌پال، `PAYMENT_GATEWAY=zarinpal` قرار دهید. در checkout امکان انتخاب هر دو درگاه وجود دارد.

بعد از تغییر تنظیمات:

```powershell
php artisan config:clear
```

**واحد مبلغ:** قیمت محصول در برنامه تومان است؛ درخواست پرداخت با ضرب مبلغ در ۱۰، به **ریال** ارسال می‌شود. callback باید به همان میزبان و مرورگری برگردد که پرداخت را شروع کرده است، چون اطلاعات تطبیق پرداخت در session نگهداری می‌شود. دسترس‌پذیری و پذیرش آدرس callback را مطابق تنظیمات درگاه بررسی کنید.

### جریان خرید

```text
صفحهٔ محصول → افزودن به سبد → اطلاعات خریدار → انتخاب درگاه
    → pay → انتقال به درگاه → callback → verify
        ├── تأییدشده → callback-s → ارسال فایل‌های منبع با ایمیل
        └── ناموفق یا تأییدنشده → callback-f
```

- شناسهٔ برگشتی درگاه با تراکنش ذخیره‌شده در session تطبیق داده می‌شود.
- زیبال هنگام پاسخ «قبلاً تأیید شده»، با استعلام وضعیت و تطبیق مبلغ بررسی می‌شود.
- در نبود شمارهٔ مرجع، شناسهٔ تراکنش در صفحهٔ نتیجه نمایش داده می‌شود.
- سبد خرید پس از تأیید موفق پاک می‌شود.

## تنظیم ایمیل

در توسعهٔ محلی، `MAIL_MAILER=log` پیام را در لاگ ثبت می‌کند و ایمیل واقعی نمی‌فرستد. برای ارسال واقعی، اطلاعات SMTP خودتان را وارد کنید:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_FROM_ADDRESS=shop@example.com
MAIL_FROM_NAME="Graphic Shop"
```

مقادیر نمونه را با تنظیمات سرویس ایمیل خودتان جایگزین کنید. `.env.example` نمونهٔ Mailtrap دارد؛ token و دامنهٔ ارسال باید متعلق به حساب خودتان باشند.

کلاس [SendOrderImages](app/Mail/SendOrderImages.php) فایل‌های منبع محصولات خریداری‌شده را از دیسک `product_images` پیوست می‌کند. ارسال در صفحهٔ موفقیت و با `Mail::send()` انجام می‌شود؛ در وضعیت فعلی queue worker برای این ارسال لازم نیست. علامت ارسال در همان session نگهداری می‌شود تا بازکردن مجدد صفحه پس از ارسال موفق، ایمیل را دوباره ارسال نکند. این سازوکار تضمین ارسال دقیقاً یک‌باره در برابر درخواست‌های هم‌زمان نیست.

## فایل‌ها و ساختار پروژه

تصاویر روی دیسک `product_images` ذخیره می‌شوند:

| مورد | مسیر |
| --- | --- |
| فایل‌های اصلی تصاویر | `storage/app/public/products/images` |
| لینک عمومی storage | `public/storage` |
| CSS، JavaScript، فونت و قالب | `public/` |

```text
app/
├── Http/Controllers/
│   ├── Admin/                  # مدیریت محصولات، کاربران، دسته‌بندی، سفارش و پرداخت
│   ├── Home/                   # فروشگاه، سبد خرید و checkout
│   ├── Filters/                # مرتب‌سازی محصولات
│   └── PaymentController.php   # شروع پرداخت، callback و نتیجه
├── Mail/SendOrderImages.php    # ایمیل فایل‌های خرید
├── Models/                    # مدل‌های فروشگاه
├── Observers/                 # چرخهٔ عمر محصول و پاک‌سازی تصاویر
├── Services/
│   ├── PaymentService.php
│   └── Payment/
│       ├── Contracts/         # قراردادهای درخواست، provider و HTTP client
│       ├── Providers/         # ZibalProvider و ZarinpalProvider
│       ├── Requests/          # PaymentRequest
│       ├── Results/           # نتیجهٔ شروع و تأیید پرداخت
│       ├── Exceptions/
│       └── Support/
└── Utilities/                 # آپلود فایل و تبدیل HTML به متن
config/payments.php
database/migrations/
resources/views/
├── admin/
├── frontend/
└── mail/
routes/web.php
tests/Feature/
```

## مسیرهای اصلی

| مسیر | کاربرد |
| --- | --- |
| `/` | لیست محصولات |
| `/product/{product_id}` | جزئیات محصول |
| `/checkout` | سبد خرید و اطلاعات خریدار |
| `/checkout/select` | انتخاب درگاه |
| `POST /payment` | شروع پرداخت |
| `/payment/callback/{payment}` | بازگشت از درگاه و تأیید پرداخت |
| `/payment/callback-s/{payment}` | نتیجهٔ تأییدشده و ارسال فایل‌ها |
| `/payment/callback-f` | نتیجهٔ ناموفق یا تأییدنشده |
| `/admin/product` | مدیریت محصولات |
| `/admin/categories` | مدیریت دسته‌بندی‌ها |
| `/admin/users` | مدیریت کاربران |
| `/admin/orders` | لیست سفارش‌ها |
| `/admin/payments` | لیست پرداخت‌ها |

## دستورهای کاربردی و تست

| دستور | کاربرد |
| --- | --- |
| `php artisan serve` | اجرای سرور محلی Laravel |
| `php artisan route:list` | مشاهدهٔ مسیرها و نام روت‌ها |
| `php artisan migrate:status` | مشاهدهٔ وضعیت migrationها |
| `php artisan storage:link` | ساخت لینک عمومی storage |
| `php artisan config:clear` | پاک‌کردن تنظیمات cacheشده |
| `composer test` | پاک‌کردن cache تنظیمات و اجرای تست‌ها |
| `php artisan test --filter=PaymentControllerTest` | تست جریان پرداخت و ارسال فایل |
| `php artisan test --filter=OrderPaymentStatusTest` | تست تبدیل و نمایش وضعیت‌ها |
| `vendor/bin/pint --test` | بررسی قالب‌بندی PHP |
| `npm run build` | ساخت assetهای تعریف‌شده در Vite |
| `composer run dev` | اجرای هم‌زمان سرور، queue listener، لاگ و Vite |

در PowerShell برای Pint می‌توانید از `php vendor/laravel/pint/builds/pint --test` استفاده کنید.

طبق [phpunit.xml](phpunit.xml)، تست‌ها از SQLite حافظه‌ای، session و cache از نوع array و mailer آزمایشی استفاده می‌کنند. تست‌های پرداخت پاسخ‌های HTTP درگاه را شبیه‌سازی می‌کنند؛ عبور آن‌ها به معنی آزمایش پرداخت بانکی واقعی یا تحویل ایمیل واقعی نیست. برای اجرای عادی تست‌ها نیازی به merchant عملیاتی یا اطلاعات SMTP واقعی نیست.

## وضعیت فعلی

- checkout فعلی، اطلاعات پرداخت را در session نگه می‌دارد؛ هنوز رکوردهای `orders`، `order_items` و `payments` را پس از خرید ایجاد نمی‌کند. صفحه‌های ادمین این جدول‌ها، داده‌های موجود را نمایش می‌دهند.
- وضعیت دیتابیسی سفارش و پرداخت `paid / unpaid` است. وضعیت‌های `pending` و `verified` در session، وضعیت‌های داخلی جریان درگاه‌اند.
- شناسهٔ UUID درخواست پرداخت، شمارهٔ مرجع بانکی و `orders.ref_code` کاربردهای جدا دارند؛ در جریان فعلی `orders.ref_code` مقداردهی نمی‌شود.
- گروپ `/admin` فعلاً middleware ورود و بررسی نقش ادمین ندارد. اجرای عمومی پنل نیازمند تکمیل کنترل دسترسی است.
- دیسک فایل‌های منبع فعلاً عمومی است؛ تحویل فایل خصوصی و کنترل دسترسی دانلود هنوز پیاده‌سازی نشده است.
- اطلاعات SMTP و فایل‌های منبع باید پیش از تست ارسال واقعی آماده باشند. وجود جدول‌های `jobs`، `job_batches` و `failed_jobs` به معنی استفادهٔ فعلی ارسال ایمیل از صف نیست.

## مشارکت

تغییرات هر قابلیت را در برنچ جدا انجام دهید، سبک `Route::prefix(...)->group(...)` پروژه را حفظ کنید و تست‌های مرتبط را اجرا کنید. در توضیح تغییر، قابلیت پیاده‌شده، روش بررسی و migrationهای موردنیاز را بنویسید. فایل `.env`، کلیدهای درگاه و اطلاعات SMTP را در گیت ثبت نکنید.
