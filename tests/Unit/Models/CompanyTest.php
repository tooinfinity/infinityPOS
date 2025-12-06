<?php

declare(strict_types=1);

use App\Models\BusinessIdentifier;
use App\Models\Company;

test('to array', function (): void {
    $businessId = BusinessIdentifier::factory()->create();
    $company = Company::factory()->create(['business_identifier_id' => $businessId->id]);

    expect(array_keys($company->toArray()))
        ->toBe([
            'name',
            'email',
            'phone',
            'phone_secondary',
            'address',
            'city',
            'state',
            'zip',
            'country',
            'logo',
            'website',
            'description',
            'currency',
            'currency_symbol',
            'timezone',
            'date_format',
            'business_identifier_id',
            'updated_at',
            'created_at',
            'id',
        ]);
});

test('has business identifier relationship', function (): void {
    $businessId = BusinessIdentifier::factory()->create();
    $company = Company::factory()->create(['business_identifier_id' => $businessId->id]);

    expect($company->businessIdentifier->id)->toBe($businessId->id);
});
