<?php

declare(strict_types=1);

namespace App\Services\Pos;

use App\Enums\SaleStatusEnum;
use App\Models\PosRegister;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Draft Sale-backed cart.
 *
 * We keep a draft Sale id in session and store cart lines as SaleItem rows.
 */
final readonly class CartService
{
    public function __construct(private Request $request) {}

    public function getDraftSaleId(): ?int
    {
        $register = $this->getOrCreateRegister();

        return $register->draft_sale_id !== null ? (int) $register->draft_sale_id : null;
    }

    public function getDraftSale(): ?Sale
    {
        $register = $this->getOrCreateRegister();

        $draftId = $register->draft_sale_id !== null ? (int) $register->draft_sale_id : null;
        if ($draftId === null) {
            return null;
        }

        return Sale::query()
            ->whereKey($draftId)
            ->where('status', SaleStatusEnum::PENDING)
            ->first();
    }

    public function getOrCreateDraftSale(int $userId): Sale
    {
        $existing = $this->getDraftSale();
        if ($existing instanceof Sale) {
            return $existing;
        }

        $register = $this->getOrCreateRegister($userId);
        $storeId = (int) $register->store_id;

        $sale = Sale::query()->create([
            'reference' => 'DRAFT-'.mb_strtoupper(Str::random(10)),
            'client_id' => null,
            'store_id' => $storeId,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'total' => 0,
            'paid' => 0,
            'status' => SaleStatusEnum::PENDING,
            'notes' => null,
            'created_by' => $userId,
        ]);

        $register->update([
            'draft_sale_id' => $sale->id,
            'updated_by' => $userId,
        ]);

        return $sale;
    }

    /**
     * @return array{items: array<string, array{product_id:int,name:string,unit_price:int,quantity:int}>, discount:int, tax_override:int, sale_id:?int}
     */
    public function getRaw(): array
    {
        $sale = $this->getDraftSale();
        if (! $sale instanceof Sale) {
            return ['items' => [], 'discount' => 0, 'tax_override' => 0, 'sale_id' => null];
        }

        $sale->loadMissing('items.product');
        $items = [];
        foreach ($sale->items as $item) {
            $items['item_'.$item->id] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'unit_price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
            ];
        }

        return [
            'items' => $items,
            'discount' => (int) ($sale->discount ?? 0),
            'tax_override' => (int) ($sale->tax ?? 0),
            'sale_id' => $sale->id,
        ];
    }

    public function setDiscount(int $userId, int $discount): void
    {
        $sale = $this->getOrCreateDraftSale($userId);
        $sale->update(['discount' => max(0, $discount)]);
    }

    public function setTaxOverride(int $userId, int $tax): void
    {
        $sale = $this->getOrCreateDraftSale($userId);
        $sale->update(['tax' => max(0, $tax)]);
    }

    public function clear(int $userId): void
    {
        $register = $this->getOrCreateRegister($userId);
        $sale = $this->getDraftSale();

        if ($sale instanceof Sale) {
            SaleItem::query()->where('sale_id', $sale->id)->delete();
            $sale->delete();
        }

        $register->update([
            'draft_sale_id' => null,
            'updated_by' => $userId,
        ]);
    }

    private function getDeviceId(): string
    {
        /** @var string $deviceId */
        $deviceId = $this->request->cookie(PosConfig::DEVICE_COOKIE_NAME, '');

        // Middleware should ensure this exists, but be defensive.
        return $deviceId !== '' ? $deviceId : (string) Str::uuid();
    }

    private function getOrCreateRegister(?int $userId = null): PosRegister
    {
        $deviceId = $this->getDeviceId();

        $existing = PosRegister::query()->where('device_id', $deviceId)->first();
        if ($existing instanceof PosRegister) {
            return $existing;
        }

        $store = Store::query()->where('is_active', true)->orderBy('id')->first();
        if ($store === null) {
            $store = Store::query()->orderBy('id')->first() ?? Store::query()->create([
                'name' => 'Default Store',
                'city' => null,
                'address' => null,
                'phone' => null,
                'is_active' => true,
                'created_by' => $userId,
                'updated_by' => null,
            ]);
        }

        return PosRegister::query()->create([
            'store_id' => $store->id,
            'name' => 'Register '.mb_strtoupper(mb_substr($deviceId, 0, 8)),
            'device_id' => $deviceId,
            'is_active' => true,
            'draft_sale_id' => null,
            'created_by' => $userId,
            'updated_by' => null,
        ]);
    }
}
