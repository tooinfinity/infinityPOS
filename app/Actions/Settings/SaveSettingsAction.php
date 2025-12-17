<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use Spatie\LaravelData\Data;
use Spatie\LaravelSettings\Settings;

final class SaveSettingsAction
{
    public function handle(Settings $settings, Data $dto): void
    {
        /** @var array<string, mixed> $payload */
        $payload = $dto->toArray();
        foreach ($payload as $key => $value) {
            if (property_exists($settings, $key)) {
                $settings->$key = $value;
            }
        }

        $settings->save();
    }
}
