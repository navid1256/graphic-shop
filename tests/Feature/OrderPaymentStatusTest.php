<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OrderPaymentStatusTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_pages_display_paid_and_unpaid_statuses(): void
    {
        $this->createOrderAndPayment('paid');
        $this->createOrderAndPayment('unpaid');

        foreach (['admin.orders.all', 'admin.payments.all'] as $route) {
            $this->get(route($route))->assertOk()
                ->assertSee('پرداخت‌شده')->assertSee('پرداخت‌نشده')
                ->assertSee('bg-success')->assertSee('bg-danger');
        }
    }

    public function test_migration_converts_existing_statuses_and_supports_rollback(): void
    {
        [$paidOrder, $paidPayment] = $this->createOrderAndPayment('paid');
        [$unpaidOrder, $unpaidPayment] = $this->createOrderAndPayment('unpaid');
        $migration = require database_path('migrations/2026_10_23_000000_normalize_order_and_payment_statuses.php');

        $migration->down();
        $this->assertSame('paied', $paidOrder->fresh()->status);
        $this->assertSame('unpaied', $unpaidOrder->fresh()->status);
        $this->assertSame('success', $paidPayment->fresh()->status);
        $this->assertSame('failed', $unpaidPayment->fresh()->status);

        $migration->up();
        $this->assertSame('paid', $paidOrder->fresh()->status);
        $this->assertSame('unpaid', $unpaidOrder->fresh()->status);
        $this->assertSame('paid', $paidPayment->fresh()->status);
        $this->assertSame('unpaid', $unpaidPayment->fresh()->status);
        $this->assertDatabaseCount('orders', 2);
        $this->assertDatabaseCount('payments', 2);
        $this->assertSame($paidOrder->id, $paidPayment->fresh()->order_id);
        $this->assertSame(25000, $paidOrder->fresh()->amount);
        $this->assertSame(123456, $paidPayment->fresh()->ref_id);
    }

    private function createOrderAndPayment(string $status): array
    {
        $user = User::factory()->create([
            'mobile' => fake()->unique()->numerify('09#########'),
            'role' => 'user',
        ]);
        $order = Order::create(['amount' => 25000, 'status' => $status, 'user_id' => $user->id]);
        $payment = Payment::create([
            'gateway' => 'zarinpal', 'res_id' => 123, 'ref_id' => 123456,
            'status' => $status, 'order_id' => $order->id,
        ]);

        return [$order, $payment];
    }
}
