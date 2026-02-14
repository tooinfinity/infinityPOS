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
     * @throws InvalidImageDriver|Throwable
     */
    private function processImage(UploadedFile $file): string
    {
        $driver = extension_loaded('imagick') ? ImageDriver::Imagick : ImageDriver::Gd;
        $tmpPath = sys_get_temp_dir().'/brand_logo_'.Str::uuid()->toString().'.webp';

        Image::useImageDriver($driver)
            ->loadFile($file->getPathname())
            ->width(400)
            ->optimize()
            ->format('webp')
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
