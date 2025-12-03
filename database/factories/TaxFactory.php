<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TaxTypeEnum;
use App\Models\Tax;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tax>
 */
final class TaxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(TaxTypeEnum::cases());
        $rate = match ($type) {
            TaxTypeEnum::PERCENTAGE => $this->faker->randomFloat(2, 1, 30),
            TaxTypeEnum::FIXED => $this->faker->randomFloat(2, 0.1, 50),
        };

        return [
            'name' => $this->faker->unique()->word().' Tax',
            'tax_type' => $type->value,
            'rate' => $rate,
            'is_active' => $this->faker->boolean(95),
        ];
    }

    public function percentage(?float $rate = null): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'tax_type' => TaxTypeEnum::PERCENTAGE->value,
            'rate' => $rate ?? $this->faker->randomFloat(2, 1, 30),
        ]);
    }

    public function fixed(?float $amount = null): self
    {
        return $this->state(fn (array $attrs): array => [
            ...$attrs,
            'tax_type' => TaxTypeEnum::FIXED->value,
            'rate' => $amount ?? $this->faker->randomFloat(2, 0.1, 50),
        ]);
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
