<?php

declare(strict_types=1);

namespace App\Http\Controllers\Purchases;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UploadMedia;
use App\Data\UploadMediaData;
use App\Exceptions\MediaUploadException;
use App\Http\Requests\UploadPurchaseAttachmentRequest;
use App\Models\Purchase;
use Illuminate\Http\RedirectResponse;
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
    ): RedirectResponse {
        $action->handle($purchase, UploadMediaData::forPurchaseAttachment($request));

        return redirect()
            ->back()
            ->with('success', 'Attachment uploaded successfully.');
    }

    public function destroy(
        Purchase $purchase,
        DeleteMedia $action,
    ): RedirectResponse {
        $media = $purchase->getFirstMedia('attachment');

        abort_if(! $media, 404, 'No attachment found for this purchase.');

        $action->handle($media);

        return redirect()
            ->back()
            ->with('success', 'Attachment removed.');
    }
}
