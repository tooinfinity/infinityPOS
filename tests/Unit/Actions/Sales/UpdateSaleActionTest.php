<?php

declare(strict_types=1);

use App\Actions\Sales\UpdateSale;
use App\Data\Sales\UpdateSaleData;
use App\Models\Sale;
use App\Models\User;

it('may update a sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create();
    $action = resolve(UpdateSale::class);

    $data = UpdateSaleData::from([
        'reference' => 'SALE-UPDATED',
        'client_id' => null,
        'store_id' => null,
        'subtotal' => null,
        'discount' => null,
        'tax' => null,
        'total' => null,
        'notes' => 'Updated notes',
        'updated_by' => $user->id,
    ]);

    $updatedSale = $action->handle($sale, $data);

    expect($updatedSale->reference)->toBe('SALE-UPDATED')
        ->and($updatedSale->notes)->toBe('Updated notes')
        ->and($updatedSale->updated_by)->toBe($user->id);
});
