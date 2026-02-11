<?php

declare(strict_types=1);

use App\Providers\TelescopeServiceProvider;

arch()->preset()->php();
arch()->preset()->strict()->ignoring([
    TelescopeServiceProvider::class,
    'App\Models',

]);
arch()->preset()->security()->ignoring([
    'assert',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

//
