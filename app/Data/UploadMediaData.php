<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\MediaCollection;
use App\Http\Requests\UploadBrandLogoRequest;
use App\Http\Requests\UploadProductMediaRequest;
use App\Http\Requests\UploadPurchaseAttachmentRequest;
use Illuminate\Http\UploadedFile;

final readonly class UploadMediaData
{
    /**
     * @param  array<string, mixed>  $customProperties
     */
    public function __construct(
        public UploadedFile $file,
        public MediaCollection $collection,
        public ?string $name = null,
        public array $customProperties = [],
    ) {}

    public static function forBrandLogo(UploadBrandLogoRequest $request): self
    {
        return new self(
            file: $request->file('logo'),
            collection: MediaCollection::BrandLogo,
        );
    }

    public static function forProductThumbnail(UploadProductMediaRequest $request): self
    {
        return new self(
            file: $request->file('file'),
            collection: MediaCollection::ProductThumbnail,
        );
    }

    public static function forPurchaseAttachment(UploadPurchaseAttachmentRequest $request): self
    {
        return new self(
            file: $request->file('file'),
            collection: MediaCollection::PurchaseAttachment,
            name: $request->file('file')->getClientOriginalName(),
        );
    }
}
