<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    private static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => self::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model should be an administrator.
     */
    public function admin(): self
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole(RoleEnum::ADMIN->value));
    }

    /**
     * Indicate that the model should be a manager.
     */
    public function manager(): self
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole(RoleEnum::MANAGER->value));
    }

    /**
     * Indicate that the model should be a cashier.
     */
    public function cashier(): self
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole(RoleEnum::CASHIER->value));
    }
}
