<?php

declare(strict_types=1);

use App\Models\BusinessIdentifier;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Store;
use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create();

    $client = Client::factory()->create(['created_by' => $user->id])->refresh();

    expect(array_keys($client->toArray()))
        ->toBe([
            'id',
            'name',
            'phone',
            'email',
            'address',
            'balance',
            'is_active',
            'business_identifier_id',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
});

test('client relationships', function (): void {
    $businessIdentifier = BusinessIdentifier::factory()->create();
    $user = User::factory()->create()->refresh();
    $client = Client::factory()->create(['business_identifier_id' => $businessIdentifier->id, 'created_by' => $user->id]);
    $store = Store::factory()->create(['created_by' => $user->id]);
    $sale = Sale::factory()->create(['client_id' => $client->id, 'store_id' => $store->id, 'created_by' => $user->id]);
    $saleReturn = SaleReturn::factory()->create(['client_id' => $client->id, 'store_id' => $store->id, 'created_by' => $user->id]);
    $invoice = Invoice::factory()->create(['sale_id' => $sale->id, 'client_id' => $client->id, 'created_by' => $user->id]);

    $client->update(['updated_by' => $user->id]);

    expect($client->businessIdentifier->id)->toBe($businessIdentifier->id)
        ->and($client->sales->count())->toBe(1)
        ->and($client->sales->first()->id)->toBe($sale->id)
        ->and($client->saleReturns->count())->toBe(1)
        ->and($client->saleReturns->first()->id)->toBe($saleReturn->id)
        ->and($client->invoices->count())->toBe(1)
        ->and($client->invoices->first()->id)->toBe($invoice->id)
        ->and($client->creator->id)->toBe($user->id)
        ->and($client->updater->id)->toBe($user->id);
});
