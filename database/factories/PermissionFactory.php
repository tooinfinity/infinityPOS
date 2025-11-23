<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PermissionEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Permission;

/**
 * @extends Factory<Permission>
 */
final class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permissions = PermissionEnum::allPermissions();

        return [
            'name' => $this->faker->randomElement($permissions),
            'guard_name' => 'web',
        ];
    }
}
