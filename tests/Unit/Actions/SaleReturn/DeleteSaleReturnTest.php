<?php

declare(strict_types=1);

use App\Actions\SaleReturn\DeleteSaleReturn;
use App\Enums\ReturnStatusEnum;
use App\Models\SaleReturn;

it('may delete a pending sale return', function (): void {
    $return = SaleReturn::factory()->create([
        'status' => ReturnStatusEnum::Pending,
    ]);

    $action = resolve(DeleteSaleReturn::class);

    $result = $action->handle($return);

    expect($result)->toBeTrue()
        ->and(SaleReturn::query()->where('id', $return->id)->exists())->toBeFalse();
});

it('throws exception when deleting non-pending return', function (): void {
    $return = SaleReturn::factory()->create([
        'status' => ReturnStatusEnum::Completed,
    ]);

    $action = resolve(DeleteSaleReturn::class);

    expect(fn () => $action->handle($return))->toThrow(App\Exceptions\InvalidOperationException::class);
});
