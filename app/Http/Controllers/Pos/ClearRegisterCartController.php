<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Services\Pos\CartService;
use Illuminate\Http\RedirectResponse;

final readonly class ClearRegisterCartController
{
    public function __invoke(CartService $cart): RedirectResponse
    {
        $userId = auth()->id();
        abort_if($userId === null, 401);

        // This clears the draft sale linked to this device/register.
        $cart->clear((int) $userId);

        return back();
    }
}
