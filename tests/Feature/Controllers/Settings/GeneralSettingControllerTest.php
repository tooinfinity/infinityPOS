<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\GeneralSettings;

it('renders general settings edit page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('settings.general.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/general/edit'));
});

it('updates general settings via DTO', function (): void {
    $user = User::factory()->create();

    $payload = [
        'app_name' => 'My Store Updated',
        'app_timezone' => 'UTC',
        'app_locale' => 'en',
        'currency_code' => 'USD',
        'currency_symbol' => '$',
        'currency_position' => 'before',
        'decimal_separator' => '.',
        'thousand_separator' => ',',
        'decimal_places' => 2,
    ];

    $response = $this->actingAs($user)->put(route('settings.general.update'), $payload);
    $response->assertRedirect();

    $s = resolve(GeneralSettings::class);
    expect($s->app_name)->toBe('My Store Updated')
        ->and($s->currency_code)->toBe('USD')
        ->and($s->decimal_places)->toBe(2);
});
