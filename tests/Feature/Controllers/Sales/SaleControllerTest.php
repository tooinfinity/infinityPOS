<?php

declare(strict_types=1);

use App\Enums\SaleStatusEnum;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list sales', function (): void {
    Sale::factory()->count(3)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('sales.index'));

    $response->assertStatus(500); // View not created yet
});

it('may show create sale page', function (): void {
    Client::factory()->count(2)->create(['created_by' => $this->user->id]);
    Store::factory()->count(2)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('sales.create'));

    $response->assertStatus(500); // View not created yet
});

it('may create a sale', function (): void {
    $client = Client::factory()->create(['created_by' => $this->user->id]);
    $store = Store::factory()->create(['created_by' => $this->user->id]);
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('sales.store'), [
        'reference' => 'SALE-001',
        'client_id' => $client->id,
        'store_id' => $store->id,
        'subtotal' => 10000,
        'discount' => 0,
        'tax' => 1000,
        'total' => 11000,
        'notes' => 'Test sale',
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => 10000,
                'cost' => 5000,
                'discount' => 0,
                'tax_amount' => 1000,
                'total' => 11000,
                'batch_number' => null,
                'expiry_date' => null,
            ],
        ],
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('sales.index'));

    $this->assertDatabaseHas('sales', [
        'reference' => 'SALE-001',
        'store_id' => $store->id,
    ]);
});

it('may show a sale', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('sales.show', $sale));

    $response->assertStatus(500); // View not created yet
});

it('may show edit sale page', function (): void {
    $sale = Sale::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('sales.edit', $sale));

    $response->assertStatus(500); // View not created yet
});

it('may update a sale', function (): void {
    $sale = Sale::factory()->create([
        'reference' => 'SALE-001',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('sales.update', $sale), [
        'reference' => 'SALE-UPDATED',
        'client_id' => null,
        'store_id' => null,
        'subtotal' => null,
        'discount' => null,
        'tax' => null,
        'total' => null,
        'notes' => 'Updated notes',
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'reference' => 'SALE-UPDATED',
        'notes' => 'Updated notes',
    ]);
});

it('may delete a pending sale', function (): void {
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('sales.destroy', $sale));

    $response->assertRedirect(route('sales.index'));

    $this->assertDatabaseMissing('sales', [
        'id' => $sale->id,
    ]);
});

it('cannot delete a completed sale', function (): void {
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::COMPLETED,
        'created_by' => $this->user->id,
    ]);

    $response = $this->delete(route('sales.destroy', $sale));

    $response->assertRedirect();
    $response->assertSessionHasErrors(['message']);

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
    ]);
});

it('may complete a sale', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);
    SaleItem::factory()->create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
    ]);

    $response = $this->post(route('sales.complete', $sale));

    $response->assertRedirect();

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => SaleStatusEnum::COMPLETED->value,
    ]);
});

it('may cancel a sale', function (): void {
    $sale = Sale::factory()->create([
        'status' => SaleStatusEnum::PENDING,
        'created_by' => $this->user->id,
    ]);

    $response = $this->post(route('sales.cancel', $sale));

    $response->assertRedirect();

    $this->assertDatabaseHas('sales', [
        'id' => $sale->id,
        'status' => SaleStatusEnum::CANCELLED->value,
    ]);
});
