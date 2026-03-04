<?php

declare(strict_types=1);

use App\Actions\Purchase\CancelPurchase;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may cancel a pending purchase', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $action = resolve(CancelPurchase::class);

    $cancelledPurchase = $action->handle($purchase);

    expect($cancelledPurchase->status)->toBe(PurchaseStatusEnum::Cancelled);
});

it('may cancel an ordered purchase', function (): void {
    $purchase = Purchase::factory()->ordered()->create();

    $action = resolve(CancelPurchase::class);

    $cancelledPurchase = $action->handle($purchase);

    expect($cancelledPurchase->status)->toBe(PurchaseStatusEnum::Cancelled);
});

it('deletes document when cancelling', function (): void {
    $document = UploadedFile::fake()->image('invoice.jpg');
    $path = $document->store('purchases/documents', 'public');

    $purchase = Purchase::factory()->pending()->create([
        'document' => $path,
    ]);

    $action = resolve(CancelPurchase::class);

    $action->handle($purchase);

    Storage::disk('public')->assertMissing($path);
    expect($purchase->fresh()->document)->toBeNull();
});

it('persists cancellation to database', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $action = resolve(CancelPurchase::class);

    $action->handle($purchase);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::Cancelled->value,
    ]);
});
