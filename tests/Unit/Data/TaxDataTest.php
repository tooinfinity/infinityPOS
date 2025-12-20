<?php

declare(strict_types=1);

use App\Data\Taxes\TaxData;
use App\Data\Users\UserData;
use App\Models\Tax;
use App\Models\User;

it('transforms a tax model into TaxData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    /** @var Tax $tax */
    $tax = Tax::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->create([
            'name' => 'VAT',
            'tax_type' => App\Enums\TaxTypeEnum::PERCENTAGE->value,
            'rate' => 17,
            'is_active' => true,
        ]);

    $data = TaxData::from(
        $tax->load(['creator', 'updater'])
    );

    expect($data)
        ->toBeInstanceOf(TaxData::class)
        ->id->toBe($tax->id)
        ->name->toBe('VAT')
        ->tax_type->toBe(App\Enums\TaxTypeEnum::PERCENTAGE)
        ->rate->toBe(17)
        ->is_active->toBeTrue()
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($tax->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($tax->updated_at->toDateTimeString());
});
