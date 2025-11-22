<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Ensure roles and permissions exist for testing
    foreach (RoleEnum::cases() as $roleEnum) {
        $role = Role::query()->firstOrCreate(['name' => $roleEnum->value]);
        // Assign all permissions to admin role for simplicity in these tests
        if ($roleEnum === RoleEnum::ADMIN) {
            foreach (PermissionEnum::cases() as $permission) {
                Permission::query()->firstOrCreate(['name' => $permission->value]);
            }

            $role->syncPermissions(PermissionEnum::cases());
        }
    }
});

it('login user can renders registration page', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($user)
        ->get(route('users.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('user/index'));
});

it('login user register a new user', function (): void {
    Event::fake([Registered::class]);
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack();

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and(Hash::check('password1234', $user->password))->toBeTrue()
        ->and($user->hasRole(RoleEnum::CASHIER->value))->toBeTrue();

    Event::assertDispatched(Registered::class);
});

it('requires name', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($user)
        ->post(route('users.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('name');
});

it('requires email', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('email');
});

it('requires valid email', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('email');
});

it('requires unique email', function (): void {
    $loginUser = User::factory()->create();
    $loginUser->assignRole(RoleEnum::ADMIN->value);
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->actingAs($loginUser)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('email');
});

it('requires password', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('password');
});

it('requires password confirmation', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->ActingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('password');
});

it('requires matching password confirmation', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->ActingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
            'role' => RoleEnum::CASHIER->value,
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('password');
});

it('requires role', function (): void {
    $user = User::factory()->create();
    $user->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($user)
        ->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('role');
});

it('auth user can delete users account', function (): void {
    $authUser = User::factory()->create();
    $authUser->assignRole(RoleEnum::ADMIN->value);

    $user = User::factory()->create();
    $response = $this->actingAs($authUser)
        ->delete(route('users.destroy', $user));
    $response->assertRedirectBack();
});

it('update user', function (): void {
    $authUser = User::factory()->create();
    $authUser->assignRole(RoleEnum::ADMIN->value);

    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.com',
    ]);

    $response = $this->actingAs($authUser)
        ->patch(route('users.update', $user), [
            'name' => 'New Name',
            'email' => 'new@eample.com',
            'role' => RoleEnum::MANAGER->value,
        ]);

    $response->assertRedirectBack();

    $user->refresh();
    expect($user->name)->toBe('New Name')
        ->and($user->email)->toBe('new@eample.com')
        ->and($user->hasRole(RoleEnum::MANAGER->value))->toBeTrue();
});

it('shows all users except auth user', function (): void {
    $authUser = User::factory()->create();
    $authUser->assignRole(RoleEnum::ADMIN->value);

    $otherUser = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@gmail.com',
        'password' => Hash::make('password'),
    ]);
    $otherUser->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($authUser)
        ->get(route('users.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('user/index')
            ->has('users.data', 1)
            ->where('users.data.0.email', 'test@gmail.com')
        );
});

it('auth user can not delete himself', function (): void {
    $authUser = User::factory()->create();
    $authUser->assignRole(RoleEnum::ADMIN->value);

    $response = $this->actingAs($authUser)
        ->delete(route('users.destroy', $authUser));

    $response->assertRedirectBack();
    $response->assertSessionHasErrors(['message' => 'You cannot delete your own account.']);

    $authUser->refresh();
    expect($authUser->exists)->toBeTrue();
});
