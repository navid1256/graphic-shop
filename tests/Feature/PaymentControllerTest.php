<?php

namespace Tests\Feature;

use App\Mail\SendOrderImages;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config([
            'payments.providers.zibal.merchant' => 'test-merchant',
            'payments.providers.zarinpal.merchant_id' => '11111111-1111-1111-1111-111111111111',
            'payments.providers.zarinpal.sandbox' => true,
        ]);
        Storage::fake('product_images');
    }

    public static function providers(): array
    {
        return [['zibal'], ['zarinpal']];
    }

    #[DataProvider('providers')]
    public function test_payment_uses_current_prices_and_verifies_saved_transaction(string $provider): void
    {
        $this->fakeGateway($provider);
        $this->fillBasket();
        $response = $this->post(route('frontend.payment.pay'), $this->customer($provider) + ['amount' => 1]);
        $response->assertRedirect($provider === 'zibal'
            ? 'https://gateway.zibal.ir/start/123456'
            : 'https://sandbox.zarinpal.com/pg/StartPay/A12345678901234567890');

        $payments = session('payments');
        $id = array_key_first($payments);
        $this->assertSame(250000, $payments[$id]['amount_rials']);
        $this->assertSame('pending', $payments[$id]['status']);
        Http::assertSent(fn ($request) => $request['amount'] === 250000);

        // Later price changes must not change the amount sent for verification.
        Product::query()->update(['price' => 90000]);
        $callback = $provider === 'zibal'
            ? ['trackId' => '123456', 'success' => '1']
            : ['Authority' => 'A12345678901234567890', 'Status' => 'OK'];
        $url = route('frontend.payment.callback', ['payment' => $id]).'?'.http_build_query($callback);
        $resultUrl = route('frontend.payment.success', ['payment' => $id]);
        $this->get($url)->assertRedirect($resultUrl)->assertCookieExpired('basket');
        $this->get($resultUrl)->assertOk()->assertSee('987654')->assertSee('25,000');
        $this->assertSame('verified', session('payments.'.$id.'.status'));
        $this->assertSame('987654', session('payments.'.$id.'.reference_id'));
        Http::assertSentCount(2);
        $this->get($url)->assertRedirect($resultUrl);
        Http::assertSentCount(2);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_wrong_transaction_is_rejected_before_gateway_verification(): void
    {
        $this->fakeGateway('zibal');
        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'));
        $id = array_key_first(session('payments'));
        $this->get(route('frontend.payment.callback', ['payment' => $id]).'?trackId=999&success=1')
            ->assertSessionHasErrors('payment');
        Http::assertSentCount(1);
        $this->assertSame('pending', session('payments.'.$id.'.status'));
    }

    public function test_missing_session_cannot_verify_payment(): void
    {
        $this->get('/payment/callback/11111111-1111-4111-8111-111111111111?trackId=123456&success=1')
            ->assertRedirect(route('frontend.payment.failed'))->assertSessionHasErrors('payment');
        Http::assertNothingSent();
    }

    public function test_empty_basket_cannot_start_payment(): void
    {
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'))->assertSessionHasErrors('payment');
        Http::assertNothingSent();
    }

    public function test_checkout_requires_email_for_source_file_delivery(): void
    {
        $customer = ['family' => 'Test Customer', 'mobile' => '09123456789'];

        $this->post(route('frontend.checkout.prepare'), $customer)
            ->assertSessionHasErrors('email');

        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $customer + ['provider' => 'zibal'])
            ->assertSessionHasErrors('email');
        Http::assertNothingSent();
    }

    public function test_successful_purchase_emails_all_source_files_once(): void
    {
        Mail::fake();
        $product = $this->fillBasket();
        $firstSource = '1/source-first.zip';
        Storage::disk('product_images')->put($firstSource, 'first source');
        $product->update(['source_url' => $firstSource]);

        $secondProduct = $product->replicate();
        $secondProduct->title = 'Second Design';
        $secondSource = '1/source-second.zip';
        $secondProduct->source_url = $secondSource;
        $secondProduct->save();
        Storage::disk('product_images')->put($secondSource, 'second source');
        $this->withCookie('basket', json_encode([
            $product->id => [],
            $secondProduct->id => [],
        ]));

        Http::fake([
            'https://gateway.zibal.ir/v1/request' => Http::response(['result' => 100, 'trackId' => 123456]),
            'https://gateway.zibal.ir/v1/verify' => Http::response(['result' => 100, 'amount' => 500000, 'refNumber' => '987654']),
        ]);
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'));
        $id = array_key_first(session('payments'));
        $this->get(route('frontend.payment.callback', ['payment' => $id]).'?trackId=123456&success=1')
            ->assertRedirect(route('frontend.payment.success', ['payment' => $id]));

        $successUrl = route('frontend.payment.success', ['payment' => $id]);
        $this->get($successUrl)->assertOk();
        Mail::assertSent(SendOrderImages::class, function ($mail) use ($firstSource, $secondSource) {
            $attachmentNames = array_map(
                fn ($attachment) => $attachment->as,
                $mail->attachments(),
            );
            $html = $mail->render();

            return $mail->hasTo('test@example.com')
                && str_contains($html, '<html lang="fa" dir="rtl">')
                && str_contains($html, 'فایل منبع محصولات خریداری‌شده')
                && $attachmentNames === [basename($firstSource), basename($secondSource)];
        });

        $this->get($successUrl)->assertOk();
        Mail::assertSentCount(1);
    }

    public function test_unknown_provider_is_rejected(): void
    {
        $this->post(route('frontend.payment.pay'), $this->customer('unknown'))->assertSessionHasErrors('provider');
        Http::assertNothingSent();
    }

    public function test_gateway_error_does_not_store_a_pending_payment(): void
    {
        Http::fake(['*' => Http::response(['result' => 102])]);
        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'))->assertSessionHasErrors('payment');
        $this->assertNull(session('payments'));
    }

    public function test_mismatched_verified_amount_is_not_accepted(): void
    {
        Http::fake([
            'https://gateway.zibal.ir/v1/request' => Http::response(['result' => 100, 'trackId' => 123456]),
            'https://gateway.zibal.ir/v1/verify' => Http::response(['result' => 100, 'amount' => 1, 'refNumber' => '987654']),
        ]);
        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'));
        $id = array_key_first(session('payments'));
        $this->get(route('frontend.payment.callback', ['payment' => $id]).'?trackId=123456&success=1')
            ->assertSessionHasErrors('payment');
        $this->assertSame('pending', session('payments.'.$id.'.status'));
    }

    public static function successfulZibalResponses(): array
    {
        return [
            'first verification without bank reference' => [100],
            'recover previously verified transaction' => [201],
        ];
    }

    #[DataProvider('successfulZibalResponses')]
    public function test_zibal_without_reference_number_and_repeated_verification(int $code): void
    {
        Http::fake([
            'https://gateway.zibal.ir/v1/request' => Http::response(['result' => 100, 'trackId' => 123456]),
            'https://gateway.zibal.ir/v1/verify' => Http::response(['result' => $code, 'amount' => 250000]),
            'https://gateway.zibal.ir/v1/inquiry' => Http::response(['result' => 100, 'status' => 1, 'amount' => 250000]),
        ]);
        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'));
        $id = array_key_first(session('payments'));
        $url = route('frontend.payment.callback', ['payment' => $id]).'?trackId=123456&success=1';

        $resultUrl = route('frontend.payment.success', ['payment' => $id]);
        $this->get($url)->assertRedirect($resultUrl)->assertSessionHasNoErrors();
        $this->assertSame('verified', session('payments.'.$id.'.status'));
        $this->assertNull(session('payments.'.$id.'.reference_id'));
        $this->get($resultUrl)->assertOk()->assertSee('123456');
        $this->get($url)->assertRedirect($resultUrl)->assertSessionHasNoErrors();
        Http::assertSentCount($code === 100 ? 2 : 3);
    }

    public static function invalidInquiries(): array
    {
        return [
            'wrong amount' => [['result' => 100, 'status' => 1, 'amount' => 1]],
            'missing amount' => [['result' => 100, 'status' => 1]],
            'unverified' => [['result' => 100, 'status' => 2, 'amount' => 250000]],
            'failed inquiry' => [['result' => 203]],
        ];
    }

    #[DataProvider('invalidInquiries')]
    public function test_already_verified_requires_confirmed_status_and_matching_amount(array $inquiry): void
    {
        Http::fake([
            'https://gateway.zibal.ir/v1/request' => Http::response(['result' => 100, 'trackId' => 123456]),
            'https://gateway.zibal.ir/v1/verify' => Http::response(['result' => 201]),
            'https://gateway.zibal.ir/v1/inquiry' => Http::response($inquiry),
        ]);
        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'));
        $id = array_key_first(session('payments'));
        $this->get(route('frontend.payment.callback', ['payment' => $id]).'?trackId=123456&success=1')
            ->assertSessionHasErrors('payment');
        $this->assertSame('pending', session('payments.'.$id.'.status'));
    }

    public function test_success_page_cannot_be_opened_without_a_verified_payment(): void
    {
        $id = '11111111-1111-4111-8111-111111111111';
        $url = route('frontend.payment.success', ['payment' => $id]);
        $this->get($url)->assertRedirect(route('frontend.payment.failed'));
        $this->withSession(['payments' => [$id => ['status' => 'pending']]])
            ->get($url)->assertRedirect(route('frontend.payment.failed'));
        Http::assertNothingSent();
    }

    public function test_cancelled_callback_shows_failure_page_without_verifying(): void
    {
        $this->fakeGateway('zibal');
        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'));
        $id = array_key_first(session('payments'));
        $this->get(route('frontend.payment.callback', ['payment' => $id]).'?trackId=123456&success=0')
            ->assertRedirect(route('frontend.payment.failed'))
            ->assertCookieMissing('basket');
        $this->get(route('frontend.payment.failed'))->assertOk()->assertSee('پرداخت لغو شد یا ناموفق بود.');
        Http::assertSentCount(1);
    }

    public function test_malformed_callback_redirects_to_failure_page(): void
    {
        $this->fakeGateway('zibal');
        $this->fillBasket();
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'));
        $id = array_key_first(session('payments'));
        $this->get(route('frontend.payment.callback', ['payment' => $id]).'?trackId[]=123456&success=1')
            ->assertRedirect(route('frontend.payment.failed'))->assertSessionHasErrors('payment');
        Http::assertSentCount(1);
    }

    private function customer(string $provider): array
    {
        return ['family' => 'Test Customer', 'mobile' => '09123456789', 'email' => 'test@example.com', 'provider' => $provider];
    }

    private function fillBasket(): Product
    {
        $category = Category::create(['title' => 'Designs', 'slug' => 'designs']);
        $owner = User::factory()->create(['mobile' => '09111111111', 'role' => 'user']);
        $product = Product::create([
            'title' => 'Design', 'price' => 25000, 'description' => 'Design',
            'category_id' => $category->id, 'owner_id' => $owner->id,
            'source_url' => '1/source.zip',
        ]);
        Storage::disk('product_images')->put('1/source.zip', 'source');
        $this->withCookie('basket', json_encode([$product->id => ['price' => 1]]));

        return $product;
    }

    private function fakeGateway(string $provider): void
    {
        if ($provider === 'zibal') {
            Http::fake([
                'https://gateway.zibal.ir/v1/request' => Http::response(['result' => 100, 'trackId' => 123456]),
                'https://gateway.zibal.ir/v1/verify' => Http::response(['result' => 100, 'amount' => 250000, 'refNumber' => '987654']),
            ]);
        } else {
            Http::fake([
                'https://sandbox.zarinpal.com/pg/v4/payment/request.json' => Http::response(['data' => ['code' => 100, 'authority' => 'A12345678901234567890']]),
                'https://sandbox.zarinpal.com/pg/v4/payment/verify.json' => Http::response(['data' => ['code' => 100, 'ref_id' => '987654']]),
            ]);
        }
    }
}
