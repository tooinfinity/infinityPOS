<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use Inertia\Inertia;
use Inertia\Response;

final class PosController
{
    public function index(): Response
    {
        return Inertia::render('pos/index');
    }
}
