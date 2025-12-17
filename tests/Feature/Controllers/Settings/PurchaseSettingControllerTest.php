<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\PurchaseSettings;

it('renders purchase settings edit page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('settings.purchase.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/purchase/edit'));
});

it('updates purchase settings via DTO', function (): void {
    $user = User::factory()->create();

    $payload = [
        'enable_purchase_returns' => true,
        'require_supplier_for_purchase' => true,
        'enable_purchase_notes' => false,
    ];

    $response = $this->actingAs($user)->put(route('settings.purchase.update'), $payload);
    $response->assertRedirect();

    $s = resolve(PurchaseSettings::class);
    expect($s->enable_purchase_returns)->toBeTrue()
        ->and($s->require_supplier_for_purchase)->toBeTrue()
        ->and($s->enable_purchase_notes)->toBeFalse();
});
