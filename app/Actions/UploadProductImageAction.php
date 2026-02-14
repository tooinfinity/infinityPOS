<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Exceptions\InvalidImageDriver;
use Spatie\Image\Image;
use Throwable;

final readonly class UploadProductImageAction
{
    /**
     * Process and store product image.
     *
     * @throws Throwable
     */
    public function handle(UploadedFile $file, ?string $existingImage = null): string
    {
        $this->deleteExistingImage($existingImage);

        $filename = $this->generateFilename();

        $processedImage = $this->processImage($file);

        Storage::disk('public')->put($filename, $processedImage);

        return $filename;
    }

    private function deleteExistingImage(?string $existingImage): void
    {
        if ($existingImage !== null && Storage::disk('public')->exists($existingImage)) {
            Storage::disk('public')->delete($existingImage);
        }
    }

    private function generateFilename(): string
    {
        return 'products/'.Str::uuid()->toString().'.jpg';
    }

    /**
     * @throws InvalidImageDriver|Throwable
     */
    private function processImage(UploadedFile $file): string
    {
        $driver = extension_loaded('imagick') ? ImageDriver::Imagick : ImageDriver::Gd;
        $tmpPath = sys_get_temp_dir().'/product_image_'.Str::uuid()->toString().'.jpg';

        Image::useImageDriver($driver)
            ->loadFile($file->getPathname())
            ->width(400)
            ->optimize()
            ->format('jpg')
            ->save($tmpPath);

        return $this->readProcessedImage($tmpPath);
    }

    /**
     * @throws Throwable
     */
    private function readProcessedImage(string $tmpPath): string
    {
        throw_unless(file_exists($tmpPath), RuntimeException::class, 'Failed to read processed image file');

        /** @var string $binaryContent */
        $binaryContent = file_get_contents($tmpPath);
        unlink($tmpPath);

        return $binaryContent;
    }
}
