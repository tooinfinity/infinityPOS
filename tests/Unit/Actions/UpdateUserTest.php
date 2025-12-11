<?php

declare(strict_types=1);

use App\Actions\UpdateUser;
use App\Models\User;

it('may update a user', function (): void {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@email.com',
    ]);

    $action = resolve(UpdateUser::class);

    $action->handle($user, [
        'name' => 'New Name',
    ]);

    expect($user->refresh()->name)->toBe('New Name')
        ->and($user->email)->toBe('old@email.com');
});
