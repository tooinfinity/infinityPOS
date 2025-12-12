<?php

declare(strict_types=1);

use App\Data\MoneyboxData;
use App\Data\StoreData;
use App\Data\UserData;
use App\Models\Moneybox;
use App\Models\Store;
use App\Models\User;

it('transforms an moneybox model into MoneyboxData', function (): void {

    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $store = Store::factory()->create();

    /** @var Moneybox $moneybox */
    $moneybox = Moneybox::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($store, 'store')
        ->create([
            'name' => 'Cash',
            'type' => App\Enums\MoneyboxTypeEnum::CASH_REGISTER->value,
            'description' => 'Cash in hand',
            'balance' => 50000,
            'bank_name' => 'Cash',
            'account_number' => '123456789',
            'is_active' => true,
        ]);

    $data = MoneyboxData::from(
        $moneybox->load([
            'creator',
            'updater',
            'store',
        ])
    );

    expect($data)
        ->toBeInstanceOf(MoneyboxData::class)
        ->id->toBe($moneybox->id)
        ->name->toBe('Cash')
        ->type->toBe(App\Enums\MoneyboxTypeEnum::CASH_REGISTER)
        ->description->toBe('Cash in hand')
        ->balance->toBe(50000)
        ->bank_name->toBe('Cash')
        ->account_number->toBe('123456789')
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->store->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($store->id)
        ->and($data->created_at)
        ->toBe($moneybox->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($moneybox->updated_at->toDateTimeString());

});
