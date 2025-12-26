<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Data\Products\ProductData;
use App\Enums\CategoryTypeEnum;
use App\Models\Category;
use App\Models\Product;
use App\Services\Pos\RegisterContext;
use App\Settings\PosSettings;
use App\Settings\SalesSettings;
use Inertia\Inertia;
use Inertia\Response;

final readonly class PosController
{
    public function __construct(
        private RegisterContext $registerContext,
        private PosSettings $posSettings,
        private SalesSettings $salesSettings,
    ) {}

    public function index(): Response
    {
        $register = $this->registerContext->current();
        $storeId = $register?->store_id;

        // Get active product categories
        $categories = Category::query()
            ->where('type', CategoryTypeEnum::PRODUCT)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (Category $cat): array => [
                'id' => $cat->id,
                'name' => $cat->name,
                'code' => $cat->code,
            ])
            ->all();

        // Load only a limited set of products initially to improve performance
        // Users can search for specific products via the ProductSearchController
        $products = Product::query()
            ->with(['category', 'brand', 'unit', 'tax'])
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(50) // Limit initial load to 50 products
            ->get();

        // Enrich products with stock information
        $productsData = $products->map(function (Product $product) use ($storeId): array {
            $data = ProductData::from($product);
            if ($storeId !== null) {
                $data->available_stock = $product->getAvailableStock((int) $storeId);
            }

            return $data->toArray();
        });

        // Get stores for register setup
        $stores = \App\Models\Store::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (\App\Models\Store $store): array => [
                'id' => $store->id,
                'name' => $store->name,
            ])
            ->all();

        // Get moneyboxes for register setup
        $moneyboxes = \App\Models\Moneybox::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'store_id'])
            ->map(fn (\App\Models\Moneybox $moneybox): array => [
                'id' => $moneybox->id,
                'name' => $moneybox->name,
                'store_id' => $moneybox->store_id,
            ])
            ->all();

        return Inertia::render('pos/index', [
            'products' => $productsData,
            'categories' => $categories,
            'register' => $register instanceof \App\Models\PosRegister ? [
                'id' => $register->id,
                'name' => $register->name,
                'store_id' => $register->store_id,
                'moneybox_id' => $register->moneybox_id,
            ] : null,
            'stores' => $stores,
            'moneyboxes' => $moneyboxes,
            'settings' => [
                'pos' => [
                    'enable_barcode_scanner' => $this->posSettings->enable_barcode_scanner,
                    'default_payment_method' => $this->posSettings->default_payment_method,
                    'auto_print_receipt' => $this->posSettings->auto_print_receipt,
                ],
                'sales' => [
                    'enable_discounts' => $this->salesSettings->enable_discounts,
                    'max_discount_percentage' => $this->salesSettings->max_discount_percentage,
                    'require_customer_for_sale' => $this->salesSettings->require_customer_for_sale,
                    'enable_tax_calculation' => $this->salesSettings->enable_tax_calculation,
                ],
            ],
        ]);
    }
}
