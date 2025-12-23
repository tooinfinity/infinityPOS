<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Data\Pos\ApplyCartDiscountData;
use App\Services\Pos\CartService;

final readonly class ApplyDiscount
{
    public function __construct(private CartService $cart) {}

    public function handle(ApplyCartDiscountData $data, int $userId): void
    {
        $this->cart->setDiscount($userId, $data->discount);
    }
}
