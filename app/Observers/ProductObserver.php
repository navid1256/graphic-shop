<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductObserver
{
    private const IMAGE_FIELDS = ['thumbnail_url', 'demo_url', 'source_url'];

    public function updated(Product $product): void
    {
        $oldPaths = [];
        $previous = $product->getPrevious();

        foreach (self::IMAGE_FIELDS as $field) {
            if (! $product->wasChanged($field)) {
                continue;
            }

            $oldPath = $previous[$field] ?? null;
            $newPath = $product->getAttribute($field);

            if ($oldPath && $newPath && $oldPath !== $newPath) {
                $oldPaths[] = $oldPath;
            }
        }

        if ($oldPaths === []) {
            return;
        }

        $productId = $product->getKey();
        $connectionName = $product->getConnection()->getName();

        // Capture paths now: another save can change the model before commit.
        $product->getConnection()->afterCommit(static function () use ($oldPaths, $productId, $connectionName): void {
            try {
                $currentPaths = Product::on($connectionName)
                    ->find($productId)?->only(self::IMAGE_FIELDS) ?? [];
                $pathsToDelete = array_values(array_diff($oldPaths, $currentPaths));

                if ($pathsToDelete === []) {
                    return;
                }

                if (! Storage::disk('product_images')->delete($pathsToDelete)) {
                    Log::warning('Could not delete replaced product images.', [
                        'product_id' => $productId,
                        'paths' => $pathsToDelete,
                    ]);
                }
            } catch (Throwable $exception) {
                report($exception);
            }
        });
    }
}
