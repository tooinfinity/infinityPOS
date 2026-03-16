<?php

declare(strict_types=1);

use App\Actions\GenerateReferenceNo;
use App\Models\Purchase;

describe(GenerateReferenceNo::class, function (): void {
    it('may generate a reference number', function (): void {
        $action = resolve(GenerateReferenceNo::class);

        $reference = $action->handle('SAL');

        expect($reference)
            ->toStartWith('SAL-')
            ->toContain(date('Ymd'));
    });

    it('generates reference number with correct format', function (): void {
        $action = resolve(GenerateReferenceNo::class);

        $reference = $action->handle('PUR');

        expect($reference)->toMatch('/^PUR-\d{8}-\d{4}$/');
    });

    it('increments count for same day', function (): void {
        $action = resolve(GenerateReferenceNo::class);

        // Get the current count
        $initialCount = Purchase::query()->count();

        // Create records and verify incrementing
        Purchase::factory()->create();
        $ref1 = $action->handle('TEST');

        Purchase::factory()->create();
        $ref2 = $action->handle('TEST');

        Purchase::factory()->create();
        $ref3 = $action->handle('TEST');

        $datePart = today()->format('Ymd');

        // Verify sequential incrementing (regardless of starting point)
        $num1 = (int) mb_substr($ref1, -4);
        $num2 = (int) mb_substr($ref2, -4);
        $num3 = (int) mb_substr($ref3, -4);

        expect($num2)->toBe($num1 + 1)
            ->and($num3)->toBe($num2 + 1);
    });

    it('generates unique reference numbers', function (): void {
        $action = resolve(GenerateReferenceNo::class);

        $references = [];
        for ($i = 0; $i < 10; $i++) {
            Purchase::factory()->create();
            $references[] = $action->handle('UNQ');
        }

        expect(array_unique($references))->toHaveCount(10);
    });

    it('works with different model types', function (): void {
        $action = resolve(GenerateReferenceNo::class);

        $saleRef = $action->handle('SAL');
        $purchaseRef = $action->handle('PUR');

        expect($saleRef)->toStartWith('SAL-')
            ->and($purchaseRef)->toStartWith('PUR-');
    });

    it('pads count with leading zeros', function (): void {
        $action = resolve(GenerateReferenceNo::class);

        $ref = $action->handle('PAD');

        expect($ref)->toEndWith('0001');
    });
});
