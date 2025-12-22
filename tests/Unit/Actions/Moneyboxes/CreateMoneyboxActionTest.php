<?php

declare(strict_types=1);

use App\Actions\Moneyboxes\CreateMoneybox;
use App\Data\Moneyboxes\CreateMoneyboxData;
use App\Enums\MoneyboxTypeEnum;
use App\Models\Moneybox;
use App\Models\Store;
use App\Models\User;

it('may create a cash moneybox', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $action = resolve(CreateMoneybox::class);

    $data = CreateMoneyboxData::from([
        'name' => 'Cash Register 1',
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
        'description' => 'Main cash register',
        'bank_name' => null,
        'account_number' => null,
        'is_active' => true,
        'store_id' => $store->id,
        'created_by' => $user->id,
    ]);

    $moneybox = $action->handle($data);

    expect($moneybox)->toBeInstanceOf(Moneybox::class)
        ->and($moneybox->name)->toBe('Cash Register 1')
        ->and($moneybox->type)->toBe(MoneyboxTypeEnum::CASH_REGISTER)
        ->and($moneybox->description)->toBe('Main cash register')
        ->and($moneybox->balance)->toBe(0)
        ->and($moneybox->is_active)->toBeTrue()
        ->and($moneybox->store_id)->toBe($store->id)
        ->and($moneybox->created_by)->toBe($user->id);
});

it('may create a bank moneybox', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateMoneybox::class);

    $data = CreateMoneyboxData::from([
        'name' => 'Business Bank Account',
        'type' => MoneyboxTypeEnum::BANK_ACCOUNT,
        'description' => 'Main checking account',
        'bank_name' => 'First National Bank',
        'account_number' => '1234567890',
        'is_active' => true,
        'store_id' => null,
        'created_by' => $user->id,
    ]);

    $moneybox = $action->handle($data);

    expect($moneybox->type)->toBe(MoneyboxTypeEnum::BANK_ACCOUNT)
        ->and($moneybox->bank_name)->toBe('First National Bank')
        ->and($moneybox->account_number)->toBe('1234567890');
});

it('may create a mobile moneybox', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateMoneybox::class);

    $data = CreateMoneyboxData::from([
        'name' => 'Mobile Wallet',
        'type' => MoneyboxTypeEnum::MOBILE_MONEY,
        'description' => 'Mobile money account',
        'bank_name' => null,
        'account_number' => '+1234567890',
        'is_active' => true,
        'store_id' => null,
        'created_by' => $user->id,
    ]);

    $moneybox = $action->handle($data);

    expect($moneybox->type)->toBe(MoneyboxTypeEnum::MOBILE_MONEY);
});

it('creates moneybox with zero balance', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateMoneybox::class);

    $data = CreateMoneyboxData::from([
        'name' => 'New Register',
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
        'description' => null,
        'bank_name' => null,
        'account_number' => null,
        'is_active' => true,
        'store_id' => null,
        'created_by' => $user->id,
    ]);

    $moneybox = $action->handle($data);

    expect($moneybox->balance)->toBe(0);
});
