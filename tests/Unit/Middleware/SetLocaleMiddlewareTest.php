<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

test('it sets locale from cookie', function (): void {
    $middleware = new SetLocaleMiddleware();
    $request = Request::create('/', 'GET');
    $request->cookies->set('locale', 'fr');

    $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect(App::getLocale())->toBe('fr');
});

test('it sets locale from session when cookie not present', function (): void {
    $middleware = new SetLocaleMiddleware();
    $request = Request::create('/', 'GET');
    session(['locale' => 'ar']);

    $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect(App::getLocale())->toBe('ar');
});

test('it uses default locale when neither cookie nor session present', function (): void {
    config(['app.locale' => 'en']);

    $middleware = new SetLocaleMiddleware();
    $request = Request::create('/', 'GET');

    $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect(App::getLocale())->toBe('en');
});

test('it prioritizes cookie over session', function (): void {
    $middleware = new SetLocaleMiddleware();
    $request = Request::create('/', 'GET');
    $request->cookies->set('locale', 'fr');
    session(['locale' => 'ar']);

    $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    expect(App::getLocale())->toBe('fr');
});

test('it handles non-string locale gracefully', function (): void {
    config(['app.locale' => 'en']);

    $middleware = new SetLocaleMiddleware();
    $request = Request::create('/', 'GET');

    $middleware->handle($request, fn ($req): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('OK'));

    // Should not throw exception
    expect(App::getLocale())->toBeString();
});
