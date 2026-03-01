<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $required,
        public readonly int $available,
        public readonly ?int $batchId = null
    ) {
        $message = sprintf(
            'Insufficient stock. Required: %d, Available: %d',
            $required,
            $available
        );

        if ($batchId !== null) {
            $message = sprintf(
                'Insufficient stock in batch %d. Required: %d, Available: %d',
                $batchId,
                $required,
                $available
            );
        }

        parent::__construct($message);
    }
}
