<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ProductImageCleanupTest extends TestCase
{
    use DatabaseMigrations;

    public function test_product_creation_saves_description_as_plain_text(): void
    {
        Storage::fake('product_images');
        $category = Category::create(['title' => 'Designs', 'slug' => 'designs']);
        User::factory()->create([
            'email' => 'admin@yahoo.com',
            'mobile' => '09120000001',
            'role' => 'admin',
        ]);
        $image = static fn (string $name) => UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=')
        );

        $this->post(route('admin.products.store'), [
            'title' => 'Poster',
            'price' => 100,
            'category_id' => $category->id,
            'description' => '<p>Poster description&nbsp;</p>',
            'thumbnail_url' => $image('thumbnail.png'),
            'demo_url' => $image('demo.png'),
            'source_url' => $image('source.png'),
        ])->assertRedirect(route('admin.products.create'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'title' => 'Poster',
            'description' => 'Poster description',
        ]);
    }

    public function test_replacing_one_image_deletes_only_its_old_file(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct();
        $disk->put('1/new-thumbnail.jpg', 'new image');

        $product->update(['thumbnail_url' => '1/new-thumbnail.jpg']);

        $disk->assertMissing('1/old-thumbnail.jpg');
        $disk->assertExists([
            '1/new-thumbnail.jpg',
            '1/old-demo.jpg',
            '1/old-source.jpg',
        ]);
        $this->assertSame('1/new-thumbnail.jpg', $product->fresh()->thumbnail_url);
    }

    public function test_the_edit_request_replaces_only_the_uploaded_image(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct();
        $image = UploadedFile::fake()->createWithContent('new-demo.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='
        ));

        $this->put(route('admin.products.update', $product->id), [
            'title' => $product->title,
            'description' => '<p>Edited description&nbsp;</p>',
            'price' => $product->price,
            'category_id' => $product->category_id,
            'thumbnail_url' => null,
            'demo_url' => $image,
            'source_url' => null,
        ])->assertRedirect(route('admin.products.edit', $product->id))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $product->refresh();
        $this->assertSame('Edited description', $product->description);
        $this->assertNotSame('1/old-demo.jpg', $product->demo_url);
        $this->assertSame('1/old-thumbnail.jpg', $product->thumbnail_url);
        $this->assertSame('1/old-source.jpg', $product->source_url);
        $disk->assertMissing('1/old-demo.jpg');
        $disk->assertExists([$product->thumbnail_url, $product->demo_url, $product->source_url]);
    }

    public function test_updating_details_without_replacing_images_preserves_all_files(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct();

        $product->update([
            'title' => 'Updated title',
            'thumbnail_url' => $product->thumbnail_url,
        ]);

        $disk->assertExists(['1/old-thumbnail.jpg', '1/old-demo.jpg', '1/old-source.jpg']);
    }

    public function test_assigning_an_image_for_the_first_time_preserves_the_new_file(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct(['thumbnail_url' => null]);
        $disk->put('1/new-thumbnail.jpg', 'new image');

        $product->update(['thumbnail_url' => '1/new-thumbnail.jpg']);

        $disk->assertExists(['1/new-thumbnail.jpg', '1/old-demo.jpg', '1/old-source.jpg']);
    }

    public function test_replaced_files_are_deleted_only_after_the_transaction_commits(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct();
        $disk->put('1/new-demo.jpg', 'new demo');
        $disk->put('1/new-source.jpg', 'new source');
        $connection = $product->getConnection();

        $connection->beginTransaction();
        $product->update([
            'demo_url' => '1/new-demo.jpg',
            'source_url' => '1/new-source.jpg',
        ]);

        $disk->assertExists(['1/old-demo.jpg', '1/old-source.jpg']);

        $connection->commit();

        $disk->assertMissing(['1/old-demo.jpg', '1/old-source.jpg']);
        $disk->assertExists(['1/old-thumbnail.jpg', '1/new-demo.jpg', '1/new-source.jpg']);
    }

    public function test_a_rollback_preserves_the_previous_image(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct();
        $disk->put('1/new-thumbnail.jpg', 'new image');
        $connection = $product->getConnection();

        $connection->beginTransaction();
        $product->update(['thumbnail_url' => '1/new-thumbnail.jpg']);
        $connection->rollBack();

        $disk->assertExists('1/old-thumbnail.jpg');
        $this->assertSame('1/old-thumbnail.jpg', $product->fresh()->thumbnail_url);
    }

    public function test_multiple_saves_before_commit_keep_the_final_image(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct();
        $disk->put('1/intermediate-thumbnail.jpg', 'intermediate image');
        $disk->put('1/final-thumbnail.jpg', 'final image');
        $connection = $product->getConnection();

        $connection->beginTransaction();
        $product->update(['thumbnail_url' => '1/intermediate-thumbnail.jpg']);
        $product->update(['thumbnail_url' => '1/final-thumbnail.jpg']);
        $product->update(['title' => 'Updated title']);

        $disk->assertExists(['1/old-thumbnail.jpg', '1/intermediate-thumbnail.jpg']);

        $connection->commit();

        $disk->assertMissing(['1/old-thumbnail.jpg', '1/intermediate-thumbnail.jpg']);
        $disk->assertExists('1/final-thumbnail.jpg');
        $this->assertSame('1/final-thumbnail.jpg', $product->fresh()->thumbnail_url);
    }

    public function test_restoring_the_original_path_before_commit_preserves_the_current_file(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct();
        $disk->put('1/intermediate-thumbnail.jpg', 'intermediate image');
        $connection = $product->getConnection();

        $connection->beginTransaction();
        $product->update(['thumbnail_url' => '1/intermediate-thumbnail.jpg']);
        $product->update(['thumbnail_url' => '1/old-thumbnail.jpg']);
        $connection->commit();

        $disk->assertExists('1/old-thumbnail.jpg');
        $disk->assertMissing('1/intermediate-thumbnail.jpg');
        $this->assertSame('1/old-thumbnail.jpg', $product->fresh()->thumbnail_url);
    }

    public function test_an_old_file_still_used_by_another_image_field_is_preserved(): void
    {
        $disk = Storage::fake('product_images');
        $product = $this->createProduct(['demo_url' => '1/old-thumbnail.jpg']);
        $disk->put('1/new-thumbnail.jpg', 'new image');

        $product->update(['thumbnail_url' => '1/new-thumbnail.jpg']);

        $disk->assertExists(['1/old-thumbnail.jpg', '1/new-thumbnail.jpg']);
    }

    public function test_a_failed_file_deletion_is_logged_without_failing_the_saved_update(): void
    {
        Storage::fake('product_images');
        $product = $this->createProduct();
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('delete')->once()->with(['1/old-thumbnail.jpg'])->andReturn(false);
        Storage::shouldReceive('disk')->with('product_images')->andReturn($disk);
        Log::shouldReceive('warning')->once()->with('Could not delete replaced product images.', [
            'product_id' => $product->id,
            'paths' => ['1/old-thumbnail.jpg'],
        ]);

        $product->update(['thumbnail_url' => '1/new-thumbnail.jpg']);

        $this->assertSame('1/new-thumbnail.jpg', $product->fresh()->thumbnail_url);
    }

    public function test_a_file_deletion_exception_is_reported_without_failing_the_saved_update(): void
    {
        Storage::fake('product_images');
        $product = $this->createProduct();
        $exception = new RuntimeException('Storage unavailable');
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('delete')->once()->with(['1/old-thumbnail.jpg'])->andThrow($exception);
        Storage::shouldReceive('disk')->with('product_images')->andReturn($disk);
        $handler = Mockery::mock(ExceptionHandler::class);
        $handler->shouldReceive('report')->once()->with($exception);
        $this->app->instance(ExceptionHandler::class, $handler);

        $product->update(['thumbnail_url' => '1/new-thumbnail.jpg']);

        $this->assertSame('1/new-thumbnail.jpg', $product->fresh()->thumbnail_url);
    }

    private function createProduct(array $attributes = []): Product
    {
        $category = Category::create(['title' => 'Images', 'slug' => 'images']);
        $owner = User::factory()->create(['mobile' => '09120000000', 'role' => 'admin']);

        $product = Product::create(array_merge([
            'title' => 'Test product',
            'description' => 'Image cleanup test',
            'price' => 100,
            'category_id' => $category->id,
            'owner_id' => $owner->id,
            'thumbnail_url' => '1/old-thumbnail.jpg',
            'demo_url' => '1/old-demo.jpg',
            'source_url' => '1/old-source.jpg',
        ], $attributes));

        foreach (['thumbnail_url', 'demo_url', 'source_url'] as $field) {
            if ($product->{$field}) {
                Storage::disk('product_images')->put($product->{$field}, 'old image');
            }
        }

        return $product;
    }
}
