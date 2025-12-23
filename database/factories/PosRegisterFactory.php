<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PosRegister;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PosRegister> */
final class PosRegisterFactory extends Factory
{
    protected $model = PosRegister::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'moneybox_id' => null,
            'name' => 'Register '.Str::upper(Str::random(4)),
            'device_id' => (string) Str::uuid(),
            'is_active' => true,
            'draft_sale_id' => null,
            'configured_at' => null,
            'created_by' => User::factory(),
            'updated_by' => null,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
