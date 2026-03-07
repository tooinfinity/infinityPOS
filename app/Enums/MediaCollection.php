<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaCollection: string
{
    case BrandLogo = 'logo';
    case ProductThumbnail = 'thumbnail';
    case PurchaseAttachment = 'attachment';

    /**
     * @return array<int, string>
     */
    public function allowedMimeTypes(): array
    {
        return match ($this) {
            self::BrandLogo,
            self::ProductThumbnail => [
                'image/jpeg',
                'image/png',
                'image/webp',
            ],

            self::PurchaseAttachment => [
                'image/jpeg',
                'image/png',
                'image/webp',
                'application/pdf',
                'application/msword',                                                      // .doc
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            ],
        };
    }
}
