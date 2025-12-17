<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\SaveSettingsAction;
use App\Data\Settings\PosSettingsData;
use App\Settings\PosSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PosSettingController
{
    public function edit(PosSettings $settings): Response
    {
        return Inertia::render('settings/pos/edit', [
            'pos' => $settings->toArray(),
        ]);
    }

    public function update(PosSettingsData $data, SaveSettingsAction $save): RedirectResponse
    {
        $save->handle(resolve(PosSettings::class), $data);

        return back();
    }
}
