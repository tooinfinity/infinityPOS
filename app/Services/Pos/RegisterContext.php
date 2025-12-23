<?php

declare(strict_types=1);

namespace App\Services\Pos;

use App\Models\PosRegister;
use Illuminate\Http\Request;

final readonly class RegisterContext
{
    public function __construct(private Request $request) {}

    public function current(): ?PosRegister
    {
        /** @var string $deviceId */
        $deviceId = $this->request->cookie(PosConfig::DEVICE_COOKIE_NAME, '');
        if ($deviceId === '') {
            return null;
        }

        return PosRegister::query()->where('device_id', $deviceId)->first();
    }
}
