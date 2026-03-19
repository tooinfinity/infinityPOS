<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UploadMedia;
use App\Data\UploadMediaData;
use App\Exceptions\MediaUploadException;
use App\Http\Requests\UploadPurchaseAttachmentRequest;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

final class PurchaseAttachmentController
{
    /**
     * @throws MediaUploadException
     * @throws FileIsTooBig
     * @throws FileDoesNotExist
     */
    public function store(
        UploadPurchaseAttachmentRequest $request,
        Purchase $purchase,
        UploadMedia $action,
    ): JsonResponse {
        $media = $action->handle($purchase, UploadMediaData::forPurchaseAttachment($request));

        return response()->json([
            'success' => true,
            'message' => 'Attachment uploaded successfully.',
            'media' => [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'name' => $media->name,
                'size' => $media->human_readable_size,
            ],
        ]);
    }

    public function destroy(
        Purchase $purchase,
        DeleteMedia $action,
    ): JsonResponse {
        $media = $purchase->getFirstMedia('attachment');

        if (! $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media) {
            return response()->json([
                'success' => false,
                'message' => 'No attachment found for this purchase.',
            ], 404);
        }

        $action->handle($media);

        return response()->json([
            'success' => true,
            'message' => 'Attachment removed.',
        ]);
    }
}
