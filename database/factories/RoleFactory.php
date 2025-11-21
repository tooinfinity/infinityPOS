<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<Role>
 */
final class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = [
            RoleEnum::ADMIN->value,
            RoleEnum::MANAGER->value,
            RoleEnum::CASHIER->value,
        ];

        return [
            'name' => $this->faker->randomElement($roles),
            'guard_name' => 'web',
        ];
    }
}
