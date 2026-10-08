<?php

namespace App\Utilities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ImageUploader
{
    /**
     * @param array<string, UploadedFile> $images
     * @return array<string, string>
     */
    public static function uploadMany(array $images, string $path, string $diskType = 'product_images'): array
    {
        $imagePaths = [];

        try {
            foreach ($images as $key => $image) {
                if (!$image instanceof UploadedFile) {
                    throw new RuntimeException("The uploaded file for {$key} is invalid.");
                }

                $fileName = $key . '_' . $image->getClientOriginalName();
                $storedPath = Storage::disk($diskType)->putFileAs($path, $image, $fileName);

                if ($storedPath === false) {
                    throw new RuntimeException("Unable to store the uploaded file for {$key}.");
                }

                $imagePaths[$key] = $storedPath;
            }
        } catch (\Throwable $exception) {
            Storage::disk($diskType)->delete(array_values($imagePaths));

            throw $exception;
        }

        return $imagePaths;
    }
}
