<?php

namespace Tests\Feature;

use App\Models\Category;
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
