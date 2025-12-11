<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;

final class MoneyboxData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public string $type,
        public ?string $description,
        public int $balance,
        public ?string $bank_name,
        public ?string $account_number,
        public bool $is_active,
        public Lazy|StoreData|null $store,
        public Lazy|UserData $creator,
        public Lazy|UserData|null $updater,
        public CarbonInterface $created_at,
        public CarbonInterface $updated_at,
    ) {}
}
