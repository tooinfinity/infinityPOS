<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Data\CreateLanguageData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

final class LanguageController
{
    public function store(CreateLanguageData $data): RedirectResponse
    {
        session(['locale' => $data->locale]);

        app()->setLocale($data->locale);

        Cookie::queue('locale', $data->locale, 525600);

        return back();
    }
}
