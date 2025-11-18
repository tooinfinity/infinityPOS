<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLanguageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

final class LanguageController
{
    public function store(CreateLanguageRequest $request): RedirectResponse
    {
        /** @var string $locale */
        $locale = $request->validated('locale');

        session(['locale' => $locale]);

        app()->setLocale($locale);

        Cookie::queue('locale', $locale, 525600);

        return back();
    }
}
