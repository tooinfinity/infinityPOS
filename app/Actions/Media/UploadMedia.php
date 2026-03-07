<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Data\UploadMediaData;
use App\Exceptions\MediaUploadException;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class UploadMedia
{
    /**
     * @throws MediaUploadException
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function handle(HasMedia&Model $model, UploadMediaData $data): Media
    {
        $this->ensureStorageIsWritable();

        $adder = $model
            ->addMedia($data->file)
            ->withCustomProperties($data->customProperties);

        if ($data->name) {
            $adder->usingName($data->name);
        }

        /** @var Media $media */
        $media = $adder->toMediaCollection($data->collection->value);

        return $media;
    }

    /**
     * @throws MediaUploadException
     */
    private function ensureStorageIsWritable(): void
    {
        if (! is_writable(storage_path('app'))) {
            throw MediaUploadException::storageFailed('Storage directory is not writable.');
        }
    }
}
