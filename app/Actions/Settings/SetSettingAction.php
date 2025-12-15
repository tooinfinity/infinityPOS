<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Data\UpdateSettingData;
use App\Models\Setting;
use JsonException;

final readonly class SetSettingAction
{
    /**
     * @throws JsonException
     */
    public function handle(UpdateSettingData $data): Setting
    {
        /** @var Setting $setting */
        $setting = Setting::query()->where('key', $data->key)->firstOrFail();

        $value = $setting->type->castValue($data->value);

        $setting->update([
            'value' => $value,
            'updated_by' => auth()->id(),
        ]);

        return $setting;
    }
}
