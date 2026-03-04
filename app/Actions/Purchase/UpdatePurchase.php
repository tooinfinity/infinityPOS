<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\UploadImage;
use App\Data\Purchase\UpdatePurchaseData;
use App\Models\Purchase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $oldDocument = $purchase->document;
        $uploadedDocumentPath = null;

        if ($data->document instanceof UploadedFile) {
            $uploadedDocumentPath = $this->uploadImage->handle($data->document, 'purchases/documents');
        }

        try {
            return DB::transaction(static function () use ($purchase, $data, $uploadedDocumentPath, $oldDocument): Purchase {
                /** @var Purchase $purchase */
                $purchase = Purchase::query()
                    ->lockForUpdate()
                    ->findOrFail($purchase->id);

                $updateData = [];

                if (! $data->supplier_id instanceof Optional) {
                    $updateData['supplier_id'] = $data->supplier_id;
                }

                if (! $data->warehouse_id instanceof Optional) {
                    $updateData['warehouse_id'] = $data->warehouse_id;
                }

                if (! $data->purchase_date instanceof Optional) {
                    $updateData['purchase_date'] = $data->purchase_date;
                }

                if (! $data->note instanceof Optional) {
                    $updateData['note'] = $data->note;
                }

                if (! $data->document instanceof Optional) {
                    if ($uploadedDocumentPath !== null) {
                        $updateData['document'] = $uploadedDocumentPath;
                    } elseif (! $data->document instanceof UploadedFile) {
                        $updateData['document'] = null;
                    }
                }

                $purchase->update($updateData);

                if (! $data->document instanceof Optional && $oldDocument !== null) {
                    DB::afterCommit(static function () use ($oldDocument): void {
                        if (Storage::disk('public')->exists($oldDocument)) {
                            Storage::disk('public')->delete($oldDocument);
                        }
                    });
                }

                return $purchase->refresh();
            });
        } catch (Throwable $e) {
            if ($uploadedDocumentPath !== null) {
                Storage::disk('public')->delete($uploadedDocumentPath);
            }
            throw $e;
        }
    }
}
