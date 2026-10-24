<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendProductPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_listing_links_each_product_to_its_detail_page(): void
    {
        $category = Category::create(['title' => 'Designs', 'slug' => 'designs']);
        $product = $this->createProduct('Dynamic Product', $category);

        $response = $this->get(route('frontend.products.all'));

        $response
            ->assertOk()
            ->assertViewIs('frontend.products.all')
            ->assertSee('Dynamic Product')
            ->assertSee('Designs')
            ->assertViewHas('categories', fn ($categories) => $categories->modelKeys() === [$category->id])
            ->assertSee('مشاهده سریع')
            ->assertSee('js-show-modal1', false)
            ->assertSee('class="wrap-modal1 js-modal1', false)
            ->assertSee('data-product-title="Dynamic Product"', false)
            ->assertSee('data-product-description="Description for Dynamic Product"', false)
            ->assertSee('مشاهده جزئیات محصول');

        $this->assertSame(
            3,
            substr_count(
                $response->getContent(),
                'href="'.route('frontend.products.show', $product->id).'"'
            )
        );
    }

    public function test_product_listing_search_filters_products_by_title(): void
    {
        $this->createProduct('Unique Search Result');
        $this->createProduct('Different Product');

        $this->get(route('frontend.products.all', ['search' => 'Unique']))
            ->assertOk()
            ->assertSee('Unique Search Result')
            ->assertDontSee('Different Product')
            ->assertSee('name="search"', false)
            ->assertSee('value="Unique"', false);
    }

    public function test_filter_panel_links_preserve_other_selected_filters_and_search(): void
    {
        $this->createProduct('Filtered Product');

        $query = [
            'search' => 'Filtered',
            'type' => 'paid',
            'price' => '10-100',
            'sort' => 'popular',
        ];

        $response = $this->get(route('frontend.products.all', $query))
            ->assertOk()
            ->assertSee('name="type" value="paid"', false)
            ->assertSee('name="price" value="10-100"', false)
            ->assertSee('name="sort" value="popular"', false);

        $response
            ->assertSee('href="'.e(route('frontend.products.all', array_merge($query, ['type' => 'free']))).'"', false)
            ->assertSee('href="'.e(route('frontend.products.all', array_merge($query, ['price' => '101-200']))).'"', false)
            ->assertSee('href="'.e(route('frontend.products.all', array_merge($query, ['sort' => 'price_asc']))).'"', false);
    }

    public function test_default_product_sort_returns_all_products_without_reordering(): void
    {
        $firstProduct = $this->createProduct('First Product');
        $secondProduct = $this->createProduct('Second Product');

        $this->get(route('frontend.products.all'))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->modelKeys() === [
                $firstProduct->id,
                $secondProduct->id,
            ]);
    }

    public function test_latest_product_sort_orders_by_created_at_descending(): void
    {
        $olderProduct = $this->createProduct('Older Product');
        $newerProduct = $this->createProduct('Newer Product');
        $olderProduct->forceFill([
            'created_at' => now()->subDay(),
        ])->save();

        $this->get(route('frontend.products.all', ['sort' => 'latest']))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->modelKeys() === [
                $newerProduct->id,
                $olderProduct->id,
            ]);
    }

    public function test_popular_product_sort_orders_all_products_by_paid_sales(): void
    {
        $popularProduct = $this->createProduct('Popular Product');
        $lessPopularProduct = $this->createProduct('Less Popular Product');
        $unpopularProduct = $this->createProduct('Unpopular Product');
        $user = User::factory()->create([
            'mobile' => fake()->unique()->numerify('09#########'),
            'role' => 'user',
        ]);
        $paidOrder = Order::create([
            'amount' => 30000,
            'status' => 'paid',
            'user_id' => $user->id,
        ]);
        $unpaidOrder = Order::create([
            'amount' => 10000,
            'status' => 'unpaid',
            'user_id' => $user->id,
        ]);

        foreach ([$popularProduct, $popularProduct, $lessPopularProduct] as $product) {
            OrderItem::create([
                'price' => $product->price,
                'user_id' => $user->id,
                'order_id' => $paidOrder->id,
                'product_id' => $product->id,
            ]);
        }

        OrderItem::create([
            'price' => $unpopularProduct->price,
            'user_id' => $user->id,
            'order_id' => $unpaidOrder->id,
            'product_id' => $unpopularProduct->id,
        ]);

        $this->get(route('frontend.products.all', ['sort' => 'popular']))
            ->assertOk()
            ->assertViewHas('products', fn ($products) => $products->modelKeys() === [
                $popularProduct->id,
                $lessPopularProduct->id,
                $unpopularProduct->id,
            ]);
    }

    public function test_product_detail_page_shows_the_selected_product_data(): void
    {
        $product = $this->createProduct('Selected Product');
        $product->update([
            'price' => 42000,
            'description' => 'Product detail description',
        ]);

        $this->get(route('frontend.products.show', $product->id))
            ->assertOk()
            ->assertViewIs('frontend.products.show')
            ->assertSee('Selected Product')
            ->assertSee('42,000 تومان')
            ->assertSee('Product detail description');
    }

    public function test_product_detail_recommends_only_other_products_from_the_same_category(): void
    {
        $category = Category::create([
            'title' => 'Business Cards',
            'slug' => 'business-cards',
        ]);
        $currentProduct = $this->createProduct('Current Product', $category);
        $similarProduct = $this->createProduct('Similar Business Card', $category);
        $otherCategory = Category::create([
            'title' => 'Posters',
            'slug' => 'posters',
        ]);
        $unrelatedProduct = $this->createProduct('Unrelated Poster', $otherCategory);

        $this->get(route('frontend.products.show', $currentProduct->id))
            ->assertOk()
            ->assertViewHas('similarProducts', fn ($products) => $products->modelKeys() === [$similarProduct->id])
            ->assertSee('Similar Business Card')
            ->assertSee(route('frontend.products.show', $similarProduct->id), false)
            ->assertDontSee('Unrelated Poster')
            ->assertDontSee(route('frontend.products.show', $unrelatedProduct->id), false);
    }

    private function createProduct(string $title, ?Category $category = null): Product
    {
        $category ??= Category::create(['title' => 'Designs', 'slug' => 'designs']);
        $owner = User::factory()->create([
            'mobile' => fake()->unique()->numerify('09#########'),
            'role' => 'user',
        ]);

        return Product::create([
            'title' => $title,
            'price' => 10000,
            'category_id' => $category->id,
            'description' => 'Description for '.$title,
            'owner_id' => $owner->id,
        ]);
    }
}
