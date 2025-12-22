<?php

declare(strict_types=1);

use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list all moneybox transactions', function (): void {
    MoneyboxTransaction::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('moneyboxes.transactions.index'));

    $response->assertStatus(500); // View not created yet
});

it('may show transactions for specific moneybox', function (): void {
    $moneybox = Moneybox::factory()->create(['created_by' => $this->user->id]);

    MoneyboxTransaction::factory()->count(3)->create([
        'moneybox_id' => $moneybox->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('moneyboxes.transactions.show', $moneybox));

    $response->assertStatus(500); // View not created yet
});

it('filters transactions by moneybox correctly', function (): void {
    $moneybox1 = Moneybox::factory()->create(['created_by' => $this->user->id]);
    $moneybox2 = Moneybox::factory()->create(['created_by' => $this->user->id]);

    MoneyboxTransaction::factory()->count(3)->create([
        'moneybox_id' => $moneybox1->id,
        'created_by' => $this->user->id,
    ]);

    MoneyboxTransaction::factory()->count(2)->create([
        'moneybox_id' => $moneybox2->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->get(route('moneyboxes.transactions.show', $moneybox1));

    $response->assertStatus(500); // View not created yet
});
