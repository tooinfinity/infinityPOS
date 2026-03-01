<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class InvalidPaymentMethodException extends Exception
{
    public function __construct(
        public readonly int $methodId,
        public readonly ?string $reason = null
    ) {
        $message = $reason !== null
            ? sprintf('Payment method %d: %s', $methodId, $reason)
            : sprintf('Payment method %d is not active or does not exist', $methodId);

        parent::__construct($message);
    }
}
