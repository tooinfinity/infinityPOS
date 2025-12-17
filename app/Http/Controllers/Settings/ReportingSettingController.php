<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\SaveSettingsAction;
use App\Data\Settings\ReportingSettingsData;
use App\Settings\ReportingSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class ReportingSettingController
{
    public function edit(ReportingSettings $settings): Response
    {
        return Inertia::render('settings/reporting/edit', [
            'reporting' => $settings->toArray(),
        ]);
    }

    public function update(ReportingSettingsData $data, SaveSettingsAction $save): RedirectResponse
    {
        $save->handle(resolve(ReportingSettings::class), $data);

        return back();
    }
}
