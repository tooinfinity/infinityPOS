<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pos;

use App\Actions\Pos\SearchPosProducts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class PosProductSearchController
{
    /**
     * Search products for the POS terminal.
     * Returns JSON — consumed by the React terminal UI.
     */
    public function __invoke(Request $request, SearchPosProducts $action): JsonResponse
    {
        $request->validate([
            'query' => ['required', 'string', 'min:1', 'max:100'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
        ]);

        $products = $action->handle(
            query: $request->string('query')->trim()->value(),
            warehouseId: (int) $request->integer('warehouse_id'),
        );

        return response()->json([
            'products' => $products,
        ]);
    }
}
