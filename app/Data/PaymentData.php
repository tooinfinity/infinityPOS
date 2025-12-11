<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use App\Enums\PaymentTypeEnum;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

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
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
