<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\SaveSettingsAction;
use App\Data\Settings\GeneralSettingsData;
use App\Settings\GeneralSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class GeneralSettingController
{
    public function edit(GeneralSettings $settings): Response
    {
        return Inertia::render('settings/general/edit', [
            'general' => $settings->toArray(),
        ]);
    }

    public function update(GeneralSettingsData $data, SaveSettingsAction $save): RedirectResponse
    {
        $save->handle(resolve(GeneralSettings::class), $data);

        return back();
    }
}
