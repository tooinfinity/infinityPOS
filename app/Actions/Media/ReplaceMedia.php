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

final readonly class ReplaceMedia
{
    public function __construct(
        private UploadMedia $uploadMedia,
    ) {}

    /**
     * @throws MediaUploadException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function handle(HasMedia&Model $model, UploadMediaData $data): Media
    {
        $model->clearMediaCollection($data->collection->value);

        return $this->uploadMedia->handle($model, $data);
    }
}
