<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class StateTransitionException extends Exception
{
    public function __construct(
        string $fromStatus,
        string $toStatus
    ) {
        $message = sprintf(
            'Invalid state transition from "%s" to "%s"',
            $fromStatus,
            $toStatus
        );

        parent::__construct($message);
    }
}
