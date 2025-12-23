<?php

declare(strict_types=1);

use App\Enums\MoneyboxTransactionTypeEnum;
use App\Enums\MoneyboxTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\EnsurePosDeviceCookie;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tax;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (RoleEnum::cases() as $roleEnum) {
        Role::query()->firstOrCreate(['name' => $roleEnum->value]);
    }

    foreach (PermissionEnum::cases() as $permission) {
        Permission::query()->firstOrCreate(['name' => $permission->value]);
    }
});

it('records cash POS payment into assigned cash drawer moneybox', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-pos-moneybox';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    $moneybox = Moneybox::factory()->create([
        'type' => MoneyboxTypeEnum::CASH_REGISTER->value,
        'store_id' => $store->id,
        'balance' => 0,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'moneybox_id' => $moneybox->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $tax = Tax::factory()->percentage(0)->active()->create([
        'tax_type' => App\Enums\TaxTypeEnum::PERCENTAGE->value,
        'rate' => 0,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'cost' => 400,
        'tax_id' => $tax->id,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    // Add inventory so stock validation passes
    App\Models\InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $store->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertCreated();

    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.payments.store'), [
            'store_id' => $store->id,
            'amount' => 1000,
            'method' => PaymentMethodEnum::CASH->value,
            'reference' => 'POS-MB-1',
        ])
        ->assertCreated();

    expect(MoneyboxTransaction::query()->where('moneybox_id', $moneybox->id)->count())
        ->toBe(1);

    $trx = MoneyboxTransaction::query()->where('moneybox_id', $moneybox->id)->firstOrFail();
    expect($trx->type)->toBe(MoneyboxTransactionTypeEnum::IN)
        ->and($trx->amount)->toBe(1000);
});
