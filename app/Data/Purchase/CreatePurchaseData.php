<?php

declare(strict_types=1);

namespace App\Data\Purchase;

use DateTimeInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CreatePurchaseData extends Data
{
    /**
     * @param  DataCollection<int, PurchaseItemData>  $items
     */
    public function __construct(
        public int $supplier_id,
        public int $warehouse_id,
        public DateTimeInterface|string $purchase_date,
        public ?string $note,
        public ?int $user_id,
        public ?UploadedFile $document,
        public DataCollection $items,
    ) {}
}
