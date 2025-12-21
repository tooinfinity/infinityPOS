<?php

declare(strict_types=1);

use App\Actions\Sales\DeleteSale;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;

it('may delete a pending sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $user->id,
    ]);
    SaleItem::factory()->create(['sale_id' => $sale->id]);

    $action = resolve(DeleteSale::class);

    $result = $action->handle($sale);

    expect($result)->toBeTrue()
        ->and(Sale::query()->find($sale->id))->toBeNull()
        ->and(SaleItem::query()->where('sale_id', $sale->id)->count())->toBe(0);
});

it('cannot delete a completed sale', function (): void {
    $user = User::factory()->create();
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::COMPLETED,
        'created_by' => $user->id,
    ]);

    $action = resolve(DeleteSale::class);

    $action->handle($sale);
})->throws(InvalidArgumentException::class, 'Cannot delete a completed sale. Please cancel it first.');
