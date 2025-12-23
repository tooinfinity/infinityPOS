<?php

declare(strict_types=1);

use App\Enums\MoneyboxTypeEnum;
use App\Enums\PermissionEnum;
use App\Http\Controllers\Pos\RegisterController;
use App\Models\Moneybox;
use App\Models\PosRegister;
use App\Models\Store;
use App\Models\User;
use App\Services\Pos\PosConfig;
use Illuminate\Http\Request;

beforeEach(function (): void {
    $this->user = User::factory()->create();

    // Create permission
    $permission = App\Models\Permission::query()->firstOrCreate([
        'name' => PermissionEnum::ACCESS_POS->value,
        'guard_name' => 'web',
    ]);

    $this->user->givePermissionTo($permission);
    $this->actingAs($this->user);
});

test('it handles empty device cookie and returns null register', function (): void {
    // This test specifically covers line 28 (the : null; part of the ternary)
    // When $deviceId === '', the ternary should return null

    Store::factory()->create(['is_active' => true]);
    Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
    ]);

    // Use the route to test, but we need to bypass the middleware that creates the cookie
    // We'll test this by checking if a query is made with empty device_id

    $controller = new RegisterController();

    // Create a request with explicitly empty device cookie
    $request = Request::create('/pos/register', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, '');

    // Spy on the PosRegister query to verify line 28 (: null) is executed
    // When deviceId is '', it should NOT query the database
    $queriesBefore = Illuminate\Support\Facades\DB::getQueryLog();
    Illuminate\Support\Facades\DB::enableQueryLog();

    $response = $controller->edit($request);

    $queries = Illuminate\Support\Facades\DB::getQueryLog();
    Illuminate\Support\Facades\DB::disableQueryLog();

    // Verify no query was made to pos_registers (line 28 returned null directly)
    $posRegisterQueries = collect($queries)->filter(fn (array $query): bool => str_contains((string) $query['query'], 'pos_registers'));

    expect($posRegisterQueries)->toHaveCount(0)
        ->and($response)->toBeInstanceOf(Inertia\Response::class);

    // Verify response is Inertia response
});

test('it queries database when device id is present', function (): void {
    Store::factory()->create(['is_active' => true]);
    Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
    ]);

    $controller = new RegisterController();

    // Create a request with a device cookie
    $request = Request::create('/pos/register', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-123');

    Illuminate\Support\Facades\DB::enableQueryLog();

    $response = $controller->edit($request);

    $queries = Illuminate\Support\Facades\DB::getQueryLog();
    Illuminate\Support\Facades\DB::disableQueryLog();

    // Verify a query WAS made to pos_registers (line 26 was executed, not line 28)
    $posRegisterQueries = collect($queries)->filter(fn (array $query): bool => str_contains((string) $query['query'], 'pos_registers'));

    expect($posRegisterQueries)->toHaveCount(1)
        ->and($response)->toBeInstanceOf(Inertia\Response::class);

});

test('it returns register data when register exists', function (): void {
    $store = Store::factory()->create(['is_active' => true]);
    Moneybox::factory()->create([
        'is_active' => true,
        'type' => MoneyboxTypeEnum::CASH_REGISTER,
    ]);

    $register = PosRegister::factory()->create([
        'device_id' => 'existing-device',
        'store_id' => $store->id,
        'name' => 'Test Register',
        'configured_at' => now(),
    ]);

    $controller = new RegisterController();

    // Create a request with the device cookie that has a register
    $request = Request::create('/pos/register', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'existing-device');

    $response = $controller->edit($request);

    // Use reflection to access protected props (for testing purposes)
    $reflection = new ReflectionClass($response);
    $property = $reflection->getProperty('props');
    $props = $property->getValue($response);

    expect($props['register'])->not->toBeNull()
        ->and($props['register']['name'])->toBe('Test Register')
        ->and($props['register']['is_configured'])->toBeTrue();
});
