<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class WarehouseSameException extends Exception
{
    public function __construct()
    {
        parent::__construct('Source and destination warehouse cannot be the same.');
    }
}
