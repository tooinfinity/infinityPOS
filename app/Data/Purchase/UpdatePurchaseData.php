<?php

declare(strict_types=1);

namespace App\Data\Purchase;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdatePurchaseData extends Data
{
    public function __construct(
        public int|Optional $supplier_id,
        public int|Optional $warehouse_id,
        public DateTimeInterface|string|Optional $purchase_date,
        public string|null|Optional $note,
        public UploadedFile|null|Optional $document,
    ) {}
}
