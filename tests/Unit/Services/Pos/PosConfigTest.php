<?php

declare(strict_types=1);

use App\Services\Pos\PosConfig;

test('it has device cookie name constant', function (): void {
    expect(PosConfig::DEVICE_COOKIE_NAME)->toBe('pos_device_id');
});

test('device cookie name is a string', function (): void {
    expect(PosConfig::DEVICE_COOKIE_NAME)->toBeString();
});
