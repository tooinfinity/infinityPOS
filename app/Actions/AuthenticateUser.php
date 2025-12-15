<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateSessionData;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateUser
{
    public function handle(CreateSessionData $data, string $ip): User
    {
        $this->ensureIsNotRateLimited($data->email, $ip);

        /** @var User|null $user */
        $user = Auth::getProvider()->retrieveByCredentials([
            'email' => $data->email,
            'password' => $data->password,
        ]);

        if (! $user || ! Auth::getProvider()->validateCredentials($user, ['password' => $data->password])) {
            RateLimiter::hit($this->throttleKey($data->email, $ip));

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($data->email, $ip));

        return $user;
    }

    private function throttleKey(string $email, string $ip): string
    {
        return Str::transliterate(Str::lower($email).'|'.$ip);
    }

    private function ensureIsNotRateLimited(string $email, string $ip): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($email, $ip), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey($email, $ip));

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
