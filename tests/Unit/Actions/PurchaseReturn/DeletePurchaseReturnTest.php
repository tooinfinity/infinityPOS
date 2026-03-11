<?php

declare(strict_types=1);

use App\Actions\PurchaseReturn\DeletePurchaseReturn;
use App\Enums\ReturnStatusEnum;
use App\Models\PurchaseReturn;

it('may delete a pending purchase return', function (): void {
    $return = PurchaseReturn::factory()->create([
        'status' => ReturnStatusEnum::Pending,
    ]);

    $action = resolve(DeletePurchaseReturn::class);

    $result = $action->handle($return);

    expect($result)->toBeTrue()
        ->and(PurchaseReturn::query()->where('id', $return->id)->exists())->toBeFalse();
});

it('throws exception when deleting non-pending return', function (): void {
    $return = PurchaseReturn::factory()->create([
        'status' => ReturnStatusEnum::Completed,
    ]);

    $action = resolve(DeletePurchaseReturn::class);

    expect(fn () => $action->handle($return))->toThrow(App\Exceptions\InvalidOperationException::class);
});
