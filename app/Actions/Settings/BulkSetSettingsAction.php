<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Data\UpdateSettingData;
use App\Models\Setting;
use Illuminate\Support\Collection;

final readonly class BulkSetSettingsAction
{
    /**
     * @param  array<int, UpdateSettingData>  $settings
     * @return Collection<int, Setting>
     */
    public function handle(array $settings, SetSettingAction $setAction): Collection
    {
        /** @var Collection<int, Setting> $updated */
        $updated = collect();

        foreach ($settings as $settingData) {
            $updated->push($setAction->handle($settingData));
        }

        return $updated;
    }
}
