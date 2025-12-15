<?php

declare(strict_types=1);

namespace App\Queries\Settings;

use App\Models\Setting;
use Illuminate\Support\Collection;

final readonly class GetAllSettingsQuery
{
    /**
     * @return Collection<string, mixed>
     */
    public function handle(): Collection
    {
        return Setting::all()
            ->mapWithKeys(function (Setting $setting): array {
                /** @var mixed $value */
                $value = $setting->value;

                return [$setting->key => $value];
            });
    }

    /**
     * @return Collection<int|string, \Illuminate\Database\Eloquent\Collection<string, Setting>>
     */
    public function handleGrouped(): Collection
    {
        return Setting::all()
            ->groupBy('group')
            ->map(fn (Collection $settings) => $settings->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting]));
    }
}
