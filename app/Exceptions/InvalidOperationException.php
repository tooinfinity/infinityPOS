<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class InvalidOperationException extends Exception
{
    public function __construct(
        public readonly string $operation,
        public readonly string $entity,
        public readonly string $reason
    ) {
        $message = sprintf(
            'Cannot %s %s. %s',
            $operation,
            $entity,
            $reason
        );

        parent::__construct($message);
    }
}
