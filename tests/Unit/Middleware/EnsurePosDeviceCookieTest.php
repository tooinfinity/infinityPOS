<?php

declare(strict_types=1);

use App\Http\Middleware\EnsurePosDeviceCookie;
use App\Services\Pos\PosConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

test('it creates a device cookie when not present', function (): void {
    $middleware = new EnsurePosDeviceCookie();
    $request = Request::create('/pos', 'GET');

    $response = $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect($response->getStatusCode())->toBe(200);

    // Check that cookie was queued
    $queued = Cookie::getQueuedCookies();
    expect($queued)->toHaveCount(1);
    expect($queued[0]->getName())->toBe(PosConfig::DEVICE_COOKIE_NAME);
    expect($queued[0]->getValue())->toBeString();
    expect($queued[0]->getValue())->not->toBeEmpty();
});

test('it does not create a cookie when already present', function (): void {
    $middleware = new EnsurePosDeviceCookie();
    $existingDeviceId = 'existing-device-id';

    $request = Request::create('/pos', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, $existingDeviceId);

    Cookie::shouldReceive('queue')->never();

    $response = $middleware->handle($request, function ($req) use ($existingDeviceId): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response {
        expect($req->cookie(PosConfig::DEVICE_COOKIE_NAME))->toBe($existingDeviceId);

        return response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

test('it sets cookie on request for immediate use', function (): void {
    $middleware = new EnsurePosDeviceCookie();
    $request = Request::create('/pos', 'GET');

    $middleware->handle($request, function ($req): Illuminate\Contracts\Routing\ResponseFactory|Illuminate\Http\Response {
        $deviceId = $req->cookie(PosConfig::DEVICE_COOKIE_NAME);
        expect($deviceId)->toBeString();
        expect($deviceId)->not->toBeEmpty();

        return response('OK');
    });
});

test('it uses correct cookie name constant', function (): void {
    expect(EnsurePosDeviceCookie::COOKIE_NAME)->toBe(PosConfig::DEVICE_COOKIE_NAME);
});
