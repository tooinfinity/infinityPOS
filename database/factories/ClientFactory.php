<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
final class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'address' => $this->faker->optional()->address(),
            'article' => $this->faker->optional()->bothify('ARTICLE-#####'),
            'nif' => $this->faker->optional()->bothify('NIF-#####'),
            'nis' => $this->faker->optional()->bothify('NIS-#####'),
            'rc' => $this->faker->optional()->bothify('RC-#####'),
            'rib' => $this->faker->optional()->bothify('RIB-#####'),
            'is_active' => $this->faker->boolean(95),
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'is_active' => true]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attrs): array => [...$attrs, 'is_active' => false]);
    }
}
