<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
        $this->get($url)->assertRedirect(route('frontend.checkout'))->assertSessionHas('success');
        $this->assertSame('verified', session('payments.'.$id.'.status'));
        $this->assertSame('987654', session('payments.'.$id.'.reference_id'));
        Http::assertSentCount(2);
        $this->get($url)->assertSessionHas('success');
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
            ->assertRedirect(route('frontend.checkout'))->assertSessionHasErrors('payment');
        Http::assertNothingSent();
    }

    public function test_empty_basket_cannot_start_payment(): void
    {
        $this->post(route('frontend.payment.pay'), $this->customer('zibal'))->assertSessionHasErrors('payment');
        Http::assertNothingSent();
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

    private function customer(string $provider): array
    {
        return ['family' => 'Test Customer', 'mobile' => '09123456789', 'email' => 'test@example.com', 'provider' => $provider];
    }

    private function fillBasket(): void
    {
        $category = Category::create(['title' => 'Designs', 'slug' => 'designs']);
        $owner = User::factory()->create(['mobile' => '09111111111', 'role' => 'user']);
        $product = Product::create([
            'title' => 'Design', 'price' => 25000, 'description' => 'Design',
            'category_id' => $category->id, 'owner_id' => $owner->id,
        ]);
        $this->withCookie('basket', json_encode([$product->id => ['price' => 1]]));
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
