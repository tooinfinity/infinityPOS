<?php

declare(strict_types=1);

use App\Actions\Purchase\UpdatePurchase;
use App\Data\Purchase\UpdatePurchaseData;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Optional;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may update purchase supplier', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $newSupplier = Supplier::factory()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: $newSupplier->id,
        warehouse_id: Optional::create(),
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: Optional::create(),
    );

    $updatedPurchase = $action->handle($purchase, $data);

    expect($updatedPurchase->supplier_id)->toBe($newSupplier->id);
});

it('may update purchase warehouse when no items exist', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    $newWarehouse = Warehouse::factory()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: Optional::create(),
        warehouse_id: $newWarehouse->id,
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: Optional::create(),
    );

    $updatedPurchase = $action->handle($purchase, $data);

    expect($updatedPurchase->warehouse_id)->toBe($newWarehouse->id);
});

it('throws exception when changing warehouse with items', function (): void {
    $purchase = Purchase::factory()->pending()->create();
    App\Models\PurchaseItem::factory()->create([
        'purchase_id' => $purchase->id,
    ]);
    $newWarehouse = Warehouse::factory()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: Optional::create(),
        warehouse_id: $newWarehouse->id,
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: Optional::create(),
    );

    expect(fn () => $action->handle($purchase, $data))
        ->toThrow(RuntimeException::class, 'Cannot change warehouse after items have been added.');
});

it('may update all purchase fields', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'purchase_date' => now()->subDays(5),
        'note' => 'Old note',
    ]);
    $newSupplier = Supplier::factory()->create();
    $newWarehouse = Warehouse::factory()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: $newSupplier->id,
        warehouse_id: $newWarehouse->id,
        purchase_date: now(),
        note: 'New note',
        document: Optional::create(),
    );

    $updatedPurchase = $action->handle($purchase, $data);

    expect($updatedPurchase->supplier_id)->toBe($newSupplier->id)
        ->and($updatedPurchase->warehouse_id)->toBe($newWarehouse->id)
        ->and($updatedPurchase->note)->toBe('New note');
});

it('partially updates purchase with Optional fields', function (): void {
    $originalSupplier = Supplier::factory()->create();
    $purchase = Purchase::factory()->pending()->create([
        'supplier_id' => $originalSupplier->id,
        'note' => 'Original note',
    ]);
    $newWarehouse = Warehouse::factory()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: Optional::create(),
        warehouse_id: $newWarehouse->id,
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: Optional::create(),
    );

    $updatedPurchase = $action->handle($purchase, $data);

    expect($updatedPurchase->supplier_id)->toBe($originalSupplier->id)
        ->and($updatedPurchase->warehouse_id)->toBe($newWarehouse->id)
        ->and($updatedPurchase->note)->toBe('Original note');
});

it('throws exception when updating non-pending purchase', function (): void {
    $purchase = Purchase::factory()->received()->create();
    $newSupplier = Supplier::factory()->create();

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: $newSupplier->id,
        warehouse_id: Optional::create(),
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: Optional::create(),
    );

    expect(fn () => $action->handle($purchase, $data))
        ->toThrow(RuntimeException::class, 'Only pending purchases can be updated.');
});

it('updates document and deletes old one', function (): void {
    $oldDocument = UploadedFile::fake()->image('old.jpg');
    $oldPath = $oldDocument->store('purchases/documents', 'public');

    $purchase = Purchase::factory()->pending()->create([
        'document' => $oldPath,
    ]);

    $newDocument = UploadedFile::fake()->image('new.jpg');

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: Optional::create(),
        warehouse_id: Optional::create(),
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: $newDocument,
    );

    $updatedPurchase = $action->handle($purchase, $data);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($updatedPurchase->document);
});

it('removes document when set to null', function (): void {
    $document = UploadedFile::fake()->image('doc.jpg');
    $path = $document->store('purchases/documents', 'public');

    $purchase = Purchase::factory()->pending()->create([
        'document' => $path,
    ]);

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: Optional::create(),
        warehouse_id: Optional::create(),
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: null,
    );

    $updatedPurchase = $action->handle($purchase, $data);

    expect($updatedPurchase->document)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

it('persists updates to database', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'note' => 'Original note',
    ]);

    $action = resolve(UpdatePurchase::class);

    $data = new UpdatePurchaseData(
        supplier_id: Optional::create(),
        warehouse_id: Optional::create(),
        purchase_date: Optional::create(),
        note: 'Updated note',
        document: Optional::create(),
    );

    $action->handle($purchase, $data);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'note' => 'Updated note',
    ]);
});

it('deletes uploaded document when transaction fails', function (): void {
    $purchase = Purchase::factory()->pending()->create([
        'document' => null,
    ]);

    $file = UploadedFile::fake()->image('document.png', 800, 600);

    $data = new UpdatePurchaseData(
        supplier_id: Optional::create(),
        warehouse_id: Optional::create(),
        purchase_date: Optional::create(),
        note: Optional::create(),
        document: $file,
    );

    DB::shouldReceive('transaction')
        ->once()
        ->andThrow(new Exception('Database error'));

    $action = resolve(UpdatePurchase::class);

    expect(fn () => $action->handle($purchase, $data))
        ->toThrow(Exception::class, 'Database error');

    expect(Storage::disk('public')->files('purchases/documents'))->toBeEmpty();
});
