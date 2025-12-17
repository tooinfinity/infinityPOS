<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\SaveSettingsAction;
use App\Data\Settings\SalesSettingsData;
use App\Settings\SalesSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class SalesSettingController
{
    public function edit(SalesSettings $settings): Response
    {
        return Inertia::render('settings/sales/edit', [
            'sales' => $settings->toArray(),
        ]);
    }

    public function update(SalesSettingsData $data, SaveSettingsAction $save): RedirectResponse
    {
        $save->handle(resolve(SalesSettings::class), $data);

        return back();
    }
}
