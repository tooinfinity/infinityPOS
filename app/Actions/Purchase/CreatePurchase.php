<?php

declare(strict_types=1);

namespace App\Actions\Purchase;

use App\Actions\UploadImage;
use App\Data\Purchase\CreatePurchaseData;
use App\Data\Purchase\PurchaseItemData;
use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\LaravelData\DataCollection;
use Throwable;

final readonly class CreatePurchase
{
    public function __construct(private UploadImage $uploadImage) {}

    /**
     * @throws Throwable
     */
    public function handle(CreatePurchaseData $data): Purchase
    {
        $documentPath = null;

        if ($data->document instanceof UploadedFile) {
            $documentPath = $this->uploadImage->handle($data->document, 'purchases/documents');
        }

        try {
            return DB::transaction(function () use ($data, $documentPath): Purchase {
                $totalAmount = $this->calculateTotalAmount($data->items);

                $purchase = Purchase::query()->forceCreate([
                    'supplier_id' => $data->supplier_id,
                    'warehouse_id' => $data->warehouse_id,
                    'user_id' => $data->user_id,
                    'reference_no' => $this->generateReferenceNo(),
                    'status' => PurchaseStatusEnum::Pending,
                    'purchase_date' => $data->purchase_date,
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'payment_status' => PaymentStatusEnum::Unpaid,
                    'note' => $data->note,
                    'document' => $documentPath,
                ]);

                foreach ($data->items as $item) {
                    PurchaseItem::query()->forceCreate([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'received_quantity' => 0,
                        'unit_cost' => $item->unit_cost,
                        'subtotal' => $item->quantity * $item->unit_cost,
                    ]);
                }

                return $purchase->refresh();
            });
        } catch (Throwable $e) {
            if ($documentPath !== null) {
                Storage::disk('public')->delete($documentPath);
            }

            throw $e;
        }
    }

    /**
     * @param  DataCollection<int, PurchaseItemData>  $items
     */
    private function calculateTotalAmount(DataCollection $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item->quantity * $item->unit_cost;
        }

        return $total;
    }

    private function generateReferenceNo(): string
    {
        do {
            $reference = 'PUR-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
        } while (Purchase::query()->where('reference_no', $reference)->exists());

        return $reference;
    }
}
