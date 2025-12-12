<?php

declare(strict_types=1);

use App\Data\BusinessIdentifierData;
use App\Data\CompanyData;
use App\Models\BusinessIdentifier;
use App\Models\Company;

it('transforms a company model into CompanyData', function (): void {

    $identifier = BusinessIdentifier::factory()->create();

    /** @var Company $company */
    $company = Company::factory()
        ->for($identifier, 'businessIdentifier')
        ->create([
            'name' => 'Tooinfinity Inc.',
            'email' => 'info@tooinfinity.test',
            'phone' => '123456',
            'phone_secondary' => '7891011',
            'address' => 'Main Road',
            'city' => 'Algiers',
            'state' => 'DZ-01',
            'zip' => '16000',
            'country' => 'Algeria',
            'logo' => '/logos/company.png',
            'website' => 'https://tooinfinity.test',
            'description' => 'A test company',
            'currency' => 'DZD',
            'currency_symbol' => 'DA',
            'timezone' => 'Africa/Algiers',
            'date_format' => 'Y-m-d',
        ]);

    $data = CompanyData::from(
        $company->load([
            'businessIdentifier',
        ])
    );

    expect($data)
        ->toBeInstanceOf(CompanyData::class)
        ->id->toBe($company->id)
        ->name->toBe('Tooinfinity Inc.')
        ->email->toBe('info@tooinfinity.test')
        ->phone->toBe('123456')
        ->phone_secondary->toBe('7891011')
        ->address->toBe('Main Road')
        ->city->toBe('Algiers')
        ->state->toBe('DZ-01')
        ->zip->toBe('16000')
        ->country->toBe('Algeria')
        ->logo->toBe('/logos/company.png')
        ->website->toBe('https://tooinfinity.test')
        ->description->toBe('A test company')
        ->currency->toBe('DZD')
        ->currency_symbol->toBe('DA')
        ->timezone->toBe('Africa/Algiers')
        ->date_format->toBe('Y-m-d')
        ->and($data->businessIdentifier->resolve())
        ->toBeInstanceOf(BusinessIdentifierData::class)
        ->id->toBe($identifier->id)
        ->and($data->created_at)
        ->toBe($company->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($company->updated_at->toDateTimeString());
});
