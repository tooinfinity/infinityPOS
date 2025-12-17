<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\ReportingSettings;

it('renders reporting settings edit page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('settings.reporting.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/reporting/edit'));
});

it('updates reporting settings via DTO', function (): void {
    $user = User::factory()->create();

    $payload = [
        'default_date_range' => 'last_7_days',
        'enable_profit_tracking' => true,
        'enable_export_reports' => true,
    ];

    $response = $this->actingAs($user)->put(route('settings.reporting.update'), $payload);
    $response->assertRedirect();

    $s = resolve(ReportingSettings::class);
    expect($s->default_date_range)->toBe('last_7_days')
        ->and($s->enable_profit_tracking)->toBeTrue()
        ->and($s->enable_export_reports)->toBeTrue();
});
