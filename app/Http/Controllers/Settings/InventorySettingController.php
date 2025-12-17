<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\SaveSettingsAction;
use App\Data\Settings\InventorySettingsData;
use App\Settings\InventorySettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class InventorySettingController
{
    public function edit(InventorySettings $settings): Response
    {
        return Inertia::render('settings/inventory/edit', [
            'inventory' => $settings->toArray(),
        ]);
    }

    public function update(InventorySettingsData $data, SaveSettingsAction $save): RedirectResponse
    {
        $save->handle(resolve(InventorySettings::class), $data);

        return back();
    }
}
