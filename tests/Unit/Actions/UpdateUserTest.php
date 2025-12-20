<?php

declare(strict_types=1);

use App\Actions\Users\UpdateUser;
use App\Data\Users\UpdateUserData;
use App\Models\User;

it('may update a user', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@email.com',
    ]);

    $action = resolve(UpdateUser::class);

    $data = UpdateUserData::from([
        'name' => 'New Name',
        'email' => 'old@email.com',
    ]);

    $action->handle($user, $data);

    expect($user->refresh()->name)->toBe('New Name')
        ->and($user->email)->toBe('old@email.com');
});
