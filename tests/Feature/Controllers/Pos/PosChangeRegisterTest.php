<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\EnsurePosDeviceCookie;
use App\Models\PosRegister;
use App\Models\Product;
use App\Models\Store;
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

it('clears device cart when changing register store', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-change-register';

    $storeA = Store::factory()->active()->create(['created_by' => $user->id]);
    $storeB = Store::factory()->active()->create(['created_by' => $user->id]);

    PosRegister::factory()->create([
        'device_id' => $deviceId,
        'store_id' => $storeA->id,
        'configured_at' => now(),
        'created_by' => $user->id,
    ]);

    $product = Product::factory()->create([
        'price' => 1000,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    // Add inventory so stock validation passes
    App\Models\InventoryLayer::factory()->create([
        'product_id' => $product->id,
        'store_id' => $storeA->id,
        'remaining_qty' => 100,
        'received_at' => now(),
    ]);

    // Add to cart (creates draft sale and links it to register)
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->post(route('pos.cart.items.store'), ['product_id' => $product->id, 'quantity' => 1])
        ->assertCreated();

    $register = PosRegister::query()->where('device_id', $deviceId)->firstOrFail();
    expect($register->draft_sale_id)->not->toBeNull();

    // Change register store
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->put(route('pos.register.update'), [
            'store_id' => $storeB->id,
            'name' => 'Register B',
        ])
        ->assertRedirect(route('pos.index'));

    $register->refresh();
    expect($register->store_id)->toBe($storeB->id)
        ->and($register->draft_sale_id)->toBeNull();

    // Cart should now be empty
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->get(route('pos.cart.show'))
        ->assertOk()
        ->assertJsonPath('data.items', []);
});
