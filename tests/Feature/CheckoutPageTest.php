<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CheckoutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_shows_current_product_titles_images_and_prices(): void
    {
        $first = $this->createProduct('Current design', 54000, 'products/1/demo.jpg');
        $second = $this->createProduct('Another design', 25000);
        $this->createProduct('Not in basket', 90000);
        $basket = [
            $first->id => ['title' => 'Outdated title', 'price' => 1, 'demo_url' => 'old.jpg'],
            $second->id => [],
        ];

        $response = $this->withCookie('basket', json_encode($basket))
            ->get(route('frontend.checkout'))->assertOk();

        $table = $this->checkoutTable($response);
        $this->assertStringContainsString('Current design', $table);
        $this->assertStringContainsString('Another design', $table);
        $this->assertStringContainsString('54,000 تومان', $table);
        $this->assertStringContainsString('25,000 تومان', $table);
        $this->assertStringContainsString(Storage::disk('product_images')->url($first->demo_url), $table);
        $this->assertStringContainsString(asset('images/product-01.jpg'), $table);
        $this->assertStringNotContainsString('Outdated title', $table);
        $this->assertStringNotContainsString('Not in basket', $table);
        $this->assertStringNotContainsString('هزار تومان', $table);
        $this->assertStringNotContainsString('کارت ویزیت مشاور املاک', $table);
        $response->assertSee('79,000 تومان');
    }

    public function test_empty_checkout_displays_an_empty_message_and_zero_total(): void
    {
        $response = $this->get(route('frontend.checkout'))->assertOk();

        $this->assertStringContainsString('سبد خرید شما خالی است.', $this->checkoutTable($response));
        $response->assertDontSee('کارت ویزیت مشاور املاک')->assertSee('0 تومان');
    }

    #[DataProvider('invalidCookies')]
    public function test_checkout_handles_invalid_cookies_and_missing_products(string $cookie): void
    {
        $response = $this->withCookie('basket', $cookie)
            ->get(route('frontend.checkout'))->assertOk();

        $this->assertStringContainsString('سبد خرید شما خالی است.', $this->checkoutTable($response));
    }

    public static function invalidCookies(): array
    {
        return [
            'malformed' => ['not-json'],
            'null' => ['null'],
            'scalar' => ['42'],
            'deleted product' => ['{"999999":{"title":"Deleted","price":10000,"demo_url":null}}'],
        ];
    }

    public function test_checkout_renders_a_separate_delete_form_for_each_product(): void
    {
        $first = $this->createProduct('First design', 54000);
        $second = $this->createProduct('Second design', 25000);
        $response = $this->withCookie('basket', json_encode([$first->id => [], $second->id => []]))
            ->get(route('frontend.checkout'))->assertOk();

        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new DOMXPath($document);
        $forms = $xpath->query('//table[contains(@class, "table-shopping-cart")]//form');
        $this->assertCount(2, $forms);
        foreach ([$first, $second] as $product) {
            $form = $xpath->query('//table//form[@action="'.route('frontend.basket.remove', $product->id).'"]')->item(0);
            $this->assertNotNull($form);
            $this->assertSame('POST', strtoupper($form->getAttribute('method')));
            $this->assertCount(1, $xpath->query('.//input[@name="_method" and @value="DELETE"]', $form));
            $this->assertCount(1, $xpath->query('.//input[@name="_token"]', $form));
            $button = $xpath->query('.//button[@type="submit"]', $form)->item(0);
            $this->assertNotNull($button);
            $this->assertSame('حذف '.$product->title.' از سبد خرید', $button->getAttribute('aria-label'));
            $this->assertCount(1, $xpath->query('.//i[contains(@class, "zmdi-delete")]', $button));
        }
    }

    public function test_delete_form_returns_to_checkout_with_remaining_items_and_updated_total(): void
    {
        $first = $this->createProduct('Removed design', 54000);
        $second = $this->createProduct('Remaining design', 25000);
        $response = $this->from(route('frontend.checkout'))
            ->withCookie('basket', json_encode([$first->id => [], $second->id => []]))
            ->post(route('frontend.basket.remove', $first->id), ['_method' => 'DELETE']);

        $response->assertRedirect(route('frontend.checkout'))->assertCookie('basket');
        $checkout = $this->withCookie('basket', $response->getCookie('basket')->getValue())
            ->get(route('frontend.checkout'))->assertOk();
        $table = $this->checkoutTable($checkout);
        $this->assertStringNotContainsString('Removed design', $table);
        $this->assertStringContainsString('Remaining design', $table);
        $checkout->assertSee('25,000 تومان')->assertDontSee('54,000 تومان');
    }

    private function checkoutTable(TestResponse $response): string
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $table = (new DOMXPath($document))->query('//table[contains(@class, "table-shopping-cart")]')->item(0);
        $this->assertNotNull($table);

        return $document->saveHTML($table);
    }

    private function createProduct(string $title, int $price, ?string $demo = null): Product
    {
        $category = Category::create(['title' => 'Designs', 'slug' => 'designs']);
        $owner = User::factory()->create([
            'mobile' => fake()->unique()->numerify('09#########'),
            'role' => 'user',
        ]);

        return Product::create([
            'title' => $title,
            'price' => $price,
            'description' => 'Design description',
            'demo_url' => $demo,
            'category_id' => $category->id,
            'owner_id' => $owner->id,
        ]);
    }
}
