<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SettingTypeEnum;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;
use JsonException;

/**
 * @extends Factory<Setting>
 */
final class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(SettingTypeEnum::cases());

        $value = match ($type) {
            SettingTypeEnum::STRING, SettingTypeEnum::FILE => $this->faker->sentence(3),
            SettingTypeEnum::NUMBER => (string) $this->faker->randomFloat(2, 0, 1000),
            SettingTypeEnum::BOOLEAN => $this->faker->boolean() ? '1' : '0',
            SettingTypeEnum::JSON, SettingTypeEnum::ARRAY => json_encode([
                'example' => $this->faker->word(),
                'enabled' => $this->faker->boolean(),
            ], JSON_THROW_ON_ERROR),
        };

        return [
            'key' => 'setting_'.str_replace('-', '_', $this->faker->unique()->slug()),
            'value' => $value,
            'type' => $type->value,
            'group' => $this->faker->optional()->word(),
            'description' => $this->faker->optional()->sentence(),
            'is_public' => $this->faker->boolean(),
            'updated_by' => null,
        ];
    }
}
