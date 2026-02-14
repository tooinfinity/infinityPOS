<?php

declare(strict_types=1);

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Exceptions\InvalidImageDriver;
use Spatie\Image\Image;
use Throwable;

final readonly class UploadImageAction
{
    private const array ALLOWED_EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

    private const int MAX_SIZE_BYTES = 2097152;

    private const int DEFAULT_MAX_WIDTH = 400;

    /**
     * Process and store image.
     *
     * @throws InvalidArgumentException|Throwable
     */
    public function handle(UploadedFile $file, string $directory, ?string $existingImage = null, ?int $maxWidth = null): string
    {
        $this->validateFile($file);

        $this->deleteExistingImage($existingImage);

        $filename = $this->generateFilename($directory);
        $targetWidth = $maxWidth ?? self::DEFAULT_MAX_WIDTH;

        $tmpPath = $this->processImage($file, $targetWidth);

        try {
            $stream = fopen($tmpPath, 'rb');
            throw_if($stream === false, RuntimeException::class, 'Failed to open processed image file');

            Storage::disk('public')->put($filename, $stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        } finally {
            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
        }

        return $filename;
    }

    /**
     * @throws Throwable
     */
    private function validateFile(UploadedFile $file): void
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException(
                'Invalid image format. Allowed formats: '.implode(', ', self::ALLOWED_EXTENSIONS)
            );
        }

        $size = $file->getSize();
        throw_if($size === false, InvalidArgumentException::class, 'Unable to determine image file size');
        throw_if($size > self::MAX_SIZE_BYTES, InvalidArgumentException::class, 'Image size exceeds maximum allowed size of 2MB');
    }

    private function deleteExistingImage(?string $existingImage): void
    {
        if ($existingImage !== null && Storage::disk('public')->exists($existingImage)) {
            Storage::disk('public')->delete($existingImage);
        }
    }

    private function generateFilename(string $directory): string
    {
        return $directory.'/'.Str::uuid()->toString().'.webp';
    }

    /**
     * @throws InvalidImageDriver|Throwable
     */
    private function processImage(UploadedFile $file, int $maxWidth): string
    {
        $driver = extension_loaded('imagick') ? ImageDriver::Imagick : ImageDriver::Gd;
        $tmpPath = sys_get_temp_dir().'/upload_'.Str::uuid()->toString().'.webp';

        Image::useImageDriver($driver)
            ->loadFile($file->getPathname())
            ->fit(fit: Fit::Max, desiredWidth: $maxWidth)
            ->optimize()
            ->format('webp')
            ->save($tmpPath);

        return $tmpPath;
    }
}
