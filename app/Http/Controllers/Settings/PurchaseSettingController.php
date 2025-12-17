<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\SaveSettingsAction;
use App\Data\Settings\PurchaseSettingsData;
use App\Settings\PurchaseSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PurchaseSettingController
{
    public function edit(PurchaseSettings $settings): Response
    {
        return Inertia::render('settings/purchase/edit', [
            'purchase' => $settings->toArray(),
        ]);
    }

    public function update(PurchaseSettingsData $data, SaveSettingsAction $save): RedirectResponse
    {
        $save->handle(resolve(PurchaseSettings::class), $data);

        return back();
    }
}
