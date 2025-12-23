<?php

declare(strict_types=1);

use App\Models\Moneybox;
use App\Models\PosRegister;
use App\Models\Store;

test('it may create a pos register', function (): void {
    $store = Store::factory()->create();

    $register = PosRegister::factory()->create([
        'name' => 'Register 1',
        'device_id' => 'device-123',
        'store_id' => $store->id,
        'is_active' => true,
    ]);

    expect($register)
        ->toBeInstanceOf(PosRegister::class)
        ->name->toBe('Register 1')
        ->device_id->toBe('device-123')
        ->store_id->toBe($store->id)
        ->is_active->toBeTrue()
        ->id->toBeInt()
        ->created_at->toBeInstanceOf(DateTimeInterface::class)
        ->updated_at->toBeInstanceOf(DateTimeInterface::class);
});

test('it belongs to a store', function (): void {
    $store = Store::factory()->create(['name' => 'Main Store']);
    $register = PosRegister::factory()->create(['store_id' => $store->id]);

    $relation = $register->store;

    expect($relation)
        ->toBeInstanceOf(Store::class)
        ->id->toBe($store->id)
        ->name->toBe('Main Store');
});

test('it belongs to a moneybox', function (): void {
    $moneybox = Moneybox::factory()->create(['name' => 'Cash Drawer']);
    $register = PosRegister::factory()->create(['moneybox_id' => $moneybox->id]);

    $relation = $register->moneybox;

    expect($relation)
        ->toBeInstanceOf(Moneybox::class)
        ->id->toBe($moneybox->id)
        ->name->toBe('Cash Drawer');
});

test('it can have null moneybox', function (): void {
    $register = PosRegister::factory()->create(['moneybox_id' => null]);

    expect($register->moneybox_id)->toBeNull();
});

test('it casts attributes correctly', function (): void {
    $register = PosRegister::factory()->create([
        'is_active' => 1,
        'draft_sale_id' => 42,
    ]);

    expect($register->is_active)->toBeBool();
    expect($register->draft_sale_id)->toBeInt();
});

test('it can have null draft_sale_id', function (): void {
    $register = PosRegister::factory()->create(['draft_sale_id' => null]);

    expect($register->draft_sale_id)->toBeNull();
});
