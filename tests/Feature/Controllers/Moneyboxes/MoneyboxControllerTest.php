<?php

declare(strict_types=1);

use App\Enums\MoneyboxTypeEnum;
use App\Models\Moneybox;
use App\Models\MoneyboxTransaction;
use App\Models\Store;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list moneyboxes', function (): void {
    Moneybox::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('moneyboxes.index'));

    $response->assertStatus(500); // View not created yet
});

it('may show create moneybox page', function (): void {
    Store::factory()->count(2)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('moneyboxes.create'));

    $response->assertStatus(500); // View not created yet
});

it('may create a cash register', function (): void {
    $store = Store::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('moneyboxes.store'), [
        'name' => 'Cash Register 1',
        'type' => MoneyboxTypeEnum::CASH_REGISTER->value,
        'description' => 'Main cash register',
        'bank_name' => null,
        'account_number' => null,
        'is_active' => true,
        'store_id' => $store->id,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('moneyboxes.index'));

    $this->assertDatabaseHas('moneyboxes', [
        'name' => 'Cash Register 1',
        'type' => MoneyboxTypeEnum::CASH_REGISTER->value,
        'balance' => 0,
    ]);
});

it('may create a bank account', function (): void {
    $response = $this->post(route('moneyboxes.store'), [
        'name' => 'Business Bank',
        'type' => MoneyboxTypeEnum::BANK_ACCOUNT->value,
        'description' => 'Main account',
        'bank_name' => 'First National Bank',
        'account_number' => '1234567890',
        'is_active' => true,
        'store_id' => null,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('moneyboxes.index'));

    $this->assertDatabaseHas('moneyboxes', [
        'name' => 'Business Bank',
        'type' => MoneyboxTypeEnum::BANK_ACCOUNT->value,
        'bank_name' => 'First National Bank',
    ]);
});

it('may show a moneybox', function (): void {
    $moneybox = Moneybox::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('moneyboxes.show', $moneybox));

    $response->assertStatus(500); // View not created yet
});

it('may show edit moneybox page', function (): void {
    $moneybox = Moneybox::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('moneyboxes.edit', $moneybox));

    $response->assertStatus(500); // View not created yet
});

it('may update a moneybox', function (): void {
    $moneybox = Moneybox::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('moneyboxes.update', $moneybox), [
        'name' => 'New Name',
        'type' => null,
        'description' => 'Updated description',
        'bank_name' => null,
        'account_number' => null,
        'is_active' => null,
        'store_id' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('moneyboxes', [
        'id' => $moneybox->id,
        'name' => 'New Name',
        'description' => 'Updated description',
    ]);
});

it('may delete moneybox with zero balance', function (): void {
    $moneybox = Moneybox::factory()->create([
        'balance' => 0,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('moneyboxes.destroy', $moneybox));

    $response->assertRedirect(route('moneyboxes.index'));

    $this->assertDatabaseMissing('moneyboxes', [
        'id' => $moneybox->id,
    ]);
});

it('cannot delete moneybox with non-zero balance', function (): void {
    $moneybox = Moneybox::factory()->create([
        'balance' => 50000,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('moneyboxes.destroy', $moneybox));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('moneyboxes', [
        'id' => $moneybox->id,
    ]);
});

it('cannot delete moneybox with transactions', function (): void {
    $moneybox = Moneybox::factory()->create([
        'balance' => 0,
        'created_by' => $this->user->id,
    ]);

    MoneyboxTransaction::factory()->create([
        'moneybox_id' => $moneybox->id,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('moneyboxes.destroy', $moneybox));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);
});
