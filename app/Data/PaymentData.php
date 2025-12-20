<?php

declare(strict_types=1);

namespace App\Data;

use App\Data\Users\UserData;
use App\Enums\PaymentMethodEnum;
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
        public ?string $related_type,
        public int $amount,
        public PaymentMethodEnum $method,
        public ?string $notes,
        public ?int $related_id,
        public Lazy|MoneyboxData|null $moneybox = null,
        public Lazy|UserData|null $creator = null,
        public Lazy|UserData|null $updater = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $created_at = null,
        #[WithCast(DateTimeInterfaceCast::class)]
        public ?string $updated_at = null,
    ) {}
}
