<?php

declare(strict_types=1);

use App\Models\User;
use App\Settings\PosSettings;

it('renders pos settings edit page', function (): void {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->fromRoute('dashboard')
        ->get(route('settings.pos.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/pos/edit'));
});

it('updates pos settings via DTO', function (): void {
    $user = User::factory()->create();

    $payload = [
        'enable_barcode_scanner' => true,
        'enable_receipt_printer' => true,
        'auto_print_receipt' => false,
        'default_payment_method' => 'cash',
        'enable_customer_display' => false,
        'require_cash_drawer_for_cash_payments' => false,
        'receipt_header' => 'Welcome',
        'receipt_footer' => 'Thanks',
    ];

    $response = $this->actingAs($user)->put(route('settings.pos.update'), $payload);
    $response->assertRedirect();

    $s = resolve(PosSettings::class);
    expect($s->enable_barcode_scanner)->toBeTrue()
        ->and($s->default_payment_method)->toBe('cash')
        ->and($s->receipt_footer)->toBe('Thanks');
});
