<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UpdateUserData;
use App\Models\User;

final readonly class UpdateUser
{
    public function handle(User $user, UpdateUserData $data): void
    {
        $user->update([
            'name' => $data->name,
            'email' => $data->email,
        ]);
    }
}
