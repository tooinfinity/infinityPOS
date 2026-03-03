<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class RefundNotAllowedException extends Exception
{
    public function __construct(
        public readonly string $entity,
        public readonly string $reason
    ) {
        $message = sprintf(
            'Cannot refund %s. %s',
            $entity,
            $reason
        );

        parent::__construct($message);
    }
}
