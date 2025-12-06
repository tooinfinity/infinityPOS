<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\BusinessIdentifier;
use App\Models\Client;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;

test('to array', function (): void {
    $businessId = BusinessIdentifier::factory()->create()->refresh();

    expect(array_keys($businessId->toArray()))
        ->toBe([
            'id',
            'article',
            'nif',
            'nis',
            'rc',
            'rib',
            'created_at',
            'updated_at',
        ]);
});

test('business identifier relationships', function (): void {
    $businessId = BusinessIdentifier::factory()->create();
    $user = User::factory()->create()->refresh();
    $company = Company::factory()->create(['business_identifier_id' => $businessId->id]);
    $client = Client::factory()->create(['created_by' => $user->id, 'business_identifier_id' => $businessId->id]);
    $supplier = Supplier::factory()->create(['created_by' => $user->id, 'business_identifier_id' => $businessId->id]);

    expect($businessId->company->id)->toBe($company->id)
        ->and($businessId->client->id)->toBe($client->id)
        ->and($businessId->supplier->id)->toBe($supplier->id);
});
