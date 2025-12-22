<?php

declare(strict_types=1);

use App\Actions\Moneyboxes\UpdateMoneyboxBalance;
use App\Models\Moneybox;
use App\Models\User;

it('may update moneybox balance to higher amount', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneyboxBalance::class);

    $updatedMoneybox = $action->handle($moneybox, 75000, $user->id);

    expect($updatedMoneybox->balance)->toBe(75000)
        ->and($updatedMoneybox->updated_by)->toBe($user->id);
});

it('may update moneybox balance to lower amount', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneyboxBalance::class);

    $updatedMoneybox = $action->handle($moneybox, 25000, $user->id);

    expect($updatedMoneybox->balance)->toBe(25000);
});

it('may set balance to zero', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneyboxBalance::class);

    $updatedMoneybox = $action->handle($moneybox, 0, $user->id);

    expect($updatedMoneybox->balance)->toBe(0);
});

it('uses transaction for balance update', function (): void {
    $user = User::factory()->create();
    $moneybox = Moneybox::factory()->create([
        'balance' => 100000,
        'created_by' => $user->id,
    ]);

    $action = resolve(UpdateMoneyboxBalance::class);

    $updatedMoneybox = $action->handle($moneybox, 150000, $user->id);

    $moneybox->refresh();
    expect($moneybox->balance)->toBe(150000);
});
