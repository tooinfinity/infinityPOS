<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\InventorySettings;

it('renders inventory settings edit page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('settings.inventory.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/inventory/edit'));
});

it('updates inventory settings via DTO', function (): void {
    $user = User::factory()->create();

    $payload = [
        'enable_batch_tracking' => true,
        'enable_expiry_tracking' => true,
        'low_stock_threshold' => 5,
        'enable_stock_alerts' => true,
        'auto_deduct_stock' => true,
    ];

    $response = $this->actingAs($user)->put(route('settings.inventory.update'), $payload);
    $response->assertRedirect();

    $s = resolve(InventorySettings::class);
    expect($s->enable_batch_tracking)->toBeTrue()
        ->and($s->low_stock_threshold)->toBe(5)
        ->and($s->auto_deduct_stock)->toBeTrue();
});
