<?php

declare(strict_types=1);

use App\Enums\PaymentMethodEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    foreach (RoleEnum::cases() as $roleEnum) {
        Role::query()->firstOrCreate(['name' => $roleEnum->value]);
    }

    foreach (PermissionEnum::cases() as $permission) {
        SpatiePermission::query()->firstOrCreate(['name' => $permission->value]);
    }
});

it('returns a receipt payload for a sale', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $store = Store::factory()->active()->create(['created_by' => $user->id]);
    $product = Product::factory()->create(['created_by' => $user->id]);

    $sale = Sale::factory()->completed()->create([
        'store_id' => $store->id,
        'created_by' => $user->id,
    ]);

    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'price' => 1000,
        'discount' => 0,
        'tax_amount' => 0,
        'total' => 2000,
    ]);

    Payment::factory()->create([
        'related_type' => Sale::class,
        'related_id' => $sale->id,
        'amount' => 2000,
        'method' => PaymentMethodEnum::CASH->value,
        'created_by' => $user->id,
    ]);

    $sale->refresh();

    $deviceId = 'test-device-pos-receipt';
    App\Models\PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $store->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.receipts.show', ['sale' => $sale->id]));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'sale',
                'items',
                'payments',
                'totals' => ['subtotal', 'discount', 'tax', 'total', 'paid', 'due'],
            ],
        ]);
});

it('denies receipts endpoint without access_pos', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create();

    $this->actingAs($user)
        ->withCookie(App\Http\Middleware\EnsurePosDeviceCookie::COOKIE_NAME, 'test-device-pos-receipt-forbidden')
        ->get(route('pos.receipts.show', ['sale' => $sale->id]))
        ->assertForbidden();
});
