<?php

use App\Models\User;

it('test csrf with delete', function (): void {
    $authenticatedUser = User::factory()->create();
    $userToDelete = User::factory()->create();

    $response = $this->actingAs($authenticatedUser)
        ->from(route('dashboard'))
        ->delete(route('user.destroy', ['user' => $userToDelete->id]));

    dump($response->status());
    dump($response->headers->all());
});
