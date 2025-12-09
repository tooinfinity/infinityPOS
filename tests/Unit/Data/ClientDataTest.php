<?php

declare(strict_types=1);

use App\Data\BusinessIdentifierData;
use App\Data\ClientData;
use App\Data\InvoiceData;
use App\Data\SaleData;
use App\Data\SaleReturnData;
use App\Data\UserData;
use App\Models\BusinessIdentifier;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\LaravelData\DataCollection;

it('transforms a client model into ClientData', function () {

    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $identifier = BusinessIdentifier::factory()->create();

    /** @var Client $client */
    $client = Client::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($identifier, 'businessIdentifier')
        ->has(Sale::factory()->count(2), 'sales')
        ->has(SaleReturn::factory()->count(1), 'saleReturns')
        ->has(Invoice::factory()->count(3), 'invoices')
        ->create([
            'name' => 'John Doe',
            'phone' => '123456789',
            'email' => 'john@example.com',
            'address' => 'Main street 1',
            'balance' => 4500,
            'is_active' => true,
        ]);

    $data = ClientData::fromModel(
        $client->load([
            'creator',
            'updater',
            'businessIdentifier',
            'sales',
            'saleReturns',
            'invoices',
        ])
    );

    expect($data)
        ->toBeInstanceOf(ClientData::class)
        ->id->toBe($client->id)
        ->name->toBe('John Doe')
        ->phone->toBe('123456789')
        ->email->toBe('john@example.com')
        ->address->toBe('Main street 1')
        ->balance->toBe(4500)
        ->is_active->toBeTrue()
        ->and($data->businessIdentifier->resolve())
        ->toBeInstanceOf(BusinessIdentifierData::class)
        ->id->toBe($identifier->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id);

    $sales = $data->sales->resolve();

    if ($sales instanceof DataCollection) {
        expect($sales)->toBeInstanceOf(DataCollection::class)
            ->and($sales->count())->toBe(2);

        foreach ($sales->all() as $s) {
            expect($s)->toBeInstanceOf(SaleData::class);
        }
    } else {
        expect($sales)->toBeInstanceOf(Collection::class)
            ->and($sales->count())->toBe(2);

        foreach ($sales as $s) {
            expect($s)->toBeInstanceOf(SaleData::class);
        }
    }

    $returns = $data->saleReturns->resolve();

    if ($returns instanceof DataCollection) {
        expect($returns)->toBeInstanceOf(DataCollection::class)
            ->and($returns->count())->toBe(1);

        foreach ($returns->all() as $r) {
            expect($r)->toBeInstanceOf(SaleReturnData::class);
        }
    } else {
        expect($returns)->toBeInstanceOf(Collection::class)
            ->and($returns->count())->toBe(1);

        foreach ($returns as $r) {
            expect($r)->toBeInstanceOf(SaleReturnData::class);
        }
    }

    // Invoices --------------------------------------------------------
    $invoices = $data->invoices->resolve();

    if ($invoices instanceof DataCollection) {
        expect($invoices->count())->toBe(3);

        foreach ($invoices->all() as $inv) {
            expect($inv)->toBeInstanceOf(InvoiceData::class);
        }
    } else {
        expect($invoices)->toBeInstanceOf(Collection::class)
            ->and($invoices->count())->toBe(3);

        foreach ($invoices as $inv) {
            expect($inv)->toBeInstanceOf(InvoiceData::class);
        }
    }

    expect($data->created_at->toDateTimeString())
        ->toBe($client->created_at->toDateTimeString())
        ->and($data->updated_at->toDateTimeString())
        ->toBe($client->updated_at->toDateTimeString());

});
