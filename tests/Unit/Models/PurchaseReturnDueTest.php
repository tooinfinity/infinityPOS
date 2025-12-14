<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\PurchaseReturn;

beforeEach(function (): void {
    $this->purchaseReturn = PurchaseReturn::factory()->create([
        'total' => 10000,
    ]);
});

it('returns total as integer', function (): void {
    expect($this->purchaseReturn->getTotal())->toBe(10000);
});

it('returns zero paid when no payments exist', function (): void {
    expect($this->purchaseReturn->getPaid())->toBe(0);
});

it('returns sum of all payments', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 2000,
    ]);

    expect($this->purchaseReturn->getPaid())->toBe(5000);
});

it('calculates due amount correctly', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 6000,
    ]);

    expect($this->purchaseReturn->getDue())->toBe(4000);
});

it('returns true when purchase return is due', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 5000,
    ]);

    expect($this->purchaseReturn->isDue())->toBeTrue();
});

it('returns false when purchase return is fully refunded', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 10000,
    ]);

    expect($this->purchaseReturn->isDue())->toBeFalse();
});

it('returns false when purchase return is not overpaid', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 5000,
    ]);

    expect($this->purchaseReturn->isOverpaid())->toBeFalse();
});

it('returns true when purchase return is overpaid', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 15000,
    ]);

    expect($this->purchaseReturn->isOverpaid())->toBeTrue();
});

it('handles zero total correctly', function (): void {
    $purchaseReturn = PurchaseReturn::factory()->create(['total' => 0]);

    expect($purchaseReturn->getTotal())->toBe(0)
        ->and($purchaseReturn->getDue())->toBe(0)
        ->and($purchaseReturn->isDue())->toBeFalse();
});

it('handles exactly refunded purchase return', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 10000,
    ]);

    expect($this->purchaseReturn->getDue())->toBe(0)
        ->and($this->purchaseReturn->isDue())->toBeFalse()
        ->and($this->purchaseReturn->isOverpaid())->toBeFalse();
});

it('handles multiple refund payments correctly', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 4000,
    ]);

    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 2000,
    ]);

    expect($this->purchaseReturn->getPaid())->toBe(9000)
        ->and($this->purchaseReturn->getDue())->toBe(1000)
        ->and($this->purchaseReturn->isDue())->toBeTrue()
        ->and($this->purchaseReturn->isOverpaid())->toBeFalse();
});

it('correctly calculates when supplier owes us money', function (): void {
    Payment::factory()->forPurchaseReturn($this->purchaseReturn->id)->create([
        'amount' => 0,
    ]);

    expect($this->purchaseReturn->getDue())->toBe(10000)
        ->and($this->purchaseReturn->isDue())->toBeTrue()
        ->and($this->purchaseReturn->isOverpaid())->toBeFalse();
});
