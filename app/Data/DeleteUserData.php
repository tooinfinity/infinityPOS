<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

final class DeleteUserData extends Data
{
    public function __construct(
        public string $password,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }
}
