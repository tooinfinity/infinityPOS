<?php

declare(strict_types=1);

use App\Actions\Authentication\AuthenticateUser;
use App\Data\Authentication\CreateSessionData;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    RateLimiter::clear('test@example.com|127.0.0.1');
});

test('it authenticates user with valid credentials', function (): void {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $action = new AuthenticateUser();
    $data = new CreateSessionData(
        email: 'test@example.com',
        password: 'password123',
        remember: false
    );

    $result = $action->handle($data, '127.0.0.1');

    expect($result)
        ->toBeInstanceOf(User::class)
        ->id->toBe($user->id)
        ->email->toBe('test@example.com');
});

test('it throws exception for invalid password', function (): void {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $action = new AuthenticateUser();
    $data = new CreateSessionData(
        email: 'test@example.com',
        password: 'wrongpassword',
        remember: false
    );

    $action->handle($data, '127.0.0.1');
})->throws(ValidationException::class);

test('it throws exception for non-existent user', function (): void {
    $action = new AuthenticateUser();
    $data = new CreateSessionData(
        email: 'nonexistent@example.com',
        password: 'password123',
        remember: false
    );

    $action->handle($data, '127.0.0.1');
})->throws(ValidationException::class);

test('it rate limits after multiple failed attempts', function (): void {
    User::factory()->create([
        'email' => 'rate@example.com',
        'password' => Hash::make('password123'),
    ]);

    $action = new AuthenticateUser();
    $data = new CreateSessionData(
        email: 'rate@example.com',
        password: 'wrongpassword',
        remember: false
    );

    // Make 5 failed attempts
    for ($i = 0; $i < 5; $i++) {
        try {
            $action->handle($data, '127.0.0.1');
        } catch (ValidationException) {
            // Expected
        }
    }

    // 6th attempt should be rate limited
    try {
        $action->handle($data, '127.0.0.1');
        expect(false)->toBeTrue('Should have thrown exception');
    } catch (ValidationException $validationException) {
        // Check that the error is about rate limiting (contains time reference)
        expect($validationException->getMessage())->toMatch('/seconds|minutes/');
    }
});

test('it clears rate limit on successful login', function (): void {
    $user = User::factory()->create([
        'email' => 'clear@example.com',
        'password' => Hash::make('password123'),
    ]);

    $action = new AuthenticateUser();

    // Make some failed attempts
    $wrongData = new CreateSessionData(
        email: 'clear@example.com',
        password: 'wrongpassword',
        remember: false
    );

    for ($i = 0; $i < 3; $i++) {
        try {
            $action->handle($wrongData, '127.0.0.1');
        } catch (ValidationException) {
            // Expected
        }
    }

    // Successful login should clear the rate limit
    $correctData = new CreateSessionData(
        email: 'clear@example.com',
        password: 'password123',
        remember: false
    );

    $result = $action->handle($correctData, '127.0.0.1');

    expect($result)->toBeInstanceOf(User::class);

    // Should be able to make more attempts now
    for ($i = 0; $i < 3; $i++) {
        try {
            $action->handle($wrongData, '127.0.0.1');
        } catch (ValidationException $e) {
            // Expected, but not rate limited
            expect($e->getMessage())->not->toContain('throttle');
        }
    }
});
