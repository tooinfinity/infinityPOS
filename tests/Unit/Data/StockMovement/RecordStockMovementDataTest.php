<?php

declare(strict_types=1);

use App\Data\StockMovement\RecordStockMovementData;
use App\Enums\StockMovementTypeEnum;

it('may be created with required fields', function (): void {
    $data = new RecordStockMovementData(
        warehouse_id: 1,
        product_id: 1,
        type: StockMovementTypeEnum::In,
        quantity: 100,
        previous_quantity: 0,
        current_quantity: 100,
        reference_type: 'purchase_order',
        reference_id: 1,
        batch_id: null,
        user_id: null,
        note: null,
        created_at: null,
    );

    expect($data)
        ->warehouse_id->toBe(1)
        ->product_id->toBe(1)
        ->type->toBe(StockMovementTypeEnum::In)
        ->quantity->toBe(100)
        ->reference_type->toBe('purchase_order');
});

it('may be created with all fields', function (): void {
    $createdAt = Illuminate\Support\Facades\Date::now();

    $data = new RecordStockMovementData(
        warehouse_id: 1,
        product_id: 1,
        type: StockMovementTypeEnum::Transfer,
        quantity: 50,
        previous_quantity: 100,
        current_quantity: 50,
        reference_type: App\Models\StockTransfer::class,
        reference_id: 5,
        batch_id: 10,
        user_id: 3,
        note: 'Test movement',
        created_at: $createdAt,
    );

    expect($data)
        ->batch_id->toBe(10)
        ->user_id->toBe(3)
        ->note->toBe('Test movement')
        ->created_at->toBe($createdAt);
});
