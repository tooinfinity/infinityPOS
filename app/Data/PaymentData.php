<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Payment;
use Carbon\CarbonInterface;
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
        public Lazy|MoneyboxData|null $moneybox,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
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
            moneybox: Lazy::whenLoaded('moneybox', $payment, fn (): ?MoneyboxData => $payment->moneybox ? MoneyboxData::from($payment->moneybox) : null
            ),
            creator: Lazy::whenLoaded('creator', $payment, fn (): UserData => UserData::from($payment->creator)
            ),
            updater: Lazy::whenLoaded('updater', $payment, fn (): ?UserData => $payment->updater ? UserData::from($payment->updater) : null
            ),
            created_at: $payment->created_at,
            updated_at: $payment->updated_at,
        );
    }
}
