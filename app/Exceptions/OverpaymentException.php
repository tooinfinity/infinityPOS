<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class OverpaymentException extends Exception
{
    public function __construct(
        public readonly float $amount,
        public readonly float $maxAllowed,
        public readonly ?float $currentPaid = null
    ) {
        $message = sprintf(
            'Payment amount %.2f exceeds maximum allowed %.2f',
            $amount,
            $maxAllowed
        );

        if ($currentPaid !== null) {
            $message = sprintf(
                'Payment amount %.2f exceeds maximum allowed %.2f. Current paid: %.2f',
                $amount,
                $maxAllowed,
                $currentPaid
            );
        }

        parent::__construct($message);
    }
}
