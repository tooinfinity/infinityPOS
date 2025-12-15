<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Data;

final class CreateUserPasswordData extends Data
{
    public function __construct(
        public string $token,
        public string $email,
        public string $password,
        public string $password_confirmation,
    ) {}

    public static function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
