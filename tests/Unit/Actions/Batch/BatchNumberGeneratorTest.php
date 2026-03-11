<?php

declare(strict_types=1);

use App\Actions\Batch\BatchNumberGenerator;
use App\Models\Product;
use App\Models\Unit;

describe(BatchNumberGenerator::class, function (): void {
    it('generates a batch number with correct format', function (): void {
        $action = resolve(BatchNumberGenerator::class);

        $result = $action->handle(1);

        expect($result)->toStartWith('BAT-')
            ->toContain('-1-');
    });

    it('generates unique batch numbers', function (): void {
        $unit = Unit::factory()->create();
        $product = Product::factory()->for($unit)->create();

        $action = resolve(BatchNumberGenerator::class);

        $result1 = $action->handle($product->id);
        $result2 = $action->handle($product->id);

        expect($result1)->not->toBe($result2);
    });

    it('includes product id in batch number', function (): void {
        $unit = Unit::factory()->create();
        $product = Product::factory()->for($unit)->create();

        $action = resolve(BatchNumberGenerator::class);

        $result = $action->handle($product->id);

        expect($result)->toContain('-'.$product->id.'-');
    });

    it('includes timestamp in batch number', function (): void {
        $action = resolve(BatchNumberGenerator::class);

        $result = $action->handle(1);

        expect($result)->toMatch('/BAT-\d{8}-\d{6}-\d+-/');
    });

    it('generates batch number with random suffix', function (): void {
        $action = resolve(BatchNumberGenerator::class);

        $results = [];
        for ($i = 0; $i < 10; $i++) {
            $results[] = $action->handle(1);
        }

        expect(array_unique($results))->toHaveCount(10);
    });
});
