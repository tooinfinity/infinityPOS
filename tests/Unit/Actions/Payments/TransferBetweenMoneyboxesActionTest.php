<?php

declare(strict_types=1);

use App\Actions\Payments\TransferBetweenMoneyboxes;
use App\Data\Payments\TransferBetweenMoneyboxesData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Moneybox;
use App\Models\User;

it('may transfer between moneyboxes', function (): void {
    $user = User::factory()->create();
    $fromMoneybox = Moneybox::factory()->create([
        'name' => 'Cash Register 1',
        'balance' => 100000,
        'created_by' => $user->id,
    ]);
    $toMoneybox = Moneybox::factory()->create([
        'name' => 'Cash Register 2',
        'balance' => 50000,
        'created_by' => $user->id,
    ]);

    $action = resolve(TransferBetweenMoneyboxes::class);

    $data = TransferBetweenMoneyboxesData::from([
        'from_moneybox_id' => $fromMoneybox->id,
        'to_moneybox_id' => $toMoneybox->id,
        'amount' => 30000,
        'reference' => 'TRF-001',
        'notes' => 'Balance adjustment',
        'created_by' => $user->id,
    ]);

    $result = $action->handle($data);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['from', 'to']);

    // Check FROM transaction (OUT)
    expect($result['from']->type)->toBe(MoneyboxTransactionTypeEnum::TRANSFER)
        ->and($result['from']->amount)->toBe(30000)
        ->and($result['from']->balance_after)->toBe(70000)
        ->and($result['from']->transfer_to_moneybox_id)->toBe($toMoneybox->id);

    // Check TO transaction (IN)
    expect($result['to']->type)->toBe(MoneyboxTransactionTypeEnum::IN)
        ->and($result['to']->amount)->toBe(30000)
        ->and($result['to']->balance_after)->toBe(80000);

    // Verify balances updated
    $fromMoneybox->refresh();
    $toMoneybox->refresh();

    expect($fromMoneybox->balance)->toBe(70000)
        ->and($toMoneybox->balance)->toBe(80000);
});

it('throws exception when transferring to same moneybox', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create(['created_by' => $user->id]);

    $action = resolve(TransferBetweenMoneyboxes::class);

    $data = TransferBetweenMoneyboxesData::from([
        'from_moneybox_id' => $moneybox->id,
        'to_moneybox_id' => $moneybox->id,
        'amount' => 10000,
        'reference' => null,
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $action->handle($data);
})->throws(InvalidArgumentException::class, 'Cannot transfer to the same moneybox');

it('throws exception for zero or negative amount', function (): void {
    $user = User::factory()->create();
    $fromMoneybox = Moneybox::factory()->create(['created_by' => $user->id]);
    $toMoneybox = Moneybox::factory()->create(['created_by' => $user->id]);

    $action = resolve(TransferBetweenMoneyboxes::class);

    $data = TransferBetweenMoneyboxesData::from([
        'from_moneybox_id' => $fromMoneybox->id,
        'to_moneybox_id' => $toMoneybox->id,
        'amount' => 0,
        'reference' => null,
        'notes' => null,
        'created_by' => $user->id,
    ]);

    $action->handle($data);
})->throws(InvalidArgumentException::class, 'Transfer amount must be positive');

it('transfers with custom reference and notes', function (): void {
    $user = User::factory()->create();
    $fromMoneybox = Moneybox::factory()->create([
        'balance' => 200000,
        'created_by' => $user->id,
    ]);
    $toMoneybox = Moneybox::factory()->create([
        'balance' => 100000,
        'created_by' => $user->id,
    ]);

    $action = resolve(TransferBetweenMoneyboxes::class);

    $data = TransferBetweenMoneyboxesData::from([
        'from_moneybox_id' => $fromMoneybox->id,
        'to_moneybox_id' => $toMoneybox->id,
        'amount' => 50000,
        'reference' => 'CUSTOM-TRF-001',
        'notes' => 'End of day reconciliation',
        'created_by' => $user->id,
    ]);

    $result = $action->handle($data);

    expect($result['from']->reference)->toBe('CUSTOM-TRF-001')
        ->and($result['from']->notes)->toBe('End of day reconciliation');
});
