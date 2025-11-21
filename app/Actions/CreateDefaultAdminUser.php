<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;
use Throwable;

final readonly class CreateDefaultAdminUser
{
    /**
     * @throws Throwable
     */
    public function handle(
        string $name,
        string $email,
        #[SensitiveParameter] string $password,
    ): User {
        $this->validate([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        return User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        )->assignRole(RoleEnum::ADMIN->value);
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException|Throwable
     */
    private function validate(array $data): void
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        throw_if($validator->fails(), ValidationException::class, $validator);
    }
}
