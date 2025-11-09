<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

it('login user can renders registration page', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)
        ->get(route('register'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page->component('user/create'));
});

it('login user register a new user', function (): void {
    Event::fake([Registered::class]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

    $response->assertRedirectBack();

    $user = User::query()->where('email', 'test@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and(Hash::check('password1234', $user->password))->toBeTrue();

    Event::assertDispatched(Registered::class);
});

it('requires name', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)
        ->post(route('register.store'), [
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('name');
});

it('requires email', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('email');
});

it('requires valid email', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('email');
});

it('requires unique email', function (): void {
    $loginUser = User::factory()->create();
    User::factory()->create(['email' => 'test@example.com']);

    $response = $this->actingAs($loginUser)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('email');
});

it('requires password', function (): void {
    $user = User::factory()->create();
    $response = $this->actingAs($user)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('password');
});

it('requires password confirmation', function (): void {
    $user = User::factory()->create();
    $response = $this->ActingAs($user)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('password');
});

it('requires matching password confirmation', function (): void {
    $user = User::factory()->create();
    $response = $this->ActingAs($user)
        ->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

    $response->assertRedirectBack()
        ->assertSessionHasErrors('password');
});

it('may delete user account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('user-profile.edit')
        ->delete(route('user.destroy'), [
            'password' => 'password',
        ]);

    $response->assertRedirectBack();

    expect($user->fresh())->toBeNull();

});

it('requires password to delete account', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('user-profile.edit')
        ->delete(route('user.destroy'), []);

    $response->assertRedirectToRoute('user-profile.edit')
        ->assertSessionHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});

it('requires correct password to delete account', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->actingAs($user)
        ->fromRoute('user-profile.edit')
        ->delete(route('user.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response->assertRedirectToRoute('user-profile.edit')
        ->assertSessionHasErrors('password');

    expect($user->fresh())->not->toBeNull();
});

// it('redirects authenticated users away from registration', function (): void {
//    $user = User::factory()->create();
//
//    $response = $this->actingAs($user)
//        ->fromRoute('dashboard')
//        ->get(route('register'));
//
//    $response->assertRedirectToRoute('dashboard');
// });
