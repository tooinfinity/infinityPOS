<?php

declare(strict_types=1);

namespace App\Data\Purchase;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Data;

final class ReceivePurchaseItemData extends Data
{
    public function __construct(
        #[IntegerType, Exists('purchase_items', 'id')]
        public int $purchase_item_id,

        #[IntegerType, Min(0)]
        public int $received_quantity,

        #[Nullable]
        public ?CarbonInterface $expires_at,
    ) {}
}
