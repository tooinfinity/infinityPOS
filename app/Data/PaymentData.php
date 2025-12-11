<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Payment;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class PaymentData extends Data
{
    public function __construct(
        public int $id,
        public ?string $reference,
        public string $type,
        public int $amount,
        public string $method,
        public ?string $notes,
        public ?int $related_id,
        #[Lazy] public ?MoneyboxData $moneybox,
        #[Lazy] public ?UserData $creator,
        #[Lazy] public ?UserData $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}

    public static function fromModel(Payment $payment): self
    {
        return new self(
            id: $payment->id,
            reference: $payment->reference,
            type: $payment->type->value,
            amount: $payment->amount,
            method: $payment->method,
            notes: $payment->notes,
            related_id: $payment->related_id,
            moneybox: $payment->moneybox ? MoneyboxData::from($payment->moneybox) : null,
            creator: $payment->creator ? UserData::from($payment->creator) : null,
            updater: $payment->updater ? UserData::from($payment->updater) : null,
            created_at: $payment->created_at->toDayDateTimeString(),
            updated_at: $payment->updated_at->toDayDateTimeString(),
        );
    }
}
