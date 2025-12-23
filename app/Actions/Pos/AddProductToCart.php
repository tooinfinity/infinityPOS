<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Data\Pos\AddProductToCartData;
use App\Models\Product;
use App\Services\Pos\CartService;
use App\Services\Pos\RegisterContext;
use App\Settings\InventorySettings;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Throwable;

final readonly class AddProductToCart
{
    public function __construct(
        private CartService $cart,
        private RegisterContext $registerContext,
        private InventorySettings $inventorySettings,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(AddProductToCartData $data, int $userId): void
    {
        $product = Product::query()
            ->whereKey($data->product_id)
            ->where('is_active', true)
            ->first();

        throw_unless($product, ModelNotFoundException::class, 'Product not found');

        $sale = $this->cart->getOrCreateDraftSale($userId);

        // Get register and validate stock if auto_deduct_stock is enabled
        $register = $this->registerContext->current();
        if ($this->inventorySettings->auto_deduct_stock && $register instanceof \App\Models\PosRegister) {
            $storeId = (int) $register->store_id;
            $availableStock = $product->getAvailableStock($storeId);

            // Calculate total qty that would be in cart after adding
            $existing = $sale->items()->where('product_id', $product->id)->first();
            $currentQtyInCart = $existing ? (int) $existing->quantity : 0;
            $newTotalQty = $currentQtyInCart + $data->quantity;

            throw_if(
                $newTotalQty > $availableStock,
                InvalidArgumentException::class,
                sprintf('Insufficient stock. Available: %d, Requested: %d', $availableStock, $newTotalQty)
            );
        }

        $existing = $sale->items()->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->update([
                'quantity' => (int) $existing->quantity + $data->quantity,
            ]);

            return;
        }

        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => $data->quantity,
            'price' => (int) $product->price,
            'cost' => (int) $product->cost,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => (int) $product->price * $data->quantity,
            'batch_number' => null,
            'expiry_date' => null,
        ]);
    }
}
