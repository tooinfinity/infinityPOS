<?php

declare(strict_types=1);

use App\Actions\Taxes\CreateTax;
use App\Data\Taxes\CreateTaxData;
use App\Enums\TaxTypeEnum;
use App\Models\Tax;
use App\Models\User;

it('may create a tax', function (): void {
    $user = User::factory()->create();
    $action = resolve(CreateTax::class);

    $data = CreateTaxData::from([
        'name' => 'VAT',
        'rate' => 2000,
        'tax_type' => TaxTypeEnum::PERCENTAGE,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $tax = $action->handle($data);

    expect($tax)->toBeInstanceOf(Tax::class)
        ->and($tax->name)->toBe('VAT')
        ->and($tax->rate)->toBe(2000)
        ->and($tax->tax_type)->toBe(TaxTypeEnum::PERCENTAGE)
        ->and($tax->is_active)->toBeTrue()
        ->and($tax->created_by)->toBe($user->id);
});
