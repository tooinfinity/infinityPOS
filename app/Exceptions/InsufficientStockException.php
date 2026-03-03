<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $required,
        public readonly int $available,
        public readonly ?int $batchId = null,
        public readonly ?string $productName = null,
    ) {
        $productPrefix = $productName !== null ? " for product \"{$productName}\"" : '';

        $message = sprintf(
            'Insufficient stock%s. Required: %d, Available: %d',
            $productPrefix,
            $required,
            $available
        );

        if ($batchId !== null) {
            $message = sprintf(
                'Insufficient stock in batch %d%s. Required: %d, Available: %d',
                $batchId,
                $productPrefix,
                $required,
                $available
            );
        }

        parent::__construct($message);
    }
}
