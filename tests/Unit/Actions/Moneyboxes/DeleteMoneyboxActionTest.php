<?php

declare(strict_types=1);

use App\Actions\Moneyboxes\DeleteMoneybox;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\User;

it('may delete moneybox with zero balance and no transactions', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 0,
        'created_by' => $user->id,
    ]);

    $action = resolve(DeleteMoneybox::class);

    $result = $action->handle($moneybox);

    expect($result)->toBeTrue();
    expect(Moneybox::query()->find($moneybox->id))->toBeNull();
});

it('cannot delete moneybox with non-zero balance', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);

    $action = resolve(DeleteMoneybox::class);

    $action->handle($moneybox);
})->throws(InvalidArgumentException::class, 'Cannot delete moneybox with non-zero balance');

it('cannot delete moneybox with existing transactions', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 0,
        'created_by' => $user->id,
    ]);

    MoneyboxTransaction::factory()->create([
        'moneybox_id' => $moneybox->id,
        'created_by' => $user->id,
    ]);

    $action = resolve(DeleteMoneybox::class);

    $action->handle($moneybox);
})->throws(InvalidArgumentException::class, 'Cannot delete moneybox with existing transactions');
