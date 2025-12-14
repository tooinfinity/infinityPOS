<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\Purchase;

beforeEach(function (): void {
    $this->purchase = Purchase::factory()->create([
        'total' => 10000,
    ]);
});

it('returns total as integer', function (): void {
    expect($this->purchase->getTotal())->toBe(10000);
});

it('returns zero paid when no payments exist', function (): void {
    expect($this->purchase->getPaid())->toBe(0);
});

it('returns sum of all payments', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 2000,
    ]);

    expect($this->purchase->getPaid())->toBe(5000);
});

it('calculates due amount correctly', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 6000,
    ]);

    expect($this->purchase->getDue())->toBe(4000);
});

it('returns true when purchase is due', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 5000,
    ]);

    expect($this->purchase->isDue())->toBeTrue();
});

it('returns false when purchase is fully paid', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 10000,
    ]);

    expect($this->purchase->isDue())->toBeFalse();
});

it('returns false when purchase is not overpaid', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 5000,
    ]);

    expect($this->purchase->isOverpaid())->toBeFalse();
});

it('returns true when purchase is overpaid', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 15000,
    ]);

    expect($this->purchase->isOverpaid())->toBeTrue();
});

it('handles zero total correctly', function (): void {
    $purchase = Purchase::factory()->create(['total' => 0]);

    expect($purchase->getTotal())->toBe(0)
        ->and($purchase->getDue())->toBe(0)
        ->and($purchase->isDue())->toBeFalse();
});

it('handles exactly paid purchase', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 10000,
    ]);

    expect($this->purchase->getDue())->toBe(0)
        ->and($this->purchase->isDue())->toBeFalse()
        ->and($this->purchase->isOverpaid())->toBeFalse();
});

it('handles multiple payments correctly', function (): void {
    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 4000,
    ]);

    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forPurchase($this->purchase->id)->create([
        'amount' => 2000,
    ]);

    expect($this->purchase->getPaid())->toBe(9000)
        ->and($this->purchase->getDue())->toBe(1000)
        ->and($this->purchase->isDue())->toBeTrue()
        ->and($this->purchase->isOverpaid())->toBeFalse();
});
