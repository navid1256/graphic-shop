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

class FrontendBasketHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_basket_shows_zero_badges_total_and_an_empty_message(): void
    {
        $response = $this->get(route('frontend.products.all'))->assertOk();

        $this->assertBasketCount($response, 0);
        $response->assertSee('سبد خرید شما خالی است.')
            ->assertSee('جمع کل: 0 تومان')
            ->assertDontSee('checkout.php', false);
    }

    public function test_basket_shows_current_products_prices_links_and_thumbnail_images(): void
    {
        $first = $this->createProduct('First design', 10000, 'first.jpg');
        $second = $this->createProduct('Second design', 25000);
        $this->createProduct('Not in basket', 90000);
        $cookie = json_encode([
            $first->id => ['title' => 'Old title', 'price' => 1, 'demo_url' => 'demo.zip'],
            $second->id => ['title' => 'Another old title', 'price' => 2],
        ], JSON_THROW_ON_ERROR);

        $response = $this->withCookie('basket', $cookie)
            ->get(route('frontend.products.all'))->assertOk();

        $this->assertBasketCount($response, 2);
        $panel = $this->basketPanel($response);
        $this->assertStringContainsString('First design', $panel);
        $this->assertStringContainsString('Second design', $panel);
        $this->assertStringNotContainsString('Not in basket', $panel);
        $this->assertStringNotContainsString('Old title', $panel);
        $this->assertStringNotContainsString('demo.zip', $panel);
        $this->assertStringContainsString('10,000 تومان', $panel);
        $this->assertStringContainsString('25,000 تومان', $panel);
        $this->assertStringContainsString('جمع کل: 35,000 تومان', $panel);
        $this->assertStringContainsString(route('frontend.products.show', $first->id), $panel);
        $this->assertStringContainsString(Storage::disk('product_images')->url('first.jpg'), $panel);
        $this->assertStringContainsString(asset('images/product-01.jpg'), $panel);
    }

    #[DataProvider('invalidCookies')]
    public function test_invalid_basket_cookie_renders_as_an_empty_basket(string $cookie): void
    {
        $response = $this->withCookie('basket', $cookie)
            ->get(route('frontend.products.all'))->assertOk();

        $this->assertBasketCount($response, 0);
        $response->assertSee('سبد خرید شما خالی است.');
    }

    public static function invalidCookies(): array
    {
        return [
            'malformed' => ['not-json'],
            'null' => ['null'],
            'scalar' => ['42'],
            'missing product' => ['{"999999":{"title":"Deleted design","price":10000}}'],
        ];
    }

    public function test_product_titles_are_escaped_in_the_basket(): void
    {
        $title = '<script>alert("basket")</script>';
        $product = $this->createProduct($title, 10000);

        $response = $this->withCookie('basket', json_encode([$product->id => []]))
            ->get(route('frontend.products.all'))->assertOk();

        $panel = $this->basketPanel($response);
        $this->assertStringNotContainsString('<script>', $panel);
        $this->assertStringContainsString('&lt;script&gt;', $panel);
    }

    public function test_each_basket_item_has_its_own_delete_form_and_accessible_icon(): void
    {
        $first = $this->createProduct('First design', 10000);
        $second = $this->createProduct('Second design', 25000);
        $response = $this->withCookie('basket', json_encode([$first->id => [], $second->id => []]))
            ->get(route('frontend.products.all'))->assertOk();

        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new DOMXPath($document);
        $forms = $xpath->query('//div[@id="header-basket"]//form');
        $this->assertCount(2, $forms);

        foreach ([$first, $second] as $product) {
            $form = $xpath->query('//form[@action="'.route('frontend.basket.remove', $product->id).'"]')->item(0);
            $this->assertNotNull($form);
            $this->assertSame('POST', strtoupper($form->getAttribute('method')));
            $this->assertCount(1, $xpath->query('.//input[@name="_method" and @value="DELETE"]', $form));
            $this->assertCount(1, $xpath->query('.//input[@name="_token"]', $form));
            $button = $xpath->query('.//button[@type="submit"]', $form)->item(0);
            $this->assertNotNull($button);
            $this->assertSame('حذف '.$product->title.' از سبد خرید', $button->getAttribute('aria-label'));
            $this->assertCount(1, $xpath->query('.//i[contains(@class, "zmdi-delete") and @aria-hidden="true"]', $button));
        }
    }

    private function assertBasketCount(TestResponse $response, int $expected): void
    {
        $document = new DOMDocument;
        @$document->loadHTML($response->getContent());
        $badges = (new DOMXPath($document))->query('//*[contains(@class, "js-show-cart")]/@data-notify');
        $this->assertCount(2, $badges);
        foreach ($badges as $badge) {
            $this->assertSame((string) $expected, $badge->nodeValue);
        }
    }

    private function basketPanel(TestResponse $response): string
    {
        $document = new DOMDocument;
        @$document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $panel = (new DOMXPath($document))->query('//div[contains(@class, "js-panel-cart")]')->item(0);
        $this->assertNotNull($panel);

        return $document->saveHTML($panel);
    }

    private function createProduct(string $title, int $price, ?string $thumbnail = null): Product
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
            'thumbnail_url' => $thumbnail,
            'category_id' => $category->id,
            'owner_id' => $owner->id,
        ]);
    }
}
