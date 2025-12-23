<?php

declare(strict_types=1);

namespace App\Data\Pos;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class UpdatePosRegisterData extends Data
{
    public function __construct(
        #[Required]
        public int $store_id,

        #[Required]
        #[Min(2)]
        public string $name,

        public ?int $moneybox_id = null,
    ) {}
}
