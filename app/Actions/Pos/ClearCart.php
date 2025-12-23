<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Services\Pos\CartService;

final readonly class ClearCart
{
    public function __construct(private CartService $cart) {}

    public function handle(int $userId): void
    {
        $this->cart->clear($userId);
    }
}
