<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Exceptions\CouldNotLoadImage;
use Spatie\Image\Image;
use Throwable;

final readonly class UploadBrandLogoAction
{
    /**
     * Process and store brand logo image.
     *
     * @throws Throwable
     */
    public function handle(UploadedFile $file, ?string $existingLogo = null): string
    {
        $this->deleteExistingLogo($existingLogo);

        $filename = $this->generateFilename();

        $processedImage = $this->processImage($file);

        Storage::disk('public')->put($filename, $processedImage);

        return $filename;
    }

    private function deleteExistingLogo(?string $existingLogo): void
    {
        if ($existingLogo !== null && Storage::disk('public')->exists($existingLogo)) {
            Storage::disk('public')->delete($existingLogo);
        }
    }

    private function generateFilename(): string
    {
        return 'brands/'.Str::uuid()->toString().'.webp';
    }

    /**
     * @throws CouldNotLoadImage
     */
    private function processImage(UploadedFile $file): string
    {
        $driver = extension_loaded('imagick') ? ImageDriver::Imagick : ImageDriver::Gd;

        return Image::useImageDriver($driver)
            ->loadFile($file->getPathname())
            ->width(400)
            ->optimize()
            ->format('webp')
            ->base64();
    }
}
