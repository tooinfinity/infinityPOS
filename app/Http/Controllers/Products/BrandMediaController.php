<?php

declare(strict_types=1);

namespace App\Http\Controllers\Products;

use App\Actions\Media\DeleteMedia;
use App\Actions\Media\UploadMedia;
use App\Data\UploadMediaData;
use App\Exceptions\MediaUploadException;
use App\Http\Requests\UploadBrandLogoRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
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
    ): RedirectResponse {
        $action->handle($brand, UploadMediaData::forBrandLogo($request));

        return redirect()
            ->back()
            ->with('success', 'Brand logo uploaded successfully.');
    }

    public function destroy(
        Brand $brand,
        DeleteMedia $action,
    ): RedirectResponse {
        $media = $brand->getFirstMedia('logo');

        abort_if(! $media, 404, 'No logo found for this brand.');

        $action->handle($media);

        return redirect()
            ->back()
            ->with('success', 'Brand logo removed.');
    }
}
