<?php

declare(strict_types=1);

use App\Actions\Inventory\UpdateInventoryLayer;
use App\Data\Inventory\UpdateInventoryLayerData;
use App\Models\InventoryLayer;
use App\Models\User;

it('may update inventory layer remaining quantity', function (): void {
    $user = User::factory()->create();
    $layer = InventoryLayer::factory()->create([
        'received_qty' => 100,
        'remaining_qty' => 100,
    ]);

    $action = resolve(UpdateInventoryLayer::class);

    $data = UpdateInventoryLayerData::from([
        'remaining_qty' => 75,
    ]);

    $updatedLayer = $action->handle($layer, $data);

    expect($updatedLayer->remaining_qty)->toBe(75);
});

it('may skip update if remaining_qty is null', function (): void {
    $user = User::factory()->create();
    $layer = InventoryLayer::factory()->create([
        'received_qty' => 100,
        'remaining_qty' => 50,
    ]);

    $action = resolve(UpdateInventoryLayer::class);

    $data = UpdateInventoryLayerData::from([
        'remaining_qty' => null,
    ]);

    $updatedLayer = $action->handle($layer, $data);

    expect($updatedLayer->remaining_qty)->toBe(50);
});
