<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\RoleEnum;
use SensitiveParameter;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;

final class CreateSetupData extends Data
{
    public function __construct(
        #[StringType, Max(255)]
        public string $name,

        #[StringType, Max(255), Email, Unique('users', 'email')]
        public string $email,

        #[SensitiveParameter]
        public string $password,

        public string $password_confirmation,

        public ?RoleEnum $role,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'password' => ['string', 'min:8', 'confirmed'],
        ];
    }
}
