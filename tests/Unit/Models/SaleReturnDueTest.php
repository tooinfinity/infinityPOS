<?php

declare(strict_types=1);

use App\Models\Payment;
use App\Models\SaleReturn;

beforeEach(function (): void {
    $this->saleReturn = SaleReturn::factory()->create([
        'total' => 10000,
    ]);
});

it('returns total as integer', function (): void {
    expect($this->saleReturn->getTotal())->toBe(10000);
});

it('returns zero paid when no payments exist', function (): void {
    expect($this->saleReturn->getPaid())->toBe(0);
});

it('returns sum of all payments', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 2000,
    ]);

    expect($this->saleReturn->getPaid())->toBe(5000);
});

it('calculates due amount correctly', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 6000,
    ]);

    expect($this->saleReturn->getDue())->toBe(4000);
});

it('returns true when sale return is due', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 5000,
    ]);

    expect($this->saleReturn->isDue())->toBeTrue();
});

it('returns false when sale return is fully paid', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 10000,
    ]);

    expect($this->saleReturn->isDue())->toBeFalse();
});

it('returns false when sale return is not overpaid', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 5000,
    ]);

    expect($this->saleReturn->isOverpaid())->toBeFalse();
});

it('returns true when sale return is overpaid', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 15000,
    ]);

    expect($this->saleReturn->isOverpaid())->toBeTrue();
});

it('handles zero total correctly', function (): void {
    $saleReturn = SaleReturn::factory()->create(['total' => 0]);

    expect($saleReturn->getTotal())->toBe(0)
        ->and($saleReturn->getDue())->toBe(0)
        ->and($saleReturn->isDue())->toBeFalse();
});

it('handles exactly paid sale return', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 10000,
    ]);

    expect($this->saleReturn->getDue())->toBe(0)
        ->and($this->saleReturn->isDue())->toBeFalse()
        ->and($this->saleReturn->isOverpaid())->toBeFalse();
});

it('handles multiple payments correctly', function (): void {
    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 4000,
    ]);

    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forSaleReturn($this->saleReturn->id)->create([
        'amount' => 2000,
    ]);

    expect($this->saleReturn->getPaid())->toBe(9000)
        ->and($this->saleReturn->getDue())->toBe(1000)
        ->and($this->saleReturn->isDue())->toBeTrue()
        ->and($this->saleReturn->isOverpaid())->toBeFalse();
});
