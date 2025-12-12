<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\PaymentTypeEnum;
use Spatie\LaravelData\Attributes\AutoLazy;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

#[AutoLazy]
final class PaymentData extends Data
{
    public function __construct(
        public int $id,
        public ?string $reference,
        public PaymentTypeEnum $type,
        public int $amount,
        public string $method,
        public ?string $notes,
        public ?int $related_id,
        public Lazy|MoneyboxData|null $moneybox,
        public Lazy|UserData|null $creator,
        public Lazy|UserData|null $updater,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at,
    ) {}
}
