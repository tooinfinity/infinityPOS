<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Data;

final class UpdateUserPasswordData extends Data
{
    public function __construct(
        public string $current_password,
        public string $password,
        public string $password_confirmation,
    ) {}

    public static function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];
    }
}
