<?php

declare(strict_types=1);

use App\Data\StockTransferData;
use App\Data\Stores\StoreData;
use App\Data\Users\UserData;
use App\Enums\StockTransferStatusEnum;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;

it('transforms a stock transfer model into StockTransferData', function (): void {
    $creator = User::factory()->create();
    $updater = User::factory()->create();
    $from = Store::factory()->create();
    $to = Store::factory()->create();

    /** @var StockTransfer $transfer */
    $transfer = StockTransfer::factory()
        ->for($creator, 'creator')
        ->for($updater, 'updater')
        ->for($from, 'fromStore')
        ->for($to, 'toStore')
        ->create([
            'reference' => 'TR-12345',
            'status' => 'pending',
            'notes' => 'Restock branch',
        ]);

    $data = StockTransferData::from(
        $transfer->load(['creator', 'updater', 'fromStore', 'toStore'])
    );

    expect($data)
        ->toBeInstanceOf(StockTransferData::class)
        ->id->toBe($transfer->id)
        ->reference->toBe('TR-12345')
        ->status->toBe(StockTransferStatusEnum::PENDING)
        ->notes->toBe('Restock branch')
        ->and($data->fromStore->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($from->id)
        ->and($data->toStore->resolve())
        ->toBeInstanceOf(StoreData::class)
        ->id->toBe($to->id)
        ->and($data->creator->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($creator->id)
        ->and($data->updater->resolve())
        ->toBeInstanceOf(UserData::class)
        ->id->toBe($updater->id)
        ->and($data->created_at)
        ->toBe($transfer->created_at->toDateTimeString())
        ->and($data->updated_at)
        ->toBe($transfer->updated_at->toDateTimeString());
});
