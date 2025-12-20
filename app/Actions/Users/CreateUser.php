<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Data\Users\CreateUserData;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

final readonly class CreateUser
{
    public function handle(CreateUserData $data): User
    {
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
        ]);

        event(new Registered($user));

        return $user;
    }
}
