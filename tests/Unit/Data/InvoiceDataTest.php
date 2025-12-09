<?php

declare(strict_types=1);

use App\Data\ClientData;
use App\Data\InvoiceData;
use App\Data\PaymentData;
use App\Data\SaleData;
use App\Data\UserData;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

it('transforms an invoice model into InvoiceData', function () {

    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $client = Client::factory()->create();
    $sale = Sale::factory()->create();

    /** @var Invoice $invoice */
    $invoice = Invoice::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($client, 'client')
        ->for($sale, 'sale')
        ->has(Payment::factory(
            ['type' => 'sale', 'related_id' => $sale->id]
        )->count(3), 'payments')
        ->create([
            'reference' => 'INV-2025-001',
            'issued_at' => now(),
            'due_at' => now()->addDays(7),
            'paid_at' => null,
            'subtotal' => 10000,
            'discount' => 500,
            'tax' => 200,
            'total' => 9700,
            'paid' => 5000,
            'status' => 'partial',
            'notes' => 'Pay remaining before due date.',
        ]);

    $invoice->load([
        'creator',
        'updater',
        'client',
        'sale',
        'payments',
    ]);

    $data = InvoiceData::fromModel($invoice);

    expect($data)
        ->toBeInstanceOf(InvoiceData::class)
        ->id->toBe($invoice->id)
        ->reference->toBe('INV-2025-001')
        ->subtotal->toBe(10000)
        ->discount->toBe(500)
        ->tax->toBe(200)
        ->total->toBe(9700)
        ->paid->toBe(5000)
        ->status->toBe('partial')
        ->notes->toBe('Pay remaining before due date.')
        ->and($data->sale->resolve())
        ->toBeInstanceOf(SaleData::class)
        ->id->toBe($sale->id)
        ->and($data->client->resolve())
        ->toBeInstanceOf(ClientData::class)
        ->id->toBe($client->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id);

    $payments = $data->payments->resolve();

    if ($payments instanceof DataCollection) {
        expect($payments)->toBeInstanceOf(DataCollection::class)
            ->and($payments->count())->toBe(3);

        foreach ($payments->all() as $payment) {
            expect($payment)->toBeInstanceOf(PaymentData::class);
        }
    } else {
        expect($payments)->toBeInstanceOf(Collection::class)
            ->and($payments->count())->toBe(3);

        foreach ($payments as $payment) {
            expect($payment)->toBeInstanceOf(PaymentData::class);
        }
    }

    expect($data->issued_at->toDateTimeString())
        ->toBe($invoice->issued_at->toDateTimeString())
        ->and($data->due_at?->toDateTimeString())
        ->toBe($invoice->due_at?->toDateTimeString())
        ->and($data->paid_at?->toDateTimeString())
        ->toBe($invoice->paid_at?->toDateTimeString())
        ->and($data->created_at->toDateTimeString())
        ->toBe($invoice->created_at->toDateTimeString())
        ->and($data->updated_at->toDateTimeString())
        ->toBe($invoice->updated_at->toDateTimeString());
});
