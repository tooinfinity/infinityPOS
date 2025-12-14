<?php

declare(strict_types=1);

namespace Tests\Unit\Data;

use App\Data\CreateSetupData;
use App\Enums\RoleEnum;
use Illuminate\Validation\ValidationException;

it('validates setup data', function (): void {
    $data = CreateSetupData::from([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'P@ssword123!',
        'password_confirmation' => 'P@ssword123!',
        'role' => RoleEnum::ADMIN,
    ]);

    expect($data)->toBeInstanceOf(CreateSetupData::class)
        ->and($data->email)->toBe('admin@example.com')
        ->and($data->password)->toBe('P@ssword123!');
});

it('throws exception when passwords do not match', function (): void {
    expect(fn (): CreateSetupData => CreateSetupData::validateAndCreate([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'P@ssword123!',
        'password_confirmation' => 'Different',
        'role' => RoleEnum::ADMIN,
    ]))->toThrow(ValidationException::class);
});
