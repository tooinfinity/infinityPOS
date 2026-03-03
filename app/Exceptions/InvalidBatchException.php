<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class InvalidBatchException extends Exception
{
    public function __construct(
        public readonly int|string $batchId,
        public readonly string $reason
    ) {
        $message = sprintf(
            'Batch %s: %s',
            $batchId,
            $reason
        );

        parent::__construct($message);
    }
}
