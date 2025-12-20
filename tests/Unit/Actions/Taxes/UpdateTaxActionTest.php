<?php

declare(strict_types=1);

use App\Actions\Taxes\UpdateTax;
use App\Data\Taxes\UpdateTaxData;
use App\Enums\TaxTypeEnum;
use App\Models\Tax;
use App\Models\User;

it('may update a tax', function (): void {
    $user = User::factory()->create();
    $tax = Tax::factory()->create([
        'name' => 'Old Tax',
        'rate' => 1000,
        'tax_type' => TaxTypeEnum::PERCENTAGE,
        'is_active' => true,
        'created_by' => $user->id,
    ]);

    $user2 = User::factory()->create();
    $action = resolve(UpdateTax::class);

    $data = UpdateTaxData::from([
        'name' => 'Updated Tax',
        'rate' => 1500,
        'tax_type' => TaxTypeEnum::FIXED,
        'is_active' => false,
        'updated_by' => $user2->id,
    ]);

    $action->handle($tax, $data);

    expect($tax->refresh()->name)->toBe('Updated Tax')
        ->and($tax->rate)->toBe(1500)
        ->and($tax->tax_type)->toBe(TaxTypeEnum::FIXED)
        ->and($tax->is_active)->toBeFalse()
        ->and($tax->updated_by)->toBe($user2->id);
});
