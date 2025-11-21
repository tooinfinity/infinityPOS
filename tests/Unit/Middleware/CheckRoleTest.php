<?php

declare(strict_types=1);

use App\Actions\CreateRoles;
use App\Enums\RoleEnum;
use App\Http\Middleware\CheckRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    (new CreateRoles)->handle();
});

it('allows access when user has required role', function (): void {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckRole;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        RoleEnum::ADMIN->value
    );

    expect($response->getContent())->toBe('OK')
        ->and($response->getStatusCode())->toBe(200);
});

it('allows access when user has any of the required roles', function (): void {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckRole;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        RoleEnum::ADMIN->value,
        RoleEnum::MANAGER->value
    );

    expect($response->getContent())->toBe('OK')
        ->and($response->getStatusCode())->toBe(200);
});

it('denies access when user does not have required role', function (): void {
    $user = User::factory()->cashier()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckRole;

    expect(fn (): Response => $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        RoleEnum::ADMIN->value
    ))->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class, 'Unauthorized access. You do not have the required role.');
});

it('redirects to login when user is not authenticated', function (): void {
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn (): null => null);

    $middleware = new CheckRole;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        RoleEnum::ADMIN->value
    );

    expect($response->isRedirect())->toBeTrue()
        ->and($response->getTargetUrl())->toContain('login');
});

it('works with multiple roles', function (): void {
    $user = User::factory()->manager()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckRole;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        RoleEnum::ADMIN->value,
        RoleEnum::MANAGER->value,
        RoleEnum::CASHIER->value
    );

    expect($response->getContent())->toBe('OK')
        ->and($response->getStatusCode())->toBe(200);
});
