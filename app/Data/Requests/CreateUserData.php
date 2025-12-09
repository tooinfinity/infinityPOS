<?php

declare(strict_types=1);

namespace App\Data\Requests;

use App\Enums\RoleEnum;
use App\Rules\ValidEmail;
use Spatie\LaravelData\Attributes\Validation\Confirmed;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Password;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

final class CreateUserData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,
        #[Required, StringType, Max(255), Rule(new ValidEmail), Unique('users', 'email')]
        public string $email,
        #[Required, Confirmed, Password]
        public string $password,
        #[Required, Enum(RoleEnum::class)]
        public RoleEnum $role,
    ) {}
}
