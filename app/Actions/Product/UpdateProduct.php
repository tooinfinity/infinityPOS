<?php

declare(strict_types=1);

namespace App\Actions\Product;

use App\Actions\UploadImage;
use App\Data\Product\UpdateProductData;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdateProduct
{
    public function __construct(
        private UploadImage $uploadImage,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Product $product, UpdateProductData $data): Product
    {
        $uploadedImagePath = null;
        $imageToDelete = null;

        if ($data->image instanceof UploadedFile) {
            $uploadedImagePath = $this->uploadImage->handle($data->image, 'products');
            $imageToDelete = $product->image;
        }

        try {
            $updatedProduct = DB::transaction(static function () use ($product, $data, $uploadedImagePath, &$imageToDelete): Product {
                $updateData = [];

                if (! $data->name instanceof Optional) {
                    $updateData['name'] = $data->name;
                }
                if (! $data->sku instanceof Optional) {
                    $updateData['sku'] = $data->sku;
                }
                if (! $data->barcode instanceof Optional) {
                    $updateData['barcode'] = $data->barcode;
                }
                if (! $data->unit_id instanceof Optional) {
                    $updateData['unit_id'] = $data->unit_id;
                }
                if (! $data->category_id instanceof Optional) {
                    $updateData['category_id'] = $data->category_id;
                }
                if (! $data->brand_id instanceof Optional) {
                    $updateData['brand_id'] = $data->brand_id;
                }
                if (! $data->description instanceof Optional) {
                    $updateData['description'] = $data->description;
                }
                if (! $data->cost_price instanceof Optional) {
                    $updateData['cost_price'] = $data->cost_price;
                }
                if (! $data->selling_price instanceof Optional) {
                    $updateData['selling_price'] = $data->selling_price;
                }
                if (! $data->alert_quantity instanceof Optional) {
                    $updateData['alert_quantity'] = $data->alert_quantity;
                }
                if (! $data->track_inventory instanceof Optional) {
                    $updateData['track_inventory'] = $data->track_inventory;
                }
                if (! $data->is_active instanceof Optional) {
                    $updateData['is_active'] = $data->is_active;
                }

                if (! $data->image instanceof Optional) {
                    $image = $data->image;
                    if ($image instanceof UploadedFile) {
                        $updateData['image'] = $uploadedImagePath;
                    } elseif (is_string($image) && $image !== '' && $image !== $product->image) {
                        $updateData['image'] = $image;
                        $imageToDelete = $product->image;
                    } elseif ($image === null && $product->image !== null) {
                        $updateData['image'] = null;
                        $imageToDelete = $product->image;
                    }
                }

                $product->update($updateData);

                return $product->refresh();
            });
            if ($imageToDelete !== null) {
                Storage::disk('public')->delete($imageToDelete);
            }

            return $updatedProduct;
        } catch (Throwable $e) {
            if ($uploadedImagePath !== null && Storage::disk('public')->exists($uploadedImagePath)) {
                Storage::disk('public')->delete($uploadedImagePath);
            }
            throw $e;
        }
    }
}
