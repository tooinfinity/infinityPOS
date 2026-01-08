<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\PaymentMethodEnum;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

final class SaleData extends Data
{
    /**
     * @param  DataCollection<int, SaleItemData>  $items
     * @param  array<int, mixed>|null  $splitPayments
     */
    public function __construct(
        #[Required]
        #[MapInputName('store_id')]
        public int $storeId,

        #[Nullable]
        #[MapInputName('customer_id')]
        public ?int $customerId,

        #[Nullable]
        #[MapInputName('register_session_id')]
        public ?int $registerSessionId,

        #[Required, Min(0)]
        public int $subtotal,

        #[Min(0)]
        #[MapInputName('discount_amount')]
        public int $discountAmount = 0,

        #[Required, Min(0)]
        #[MapInputName('total_amount')]
        public int $totalAmount = 0,

        #[Required]
        #[MapInputName('payment_method')]
        public PaymentMethodEnum $paymentMethod = PaymentMethodEnum::CASH,

        #[Required, Min(0)]
        #[MapInputName('amount_paid')]
        public int $amountPaid = 0,

        #[Required, ArrayType]
        #[DataCollectionOf(SaleItemData::class)]
        public ?DataCollection $items = null,

        #[Nullable, ArrayType]
        #[MapInputName('split_payments')]
        public ?array $splitPayments = null,

        #[Nullable]
        public ?string $notes = null,

        #[Nullable]
        #[MapInputName('cashier_id')]
        public ?int $cashierId = null,
    ) {}

    public function changeGiven(): int
    {
        return max(0, $this->amountPaid - $this->totalAmount);
    }
}
