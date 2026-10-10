<?php

namespace App\Utilities;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
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

                $fullPath = $path . '/' . $key . '_' . $image->getClientOriginalName();
                self::upload($image, $fullPath, $diskType);
                $imagePaths[$key] = $fullPath;
            }
        } catch (\Throwable $exception) {
            Storage::disk($diskType)->delete(array_values($imagePaths));

            throw $exception;
        }

        return $imagePaths;
    }

    public static function upload(UploadedFile $image, string $path, string $diskType = 'product_images'): void
    {
        $stored = Storage::disk($diskType)->put(
            $path,
            File::get($image->getPathname())
        );

        if (!$stored) {
            throw new RuntimeException("Unable to store the uploaded file at {$path}.");
        }
    }
}
