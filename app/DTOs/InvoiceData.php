<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\InvoicePaymentStatusEnum;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class InvoiceData extends Data
{
    /**
     * @param  array<int, mixed>  $items
     */
    public function __construct(
        #[Required]
        #[MapInputName('store_id')]
        public int $storeId,

        #[Required]
        #[MapInputName('customer_id')]
        public int $customerId,

        #[Required, Date]
        #[MapInputName('invoice_date')]
        public string $invoiceDate,

        #[Nullable, Date]
        #[MapInputName('due_date')]
        public ?string $dueDate,

        #[Required, Min(0)]
        public int $subtotal,

        #[Min(0)]
        #[MapInputName('discount_amount')]
        public int $discountAmount = 0,

        #[Required, Min(0)]
        #[MapInputName('total_amount')]
        public int $totalAmount = 0,

        #[Required]
        #[MapInputName('payment_status')]
        public InvoicePaymentStatusEnum $paymentStatus = InvoicePaymentStatusEnum::UNPAID,

        #[ArrayType]
        public array $items = [],

        #[Nullable]
        public ?string $notes = null,

        #[Nullable]
        public ?string $terms = null,
    ) {}

    public function remainingBalance(int $paidAmount = 0): int
    {
        return max(0, $this->totalAmount - $paidAmount);
    }
}
