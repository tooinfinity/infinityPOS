<?php

declare(strict_types=1);

use App\Http\Middleware\EnsurePosRegisterConfigured;
use App\Models\PosRegister;
use App\Models\Store;
use App\Services\Pos\PosConfig;
use Illuminate\Http\Request;

test('it allows access to pos register routes', function (): void {
    $middleware = new EnsurePosRegisterConfigured();
    $request = Request::create('/pos/register/setup', 'GET');
    $request->setRouteResolver(function (): Illuminate\Routing\Route {
        $route = new Illuminate\Routing\Route('GET', '/pos/register/setup', []);
        $route->name('pos.register.edit');

        return $route;
    });

    $response = $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

test('it continues when no device cookie present', function (): void {
    $middleware = new EnsurePosRegisterConfigured();
    $request = Request::create('/pos', 'GET');
    $request->setRouteResolver(function (): Illuminate\Routing\Route {
        $route = new Illuminate\Routing\Route('GET', '/pos', []);
        $route->name('pos.index');

        return $route;
    });

    $response = $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect($response->getStatusCode())->toBe(200);
});

test('it redirects to setup when register not found', function (): void {
    $middleware = new EnsurePosRegisterConfigured();
    $request = Request::create('/pos', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'non-existent-device');
    $request->setRouteResolver(function (): Illuminate\Routing\Route {
        $route = new Illuminate\Routing\Route('GET', '/pos', []);
        $route->name('pos.index');

        return $route;
    });

    $response = $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toContain('register');
});

test('it redirects to setup when register not configured', function (): void {
    $store = Store::factory()->create();
    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $store->id,
        'configured_at' => null,
    ]);

    $middleware = new EnsurePosRegisterConfigured();
    $request = Request::create('/pos', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');
    $request->setRouteResolver(function (): Illuminate\Routing\Route {
        $route = new Illuminate\Routing\Route('GET', '/pos', []);
        $route->name('pos.index');

        return $route;
    });

    $response = $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect($response->getStatusCode())->toBe(302);
    expect($response->headers->get('Location'))->toContain('register');
});

test('it allows access when register is configured', function (): void {
    $store = Store::factory()->create();
    $register = PosRegister::factory()->create([
        'device_id' => 'test-device',
        'store_id' => $store->id,
        'configured_at' => now(),
    ]);

    $middleware = new EnsurePosRegisterConfigured();
    $request = Request::create('/pos', 'GET');
    $request->cookies->set(PosConfig::DEVICE_COOKIE_NAME, 'test-device');
    $request->setRouteResolver(function (): Illuminate\Routing\Route {
        $route = new Illuminate\Routing\Route('GET', '/pos', []);
        $route->name('pos.index');

        return $route;
    });

    $response = $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect($response->getStatusCode())->toBe(200);
});
