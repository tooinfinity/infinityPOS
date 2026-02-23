<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\UploadImage;
use App\Data\Purchase\UpdatePurchaseData;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\LaravelData\Optional;
use Throwable;

final readonly class UpdatePurchase
{
    public function __construct(private UploadImage $uploadImage) {}

    /**
     * @throws Throwable
     */
    public function handle(Purchase $purchase, UpdatePurchaseData $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data): Purchase {
            throw_if(
                $purchase->status !== PurchaseStatusEnum::Pending,
                RuntimeException::class,
                'Only pending purchases can be updated.'
            );

            $updateData = [];

            if (! $data->supplier_id instanceof Optional) {
                $updateData['supplier_id'] = $data->supplier_id;
            }

            if (! $data->warehouse_id instanceof Optional) {
                throw_if($purchase->items()->exists(), RuntimeException::class, 'Cannot change warehouse after items have been added.');
                $updateData['warehouse_id'] = $data->warehouse_id;
            }

            if (! $data->purchase_date instanceof Optional) {
                $updateData['purchase_date'] = $data->purchase_date;
            }

            if (! $data->note instanceof Optional) {
                $updateData['note'] = $data->note;
            }

            $oldDocument = $purchase->document;

            if (! $data->document instanceof Optional) {
                if ($data->document instanceof UploadedFile) {
                    $updateData['document'] = $this->uploadImage->handle($data->document, 'purchases/documents');
                } elseif (! $data->document instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $updateData['document'] = null;
                }
            }

            $purchase->update($updateData);

            if (! $data->document instanceof Optional && $oldDocument !== null) {
                DB::afterCommit(function () use ($oldDocument): void {
                    if (Storage::disk('public')->exists($oldDocument)) {
                        Storage::disk('public')->delete($oldDocument);
                    }
                });
            }

            return $purchase->refresh();
        });
    }
}
