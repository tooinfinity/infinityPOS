<?php

declare(strict_types=1);

use App\Enums\MoneyboxTypeEnum;
use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\EnsurePosDeviceCookie;
use App\Models\Moneybox;
use App\Models\PosRegister;
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

it('can assign a cash register moneybox to a device register', function (): void {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionEnum::ACCESS_POS->value);

    $deviceId = 'test-device-register-moneybox';
    $store = Store::factory()->active()->create(['created_by' => $user->id]);

    $moneybox = Moneybox::factory()->create([
        'type' => MoneyboxTypeEnum::CASH_REGISTER->value,
        'store_id' => $store->id,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    // Configure register with moneybox
    $this->actingAs($user)
        ->withCookie(EnsurePosDeviceCookie::COOKIE_NAME, $deviceId)
        ->put(route('pos.register.update'), [
            'store_id' => $store->id,
            'name' => 'Register 1',
            'moneybox_id' => $moneybox->id,
        ])
        ->assertRedirect(route('pos.index'));

    $register = PosRegister::query()->where('device_id', $deviceId)->firstOrFail();
    expect($register->moneybox_id)->toBe($moneybox->id);
});
