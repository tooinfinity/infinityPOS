<?php

declare(strict_types=1);

namespace App\Exceptions;

use InvalidArgumentException;

final class InvalidPaymentAmountException extends InvalidArgumentException
{
    public static function mustBePositive(int $amount): self
    {
        return new self("Payment amount must be positive. Given: {$amount}.");
    }

    public static function mustBeNonZero(): self
    {
        return new self('Payment amount cannot be zero.');
    }
}
