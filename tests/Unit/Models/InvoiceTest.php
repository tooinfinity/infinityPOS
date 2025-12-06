<?php

declare(strict_types=1);

use App\Enums\PaymentTypeEnum;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create([
        'created_by' => $user->id,
        'store_id' => $store->id,
    ]);

    $invoice = Invoice::factory()->create([
        'created_by' => $user->id,
        'sale_id' => $sale->id,
    ])->refresh();

    expect(array_keys($invoice->toArray()))
        ->toBe([
            'id',
            'reference',
            'issued_at',
            'due_at',
            'paid_at',
            'subtotal',
            'discount',
            'tax',
            'total',
            'paid',
            'status',
            'notes',
            'sale_id',
            'client_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('invoice relationships', function (): void {
    $user = User::factory()->create()->refresh();
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create(['created_by' => $user->id, 'store_id' => $store->id]);
    $client = Client::factory()->create(['created_by' => $user->id]);
    $payment = Payment::factory()->create(['type' => PaymentTypeEnum::SALE->value, 'related_id' => $sale->id, 'created_by' => $user->id]);
    $invoice = Invoice::factory()->create([
        'created_by' => $user->id,
        'sale_id' => $sale->id,
        'client_id' => $client->id,
    ]);
    $invoice->update(['updated_by' => $user->id]);

    expect($invoice->client->id)->toBe($client->id)
        ->and($invoice->creator->id)->toBe($user->id)
        ->and($invoice->sale->id)->toBe($sale->id)
        ->and($invoice->client->id)->toBe($client->id)
        ->and($invoice->updater->id)->toBe($user->id)
        ->and($invoice->payments->count())->toBe(1);
});
