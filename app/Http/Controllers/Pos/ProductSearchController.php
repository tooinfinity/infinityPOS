<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Data\Products\ProductData;
use App\Models\Product;
use App\Services\Pos\RegisterContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class ProductSearchController
{
    public function __construct(private RegisterContext $registerContext) {}

    public function index(Request $request): JsonResponse
    {
        $query = (string) $request->string('query', $request->string('q', ''));
        $barcode = (string) $request->string('barcode');

        $productsQuery = Product::query()
            ->with(['tax'])
            ->where('is_active', true);

        if ($barcode !== '') {
            $productsQuery->where(function (Builder $q) use ($barcode): void {
                $q->where('barcode', $barcode)
                    ->orWhere('sku', $barcode);
            });
        } elseif ($query !== '') {
            $productsQuery->where(function (Builder $q) use ($query): void {
                $q->where('name', 'like', sprintf('%%%s%%', $query))
                    ->orWhere('sku', 'like', sprintf('%%%s%%', $query))
                    ->orWhere('barcode', 'like', sprintf('%%%s%%', $query));
            });
        } else {
            // No search term provided; return empty list to avoid dumping catalog.
            return response()->json(['data' => []]);
        }

        $products = $productsQuery
            ->orderBy('name')
            ->limit(20)
            ->get();

        $register = $this->registerContext->current();
        $storeId = $register?->store_id;

        // Enrich each product with available stock for the current register's store
        $productsData = $products->map(function (Product $product) use ($storeId): ProductData {
            $data = ProductData::from($product);
            if ($storeId !== null) {
                $data->available_stock = $product->getAvailableStock((int) $storeId);
            }

            return $data;
        });

        return response()->json([
            'data' => $productsData,
        ]);
    }
}
