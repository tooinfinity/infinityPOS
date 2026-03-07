<?php

declare(strict_types=1);

namespace App\Actions\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class DeleteMedia
{
    public function handle(Media $media): void
    {
        $media->delete();
    }
}
