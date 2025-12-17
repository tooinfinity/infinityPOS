<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\SalesSettings;

it('renders sales settings edit page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('settings.sales.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/sales/edit'));
});

it('updates sales settings via DTO', function (): void {
    $user = User::factory()->create();

    $payload = [
        'enable_discounts' => true,
        'max_discount_percentage' => 25,
        'require_customer_for_sale' => false,
        'enable_sale_notes' => true,
        'enable_tax_calculation' => true,
        'tax_inclusive' => false,
    ];

    $response = $this->actingAs($user)->put(route('settings.sales.update'), $payload);
    $response->assertRedirect();

    $s = resolve(SalesSettings::class);
    expect($s->enable_discounts)->toBeTrue()
        ->and($s->max_discount_percentage)->toBe(25)
        ->and($s->tax_inclusive)->toBeFalse();
});
