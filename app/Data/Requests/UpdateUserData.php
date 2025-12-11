<?php

declare(strict_types=1);

namespace App\Data\Requests;

use App\Enums\RoleEnum;
use App\Rules\ValidEmail;
use Spatie\LaravelData\Attributes\Validation\Enum;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\References\AuthenticatedUserReference;

final class UpdateUserData extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public string $name,
        #[Required, StringType, Max(255), Rule(new ValidEmail), Unique('users', 'email', ignore: new AuthenticatedUserReference())]
        public string $email,
        #[Sometimes, StringType, Enum(RoleEnum::class)]
        public ?RoleEnum $role = null,
    ) {}
}
