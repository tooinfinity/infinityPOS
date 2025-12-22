<?php

declare(strict_types=1);

use App\Actions\Moneyboxes\UpdateMoneybox;
use App\Data\Moneyboxes\UpdateMoneyboxData;
use App\Enums\MoneyboxTypeEnum;
use App\Models\Moneybox;
use App\Models\User;

it('may update moneybox name', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'name' => 'Old Name',
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneybox::class);

    $data = UpdateMoneyboxData::from([
        'name' => 'New Name',
        'type' => null,
        'description' => null,
        'bank_name' => null,
        'account_number' => null,
        'is_active' => null,
        'store_id' => null,
        'updated_by' => $user->id,
    ]);

    $updatedMoneybox = $action->handle($moneybox, $data);

    expect($updatedMoneybox->name)->toBe('New Name')
        ->and($updatedMoneybox->updated_by)->toBe($user->id);
});

it('may update moneybox type', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneybox::class);

    $data = UpdateMoneyboxData::from([
        'name' => null,
        'type' => MoneyboxTypeEnum::BANK_ACCOUNT,
        'description' => null,
        'bank_name' => null,
        'account_number' => null,
        'is_active' => null,
        'store_id' => null,
        'updated_by' => $user->id,
    ]);

    $updatedMoneybox = $action->handle($moneybox, $data);

    expect($updatedMoneybox->type)->toBe(MoneyboxTypeEnum::BANK_ACCOUNT);
});

it('may update moneybox active status', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneybox::class);

    $data = UpdateMoneyboxData::from([
        'name' => null,
        'type' => null,
        'description' => null,
        'bank_name' => null,
        'account_number' => null,
        'is_active' => false,
        'store_id' => null,
        'updated_by' => $user->id,
    ]);

    $updatedMoneybox = $action->handle($moneybox, $data);

    expect($updatedMoneybox->is_active)->toBeFalse();
});

it('may update bank details', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'type' => MoneyboxTypeEnum::BANK_ACCOUNT,
        'bank_name' => 'Old Bank',
        'account_number' => '1111111111',
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneybox::class);

    $data = UpdateMoneyboxData::from([
        'name' => null,
        'type' => null,
        'description' => null,
        'bank_name' => 'New Bank',
        'account_number' => '9999999999',
        'is_active' => null,
        'store_id' => null,
        'updated_by' => $user->id,
    ]);

    $updatedMoneybox = $action->handle($moneybox, $data);

    expect($updatedMoneybox->bank_name)->toBe('New Bank')
        ->and($updatedMoneybox->account_number)->toBe('9999999999');
});

it('does not update balance', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneybox::class);

    $data = UpdateMoneyboxData::from([
        'name' => 'Updated Name',
        'type' => null,
        'description' => null,
        'bank_name' => null,
        'account_number' => null,
        'is_active' => null,
        'store_id' => null,
        'updated_by' => $user->id,
    ]);

    $updatedMoneybox = $action->handle($moneybox, $data);

    expect($updatedMoneybox->balance)->toBe(50000);
});
