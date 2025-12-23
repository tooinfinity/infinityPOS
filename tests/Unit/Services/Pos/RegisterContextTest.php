<?php

declare(strict_types=1);

use App\Models\PosRegister;
use App\Models\Store;
use App\Services\Pos\PosConfig;
use App\Services\Pos\RegisterContext;
use Illuminate\Http\Request;

test('it returns null when no device cookie present', function (): void {
    $request = Request::create('/', 'GET');
    $context = new RegisterContext($request);

    $register = $context->current();

    expect($register)->toBeNull();
});

test('it returns null when device id does not match any register', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'non-existent-device');

    $context = new RegisterContext($request);
    $register = $context->current();

    expect($register)->toBeNull();
});

test('it returns register when device id matches', function (): void {
    $store = Store::factory()->create();
    $posRegister = PosRegister::factory()->create([
        'device_id' => 'test-device-123',
        'store_id' => $store->id,
    ]);

    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device-123');

    $context = new RegisterContext($request);
    $register = $context->current();

    expect($register)
        ->toBeInstanceOf(PosRegister::class)
        ->id->toBe($posRegister->id)
        ->device_id->toBe('test-device-123');
});

test('it handles empty device cookie', function (): void {
    $request = Request::create('/', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, '');

    $context = new RegisterContext($request);
    $register = $context->current();

    expect($register)->toBeNull();
});
