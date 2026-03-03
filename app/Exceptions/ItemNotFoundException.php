<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class ItemNotFoundException extends Exception
{
    public function __construct(
        public readonly string $itemType,
        public readonly string $context,
        public readonly string $reason
    ) {
        $message = sprintf(
            '%s is not part of the %s. %s',
            $itemType,
            $context,
            $reason
        );

        parent::__construct($message);
    }
}
