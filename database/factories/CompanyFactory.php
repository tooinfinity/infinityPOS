<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BusinessIdentifier;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
final class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'email' => $this->faker->optional()->companyEmail(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'phone_secondary' => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->optional()->streetAddress(),
            'city' => $this->faker->optional()->city(),
            'state' => $this->faker->optional()->citySuffix(),
            'zip' => $this->faker->optional()->postcode(),
            'country' => $this->faker->optional()->country(),
            'logo' => null,
            'website' => $this->faker->optional()->url(),
            'description' => $this->faker->optional()->sentence(),
            'currency' => 'USD',
            'currency_symbol' => '$',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'business_identifier_id' => BusinessIdentifier::factory(),
        ];
    }
}
