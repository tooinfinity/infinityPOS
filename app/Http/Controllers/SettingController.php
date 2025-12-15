<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Settings\BulkSetSettingsAction;
use App\Actions\Settings\SetSettingAction;
use App\Data\BulkUpdateSettingsData;
use App\Queries\Settings\GetAllSettingsQuery;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final readonly class SettingController
{
    public function index(GetAllSettingsQuery $query): Response
    {
        return Inertia::render('settings/index', [
            'groupedSettings' => $query->handleGrouped(),
        ]);
    }

    public function update(BulkUpdateSettingsData $data, BulkSetSettingsAction $bulkSetAction, SetSettingAction $setAction): RedirectResponse
    {
        $bulkSetAction->handle($data->settings, $setAction);

        return back();
    }
}
