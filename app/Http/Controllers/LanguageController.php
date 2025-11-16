<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLanguageRequest;
use Illuminate\Http\RedirectResponse;

final class LanguageController
{
    public function store(CreateLanguageRequest $request): RedirectResponse
    {

        session(['locale' => $request->validated('locale')]);

        return back();
    }
}
