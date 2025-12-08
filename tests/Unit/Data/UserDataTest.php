<?php

declare(strict_types=1);

use App\Data\UserData;
use App\Models\User;
use Spatie\LaravelData\Lazy;

it('builds UserData from model without needing password or role', function (): void {
    $user = User::factory()->create();

    $data = UserData::fromModel($user);

    expect($data->id)->toBe($user->id)
        ->and($data->name)->toBe($user->name)
        ->and($data->email)->toBe($user->email)
        ->and($data->roles)->toBeInstanceOf(Lazy::class);
});

it('can be constructed for create validation with minimal fields', function (): void {
    // Minimal payload for creation (role/password provided), id and timestamps are optional
    $dto = new UserData(
        id: 1,
        name: 'Jane Doe',
        email: 'jane@example.com',
        created_at: now(),
        updated_at: now(),
        roles: Lazy::create(fn (): Illuminate\Support\Collection => collect([['name' => 'Admin']])),
    );

    expect($dto->id)->toBe(1)
        ->and($dto->name)->toBe('Jane Doe')
        ->and($dto->email)->toBe('jane@example.com')
        ->and($dto->roles)->toBeInstanceOf(Lazy::class)
        ->and($dto->roles->resolve()->first()['name'])->toBe('Admin')
        ->and($dto->created_at)->not()->toBeNull()
        ->and($dto->updated_at)->not()->toBeNull();
});
