<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UploadMedia;
use App\Data\UploadMediaData;
use App\Exceptions\MediaUploadException;
use App\Http\Requests\UploadBrandLogoRequest;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

final class BrandMediaController
{
    /**
     * @throws MediaUploadException
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function store(
        UploadBrandLogoRequest $request,
        Brand $brand,
        UploadMedia $action,
    ): JsonResponse {
        $media = $action->handle($brand, UploadMediaData::forBrandLogo($request));

        return response()->json([
            'success' => true,
            'message' => 'Brand logo uploaded successfully.',
            'media' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb') ?: $media->getUrl(),
                'size' => $media->human_readable_size,
            ],
        ]);
    }

    public function destroy(
        Brand $brand,
        DeleteMedia $action,
    ): JsonResponse {
        $media = $brand->getFirstMedia('logo');

        if (! $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            return response()->json([
                'success' => false,
                'message' => 'No logo found for this brand.',
            ], 404);
        }

        $action->handle($media);

        return response()->json([
            'success' => true,
            'message' => 'Brand logo removed.',
        ]);
    }
}
