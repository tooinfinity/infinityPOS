<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Http\Middleware\CheckPermission;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    foreach (RoleEnum::cases() as $roleEnum) {
        $role = Role::create(['name' => $roleEnum->value]);
    }

    foreach (PermissionEnum::cases() as $permissionEnum) {
        Permission::create(['name' => $permissionEnum->value]);
    }

    // Manually assign permissions to roles since the action was deleted
    foreach (RoleEnum::cases() as $roleEnum) {
        $role = Role::findByName($roleEnum->value);
        $role->syncPermissions(PermissionEnum::forRole($roleEnum));
    }
});

it('allows access when user has required permission', function (): void {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckPermission;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        PermissionEnum::VIEW_DASHBOARD->value
    );

    expect($response->getContent())->toBe('OK')
        ->and($response->getStatusCode())->toBe(200);
});

it('allows access when user has any of the required permissions', function (): void {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckPermission;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        PermissionEnum::VIEW_DASHBOARD->value,
        PermissionEnum::VIEW_PRODUCTS->value
    );

    expect($response->getContent())->toBe('OK')
        ->and($response->getStatusCode())->toBe(200);
});

it('denies access when user does not have required permission', function (): void {
    $user = User::factory()->cashier()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckPermission;

    expect(fn (): Response => $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        PermissionEnum::VIEW_SETTINGS->value
    ))->toThrow(Symfony\Component\HttpKernel\Exception\HttpException::class, 'Unauthorized access. You do not have the required permission.');
});

it('redirects to login when user is not authenticated', function (): void {
    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn (): null => null);

    $middleware = new CheckPermission;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        PermissionEnum::VIEW_DASHBOARD->value
    );

    expect($response->isRedirect())->toBeTrue()
        ->and($response->getTargetUrl())->toContain('login');
});

it('works with multiple permissions', function (): void {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $request = Request::create('/test', 'GET');
    $request->setUserResolver(fn () => $user);

    $middleware = new CheckPermission;
    $response = $middleware->handle(
        $request,
        fn (): Response => new Response('OK'),
        PermissionEnum::VIEW_DASHBOARD->value,
        PermissionEnum::VIEW_PRODUCTS->value,
        PermissionEnum::VIEW_SALES->value
    );

    expect($response->getContent())->toBe('OK')
        ->and($response->getStatusCode())->toBe(200);
});
