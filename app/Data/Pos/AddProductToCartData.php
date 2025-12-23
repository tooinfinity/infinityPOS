<?php

declare(strict_types=1);

namespace App\Data\Pos;

use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

final class AddProductToCartData extends Data
{
    public function __construct(
        #[Required]
        public int $product_id,

        #[Min(1)]
        public int $quantity,
    ) {}

    public static function defaults(): self
    {
        return new self(product_id: 0, quantity: 1);
    }
}
