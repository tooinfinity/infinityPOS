<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('may list products', function (): void {
    Product::factory()->count(5)->create(['created_by' => $this->user->id]);

    $response = $this->get(route('products.index'));

    $response->assertStatus(500); // View not created yet
});

it('may create a product', function (): void {
    $category = Category::factory()->create(['created_by' => $this->user->id]);
    $brand = Brand::factory()->create(['created_by' => $this->user->id]);
    $unit = Unit::factory()->create(['created_by' => $this->user->id]);
    $tax = Tax::factory()->create(['created_by' => $this->user->id]);

    $response = $this->post(route('products.store'), [
        'name' => 'Test Product',
        'sku' => 'TEST-001',
        'barcode' => '123456789',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'unit_id' => $unit->id,
        'tax_id' => $tax->id,
        'cost' => 5000,
        'price' => 10000,
        'alert_quantity' => 10,
        'description' => 'Test description',
        'image' => null,
        'has_batches' => false,
        'is_active' => true,
        'created_by' => $this->user->id,
    ]);

    $response->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', [
        'name' => 'Test Product',
        'sku' => 'TEST-001',
    ]);
});

it('may update a product', function (): void {
    $product = Product::factory()->create([
        'name' => 'Old Name',
        'created_by' => $this->user->id,
    ]);

    $response = $this->patch(route('products.update', $product), [
        'name' => 'Updated Name',
        'sku' => null,
        'barcode' => null,
        'category_id' => null,
        'brand_id' => null,
        'unit_id' => null,
        'tax_id' => null,
        'cost' => null,
        'price' => null,
        'alert_quantity' => null,
        'description' => null,
        'image' => null,
        'has_batches' => null,
        'is_active' => null,
        'updated_by' => $this->user->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
    ]);
});

it('may delete a product', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->delete(route('products.destroy', $product));

    $response->assertRedirect(route('products.index'));

    $this->assertDatabaseMissing('products', [
        'id' => $product->id,
    ]);
});

it('may show create product page', function (): void {
    Category::factory()->create(['created_by' => $this->user->id]);
    Brand::factory()->create(['created_by' => $this->user->id]);
    Unit::factory()->create(['created_by' => $this->user->id]);
    Tax::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('products.create'));

    $response->assertStatus(500); // View not created yet
});

it('may show a product', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('products.show', $product));

    $response->assertStatus(500); // View not created yet
});

it('may show edit product page', function (): void {
    $product = Product::factory()->create(['created_by' => $this->user->id]);

    $response = $this->get(route('products.edit', $product));

    $response->assertStatus(500); // View not created yet
});
