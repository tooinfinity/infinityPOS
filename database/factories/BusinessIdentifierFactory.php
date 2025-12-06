<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BusinessIdentifier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BusinessIdentifier>
 */
final class BusinessIdentifierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article' => $this->faker->optional()->bothify('ART-#####'),
            'nif' => $this->faker->optional()->bothify('NIF-########'),
            'nis' => $this->faker->optional()->bothify('NIS-########'),
            'rc' => $this->faker->optional()->bothify('RC-#####'),
            'rib' => $this->faker->optional()->iban(),
        ];
    }
}
