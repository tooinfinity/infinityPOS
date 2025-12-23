<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Data\Pos\UpdatePosRegisterData;
use App\Enums\MoneyboxTypeEnum;
use App\Models\Moneybox;
use App\Models\PosRegister;
use App\Models\Store;
use App\Services\Pos\CartService;
use App\Services\Pos\PosConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class RegisterController
{
    public function edit(Request $request): Response
    {
        /** @var string $deviceId */
        $deviceId = $request->cookie(PosConfig::DEVICE_COOKIE_NAME, '');

        $register = $deviceId !== ''
            ? PosRegister::query()->where('device_id', $deviceId)->first()
            : null;

        $stores = Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $moneyboxes = Moneybox::query()
            ->where('is_active', true)
            ->where('type', MoneyboxTypeEnum::CASH_REGISTER)
            ->orderBy('name')
            ->get(['id', 'name', 'store_id']);

        return Inertia::render('pos/register', [
            'register' => $register ? [
                'name' => $register->name,
                'store_id' => (int) $register->store_id,
                'is_configured' => $register->configured_at !== null,
                'moneybox_id' => $register->moneybox_id !== null ? (int) $register->moneybox_id : null,
            ] : null,
            'stores' => $stores,
            'moneyboxes' => $moneyboxes,
        ]);
    }

    public function update(UpdatePosRegisterData $data, Request $request, CartService $cart): RedirectResponse
    {
        $userId = auth()->id();

        /** @var string $deviceId */
        $deviceId = $request->cookie(PosConfig::DEVICE_COOKIE_NAME, '');
        abort_if($deviceId === '', 400, 'POS device not identified');

        $register = PosRegister::query()->firstOrCreate([
            'device_id' => $deviceId,
        ], [
            'name' => $data->name,
            'store_id' => $data->store_id,
            'moneybox_id' => $data->moneybox_id,
            'is_active' => true,
            'draft_sale_id' => null,
            'configured_at' => now(),
            'created_by' => $userId,
            'updated_by' => null,
        ]);

        $previousStoreId = (int) $register->store_id;

        $register->update([
            'name' => $data->name,
            'store_id' => $data->store_id,
            'moneybox_id' => $data->moneybox_id,
            'configured_at' => $register->configured_at ?? now(),
            'updated_by' => $userId,
        ]);

        // If store changed, clear any existing cart for this device/register.
        if ($previousStoreId !== $data->store_id) {
            $cart->clear((int) ($userId ?? 0));
        }

        return to_route('pos.index');
    }
}
