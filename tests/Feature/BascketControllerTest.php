<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BascketControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_adds_a_product_to_an_encrypted_cookie_for_600_minutes(): void
    {
        $this->freezeTime();
        $product = $this->createProduct('طرح گرافیکی');

        $response = $this->post(route('frontend.basket.add', $product->id), [
            'title' => 'Changed title',
            'price' => 1,
        ]);

        $response->assertRedirect(route('frontend.products.show', $product->id))
            ->assertSessionHas('success')
            ->assertCookie('basket');

        $cookie = $response->getCookie('basket');
        $this->assertSame([
            $product->id => [
                'title' => $product->title,
                'price' => $product->price,
                'demo_url' => $product->demo_url,
            ],
        ], json_decode($cookie->getValue(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(now()->addMinutes(600)->timestamp, $cookie->getExpiresTime());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertNotSame($cookie->getValue(), $response->getCookie('basket', false)->getValue());
    }

    public function test_adding_another_product_preserves_existing_items(): void
    {
        $first = $this->createProduct('First design');
        $second = $this->createProduct('Second design');
        $firstResponse = $this->post(route('frontend.basket.add', $first->id));
        $firstResponse->assertCookie('basket');

        $response = $this->withCookie('basket', $firstResponse->getCookie('basket')->getValue())
            ->post(route('frontend.basket.add', $second->id));

        $response->assertCookie('basket');
        $basket = json_decode($response->getCookie('basket')->getValue(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(2, $basket);
        $this->assertSame($first->title, $basket[$first->id]['title']);
        $this->assertSame($second->title, $basket[$second->id]['title']);
    }

    public function test_adding_the_same_product_does_not_duplicate_it(): void
    {
        $product = $this->createProduct('Single design');
        $firstResponse = $this->post(route('frontend.basket.add', $product->id));
        $firstResponse->assertCookie('basket');

        $response = $this->withCookie('basket', $firstResponse->getCookie('basket')->getValue())
            ->post(route('frontend.basket.add', $product->id));

        $response->assertCookie('basket');
        $basket = json_decode($response->getCookie('basket')->getValue(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $basket);
        $this->assertArrayHasKey($product->id, $basket);
    }

    #[DataProvider('invalidBasketCookies')]
    public function test_recovers_from_a_cookie_that_does_not_contain_a_json_array(string $cookie): void
    {
        $product = $this->createProduct('Recovered design');

        $response = $this->withCookie('basket', $cookie)
            ->post(route('frontend.basket.add', $product->id));

        $response->assertRedirect(route('frontend.products.show', $product->id))
            ->assertCookie('basket');
        $basket = json_decode($response->getCookie('basket')->getValue(), true, flags: JSON_THROW_ON_ERROR);
        $this->assertCount(1, $basket);
        $this->assertArrayHasKey($product->id, $basket);
    }

    public static function invalidBasketCookies(): array
    {
        return [
            'malformed JSON' => ['not-json'],
            'null' => ['null'],
            'number' => ['42'],
            'string' => ['"invalid"'],
        ];
    }

    public function test_a_missing_product_returns_404_without_setting_a_basket_cookie(): void
    {
        $this->post(route('frontend.basket.add', 999999))
            ->assertNotFound()
            ->assertCookieMissing('basket');
    }

    private function createProduct(string $title): Product
    {
        $category = Category::create(['title' => 'Designs', 'slug' => 'designs']);
        $owner = User::factory()->create([
            'mobile' => fake()->unique()->numerify('09#########'),
            'role' => 'user',
        ]);

        return Product::create([
            'title' => $title,
            'price' => 10000,
            'description' => 'Design description',
            'demo_url' => 'demo.zip',
            'category_id' => $category->id,
            'owner_id' => $owner->id,
        ]);
    }
}
