<?php

declare(strict_types=1);

use App\Actions\Purchase\CancelPurchaseAction;
use App\Enums\PurchaseStatusEnum;
use App\Exceptions\StateTransitionException;
use App\Models\Purchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('may cancel a pending purchase', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $action = resolve(CancelPurchaseAction::class);

    $cancelledPurchase = $action->handle($purchase);

    expect($cancelledPurchase->status)->toBe(PurchaseStatusEnum::Cancelled);
});

it('may cancel an ordered purchase', function (): void {
    $purchase = Purchase::factory()->ordered()->create();

    $action = resolve(CancelPurchaseAction::class);

    $cancelledPurchase = $action->handle($purchase);

    expect($cancelledPurchase->status)->toBe(PurchaseStatusEnum::Cancelled);
});

it('deletes document when cancelling', function (): void {
    $document = UploadedFile::fake()->image('invoice.jpg');
    $path = $document->store('purchases/documents', 'public');

    $purchase = Purchase::factory()->pending()->create([
        'document' => $path,
    ]);

    $action = resolve(CancelPurchaseAction::class);

    $action->handle($purchase);

    Storage::disk('public')->assertMissing($path);
    expect($purchase->fresh()->document)->toBeNull();
});

it('throws StateTransitionException when cancelling received purchase', function (): void {
    $purchase = Purchase::factory()->received()->create();

    $action = resolve(CancelPurchaseAction::class);

    expect(fn () => $action->handle($purchase))
        ->toThrow(StateTransitionException::class);
});

it('throws StateTransitionException when cancelling already cancelled purchase', function (): void {
    $purchase = Purchase::factory()->cancelled()->create();

    $action = resolve(CancelPurchaseAction::class);

    expect(fn () => $action->handle($purchase))
        ->toThrow(StateTransitionException::class);
});

it('persists cancellation to database', function (): void {
    $purchase = Purchase::factory()->pending()->create();

    $action = resolve(CancelPurchaseAction::class);

    $action->handle($purchase);

    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'status' => PurchaseStatusEnum::Cancelled->value,
    ]);
});
