<?php

declare(strict_types=1);

use App\Actions\Sales\UpdateSaleItem;
use App\Data\Sales\UpdateSaleItemData;
use App\Models\SaleItem;

it('may update a sale item', function (): void {
    $saleItem = SaleItem::factory()->create([
        'quantity' => 5,
        'price' => 10000,
    ]);
    $action = resolve(UpdateSaleItem::class);

    $data = UpdateSaleItemData::from([
        'quantity' => 10,
        'price' => 12000,
        'cost' => null,
        'discount' => null,
        'tax_amount' => null,
        'total' => 120000,
        'batch_number' => null,
        'expiry_date' => null,
    ]);

    $updatedItem = $action->handle($saleItem, $data);

    expect($updatedItem->quantity)->toBe(10)
        ->and($updatedItem->price)->toBe(12000)
        ->and($updatedItem->total)->toBe(120000);
});
