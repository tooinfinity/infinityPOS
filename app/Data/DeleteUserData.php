<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Attributes\Validation\CurrentPassword;
use Spatie\LaravelData\Data;

final class DeleteUserData extends Data
{
    public function __construct(
        #[CurrentPassword]
        public string $password,
    ) {}
}
