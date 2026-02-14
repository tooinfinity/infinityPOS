<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\UploadProductImageAction;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class UpdateProductAction
{
    public function __construct(
        private UploadProductImageAction $uploadImage,
    ) {}

    /**
     * @param  array{name?: string, sku?: string, barcode?: string, unit_id?: int, category_id?: int|null, brand_id?: int|null, description?: string, image?: UploadedFile|string|null, cost_price?: int, selling_price?: int, quantity?: int, alert_quantity?: int, track_inventory?: bool, is_active?: bool}  $data
     *
     * @throws Throwable
     */
    public function handle(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data): Product {
            if (array_key_exists('image', $data)) {
                if ($data['image'] instanceof UploadedFile) {
                    $data['image'] = $this->uploadImage->handle($data['image'], $product->image);
                } elseif ($data['image'] === null && $product->image !== null) {
                    Storage::disk('public')->delete($product->image);
                }
            }

            $product->update($data);

            return $product->refresh();
        });
    }
}
