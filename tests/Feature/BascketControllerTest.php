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

    public function test_removing_a_product_preserves_other_items_and_the_product_record(): void
    {
        $this->freezeTime();
        $first = $this->createProduct('Removed design');
        $second = $this->createProduct('Remaining design');
        $remaining = [$second->id => ['title' => $second->title, 'price' => $second->price]];

        $response = $this->from(route('frontend.products.show', $first->id))
            ->withCookie('basket', json_encode([
                $first->id => ['title' => $first->title, 'price' => $first->price],
                $second->id => $remaining[$second->id],
            ]))
            ->delete(route('frontend.basket.remove', $first->id));

        $response->assertRedirect(route('frontend.products.show', $first->id))
            ->assertSessionHas('success')
            ->assertCookie('basket');
        $cookie = $response->getCookie('basket');
        $this->assertSame($remaining, json_decode($cookie->getValue(), true, flags: JSON_THROW_ON_ERROR));
        $this->assertSame(now()->addMinutes(600)->timestamp, $cookie->getExpiresTime());
        $this->assertDatabaseHas('products', ['id' => $first->id]);
    }

    public function test_removing_the_last_item_leaves_an_empty_basket(): void
    {
        $response = $this->from(route('frontend.products.all'))
            ->withCookie('basket', '{"42":{"title":"Last design","price":10000}}')
            ->delete(route('frontend.basket.remove', 42));

        $response->assertRedirect(route('frontend.products.all'))->assertCookie('basket');
        $this->assertSame([], json_decode($response->getCookie('basket')->getValue(), true));
    }

    #[DataProvider('invalidBasketCookies')]
    public function test_removing_from_an_invalid_basket_is_safe(string $cookie): void
    {
        $response = $this->withCookie('basket', $cookie)
            ->delete(route('frontend.basket.remove', 42));

        $response->assertRedirect()->assertCookie('basket');
        $this->assertSame([], json_decode($response->getCookie('basket')->getValue(), true));
    }

    public function test_removing_an_absent_item_preserves_the_basket(): void
    {
        $basket = [42 => ['title' => 'Existing design', 'price' => 10000]];
        $response = $this->withCookie('basket', json_encode($basket))
            ->delete(route('frontend.basket.remove', 99));

        $response->assertRedirect()->assertCookie('basket');
        $this->assertSame($basket, json_decode($response->getCookie('basket')->getValue(), true));
    }

    public function test_basket_items_cannot_be_removed_using_a_get_request(): void
    {
        $this->get(route('frontend.basket.remove', 42))->assertStatus(405);
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
