<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UploadMedia;
use App\Data\UploadMediaData;
use App\Exceptions\MediaUploadException;
use App\Http\Requests\UploadProductMediaRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
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
    ): RedirectResponse {
        $action->handle($product, UploadMediaData::forProductThumbnail($request));

        return back()
            ->with('success', 'Product thumbnail uploaded successfully.');
    }

    public function destroy(
        Product $product,
        DeleteMedia $action,
    ): RedirectResponse {
        $media = $product->getFirstMedia('thumbnail');

        abort_if(! $media, 404, 'No thumbnail found for this product.');

        $action->handle($media);

        return back()
            ->with('success', 'Brand logo removed.');
    }
}
