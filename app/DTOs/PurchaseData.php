<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PaymentMethodEnum;
use App\Enums\PurchaseStatusEnum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class PurchaseData extends Data
{
    /**
     * @param  DataCollection<int, PurchaseItemData>  $items
     */
    public function __construct(
        #[Required]
        #[MapInputName('store_id')]
        public int $storeId,

        #[Required]
        #[MapInputName('supplier_id')]
        public int $supplierId,

        #[Required]
        #[MapInputName('purchase_date')]
        public string $purchaseDate,

        #[Required, Min(0)]
        #[MapInputName('total_amount')]
        public int $totalAmount,

        #[Required]
        #[MapInputName('payment_status')]
        public PurchaseStatusEnum $paymentStatus = PurchaseStatusEnum::PENDING,

        #[Nullable]
        #[MapInputName('payment_method')]
        public ?PaymentMethodEnum $paymentMethod = null,

        #[Nullable, Max(100)]
        #[MapInputName('reference_number')]
        public ?string $referenceNumber = null,

        #[ArrayType]
        #[DataCollectionOf(PurchaseItemData::class)]
        public ?DataCollection $items = null,

        #[Nullable]
        public ?string $notes = null,
    ) {}

    public function calculateTotal(): int
    {
        if (! $this->items instanceof DataCollection || $this->items->count() === 0) {
            return 0;
        }

        return $this->items->reduce(
            fn (int $carry, PurchaseItemData $item): int => $carry + $item->subtotal(),
            0
        );
    }
}
