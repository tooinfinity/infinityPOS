<?php

declare(strict_types=1);

use App\Enums\RegisterSessionStatusEnum;

describe('RegisterSessionStatusEnum', function (): void {
    it('returns all values', function (): void {
        $values = RegisterSessionStatusEnum::values();

        expect($values)->toBe([
            'open',
            'closed',
        ]);
    });

    it('returns correct label for each case', function (RegisterSessionStatusEnum $case, string $expectedLabel): void {
        expect($case->label())->toBe($expectedLabel);
    })->with([
        [RegisterSessionStatusEnum::OPEN, 'Open'],
        [RegisterSessionStatusEnum::CLOSED, 'Closed'],
    ]);

    it('returns correct color for each case', function (RegisterSessionStatusEnum $case, string $expectedColor): void {
        expect($case->color())->toBe($expectedColor);
    })->with([
        [RegisterSessionStatusEnum::OPEN, 'green'],
        [RegisterSessionStatusEnum::CLOSED, 'gray'],
    ]);

    it('returns correct icon for each case', function (RegisterSessionStatusEnum $case, string $expectedIcon): void {
        expect($case->icon())->toBe($expectedIcon);
    })->with([
        [RegisterSessionStatusEnum::OPEN, 'unlock'],
        [RegisterSessionStatusEnum::CLOSED, 'lock'],
    ]);

    it('correctly identifies open status', function (RegisterSessionStatusEnum $case, bool $expectedResult): void {
        expect($case->isOpen())->toBe($expectedResult);
    })->with([
        [RegisterSessionStatusEnum::OPEN, true],
        [RegisterSessionStatusEnum::CLOSED, false],
    ]);

    it('correctly identifies closed status', function (RegisterSessionStatusEnum $case, bool $expectedResult): void {
        expect($case->isClosed())->toBe($expectedResult);
    })->with([
        [RegisterSessionStatusEnum::OPEN, false],
        [RegisterSessionStatusEnum::CLOSED, true],
    ]);
});
