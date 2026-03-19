<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UploadMedia;
use App\Data\UploadMediaData;
use App\Exceptions\MediaUploadException;
use App\Http\Requests\UploadProductMediaRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

final class ProductMediaController
{
    /**
     * @throws MediaUploadException
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function store(
        UploadProductMediaRequest $request,
        Product $product,
        UploadMedia $action,
    ): JsonResponse {
        $media = $action->handle($product, UploadMediaData::forProductThumbnail($request));

        return response()->json([
            'success' => true,
            'message' => 'Product thumbnail uploaded successfully.',
            'media' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumb' => $media->getUrl('thumb') ?: $media->getUrl(),
                'size' => $media->human_readable_size,
            ],
        ]);
    }

    public function destroy(
        Product $product,
        DeleteMedia $action,
    ): JsonResponse {
        $media = $product->getFirstMedia('thumbnail');

        if (! $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            return response()->json([
                'success' => false,
                'message' => 'No thumbnail found for this product.',
            ], 404);
        }

        $action->handle($media);

        return response()->json([
            'success' => true,
            'message' => 'Product thumbnail removed.',
        ]);
    }
}
