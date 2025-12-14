<?php

declare(strict_types=1);

use App\Models\Invoice;
use App\Models\Payment;

beforeEach(function (): void {
    $this->invoice = Invoice::factory()->create([
        'total' => 10000,
    ]);
});

it('returns total as integer', function (): void {
    expect($this->invoice->getTotal())->toBe(10000);
});

it('returns zero paid when no payments exist', function (): void {
    expect($this->invoice->getPaid())->toBe(0);
});

it('returns sum of all payments', function (): void {
    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 3000,
    ]);

    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 2000,
    ]);

    expect($this->invoice->getPaid())->toBe(5000);
});

it('calculates due amount correctly', function (): void {
    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 6000,
    ]);

    expect($this->invoice->getDue())->toBe(4000);
});

it('returns true when invoice is due', function (): void {
    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 5000,
    ]);

    expect($this->invoice->isDue())->toBeTrue();
});

it('returns false when invoice is fully paid', function (): void {
    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 10000,
    ]);

    expect($this->invoice->isDue())->toBeFalse();
});

it('returns false when invoice is overpaid', function (): void {
    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 5000,
    ]);

    expect($this->invoice->isOverpaid())->toBeFalse();
});

it('returns true when invoice is overpaid', function (): void {
    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 15000,
    ]);

    expect($this->invoice->isOverpaid())->toBeTrue();
});

it('handles zero total correctly', function (): void {
    $invoice = Invoice::factory()->create(['total' => 0]);

    expect($invoice->getTotal())->toBe(0)
        ->and($invoice->getDue())->toBe(0)
        ->and($invoice->isDue())->toBeFalse();
});

it('handles exactly paid invoice', function (): void {
    Payment::factory()->forInvoice($this->invoice->id)->create([
        'amount' => 10000,
    ]);

    expect($this->invoice->getDue())->toBe(0)
        ->and($this->invoice->isDue())->toBeFalse()
        ->and($this->invoice->isOverpaid())->toBeFalse();
});
