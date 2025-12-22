<?php

declare(strict_types=1);

namespace App\Data\Moneyboxes;

use App\Enums\MoneyboxTypeEnum;
use Spatie\LaravelData\Data;

final class CreateMoneyboxData extends Data
{
    public function __construct(
        public string $name,
        public MoneyboxTypeEnum $type,
        public ?string $description,
        public ?string $bank_name,
        public ?string $account_number,
        public bool $is_active,
        public ?int $store_id,
        public int $created_by,
    ) {}
}
