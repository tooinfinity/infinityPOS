<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\Sale;

beforeEach(function (): void {
    $this->sale = Sale::factory()->create([
        'total' => 10000,
    ]);
});

it('returns total as integer', function (): void {
    expect($this->sale->getTotal())->toBe(10000);
});

it('returns zero paid when no payments exist', function (): void {
    expect($this->sale->getPaid())->toBe(0);
});

it('returns sum of all payments', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 2000,
    ]);

    expect($this->sale->getPaid())->toBe(5000);
});

it('calculates due amount correctly', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 6000,
    ]);

    expect($this->sale->getDue())->toBe(4000);
});

it('returns true when sale is due', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 5000,
    ]);

    expect($this->sale->isDue())->toBeTrue();
});

it('returns false when sale is fully paid', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 10000,
    ]);

    expect($this->sale->isDue())->toBeFalse();
});

it('returns false when sale is not overpaid', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 5000,
    ]);

    expect($this->sale->isOverpaid())->toBeFalse();
});

it('returns true when sale is overpaid', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 15000,
    ]);

    expect($this->sale->isOverpaid())->toBeTrue();
});

it('handles zero total correctly', function (): void {
    $sale = Sale::factory()->create(['total' => 0]);

    expect($sale->getTotal())->toBe(0)
        ->and($sale->getDue())->toBe(0)
        ->and($sale->isDue())->toBeFalse();
});

it('handles exactly paid sale', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 10000,
    ]);

    expect($this->sale->getDue())->toBe(0)
        ->and($this->sale->isDue())->toBeFalse()
        ->and($this->sale->isOverpaid())->toBeFalse();
});

it('handles multiple payments correctly', function (): void {
    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 4000,
    ]);

    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forSale($this->sale->id)->create([
        'amount' => 2000,
    ]);

    expect($this->sale->getPaid())->toBe(9000)
        ->and($this->sale->getDue())->toBe(1000)
        ->and($this->sale->isDue())->toBeTrue()
        ->and($this->sale->isOverpaid())->toBeFalse();
});
