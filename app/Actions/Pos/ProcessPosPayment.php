<?php

declare(strict_types=1);

namespace App\Actions\Pos;

use App\Actions\Payments\RecordMoneyboxTransaction;
use App\Actions\Sales\CompleteSale;
use App\Actions\Sales\CreateSale;
use App\Actions\Sales\ProcessSalePayment;
use App\Data\Payments\RecordMoneyboxTransactionData;
use App\Data\Pos\ProcessPosPaymentData;
use App\Data\Sales\CreateSaleData;
use App\Data\Sales\CreateSaleItemData;
use App\Enums\MoneyboxTransactionTypeEnum;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Pos\CartService;
use App\Services\Pos\RegisterContext;
use App\Settings\PosSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final readonly class ProcessPosPayment
{
    public function __construct(
        private CartService $cart,
        private CalculateCartTotals $cartTotals,
        private ValidateCartStock $validateCartStock,
        private CreateSale $createSale,
        private ProcessSalePayment $processSalePayment,
        private RecordMoneyboxTransaction $recordMoneyboxTransaction,
        private RegisterContext $registerContext,
        private PosSettings $posSettings,
        private CompleteSale $completeSale,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(ProcessPosPaymentData $data, int $userId): Sale
    {
        $cart = $this->cart->getRaw();

        throw_if($cart['items'] === [], InvalidArgumentException::class, 'Cart is empty');

        // Get draft sale for stock validation
        /** @var Sale $draft */
        $draft = $this->cart->getDraftSale();

        return DB::transaction(function () use ($data, $userId, $cart, $draft): Sale {
            $productIds = array_values(array_unique(array_map(
                static fn (array $line): int => $line['product_id'],
                $cart['items'],
            )));

            $products = Product::query()
                ->with('tax')
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $totals = $this->cartTotals->handle($cart['items'], (int) ($cart['discount'] ?? 0));

            $items = [];
            $remainingDiscount = $totals->discount_total;

            // Allocate cart discount proportionally to line subtotals.
            $lines = array_values($cart['items']);
            $lastIndex = count($lines) - 1;

            foreach ($lines as $index => $line) {
                /** @var Product $product */
                $product = $products->get($line['product_id']);

                $lineSubtotal = $line['unit_price'] * $line['quantity'];

                $discount = 0;
                if ($totals->discount_total > 0 && $totals->subtotal > 0) {
                    if ($index === $lastIndex) {
                        $discount = $remainingDiscount;
                    } else {
                        $discount = (int) floor(($lineSubtotal / $totals->subtotal) * $totals->discount_total);
                        $discount = min($discount, $remainingDiscount);
                    }

                    $remainingDiscount -= $discount;
                }

                $taxAmount = 0;
                $tax = $product->tax;
                $taxableBase = max(0, $lineSubtotal - $discount);
                if ($tax !== null && $tax->is_active) {
                    $taxAmount = match ($tax->tax_type) {
                        \App\Enums\TaxTypeEnum::PERCENTAGE => (int) round(($taxableBase * $tax->rate) / 100),
                        \App\Enums\TaxTypeEnum::FIXED => (int) ($tax->rate * $line['quantity']),
                    };
                }

                $items[] = new CreateSaleItemData(
                    product_id: $product->id,
                    quantity: $line['quantity'],
                    price: (int) $line['unit_price'],
                    cost: (int) $product->cost,
                    discount: $discount,
                    tax_amount: $taxAmount,
                    total: ($lineSubtotal - $discount) + $taxAmount,
                    batch_number: null,
                    expiry_date: null,
                );
            }

            // CreateSale will recalculate totals from items; these are seed values.
            $sale = $this->createSale->handle(new CreateSaleData(
                reference: 'POS-'.mb_strtoupper(Str::random(10)),
                client_id: $data->client_id,
                store_id: $data->store_id,
                subtotal: $totals->subtotal,
                discount: $totals->discount_total,
                tax: $totals->tax_total,
                total: $totals->total,
                notes: $data->notes,
                items: $items,
                created_by: $userId,
            ));

            // Enforce cash drawer requirement if configured
            $register = $this->registerContext->current();
            if ($this->posSettings->require_cash_drawer_for_cash_payments && $data->method->isCash()) {
                throw_if($register?->moneybox_id === null, InvalidArgumentException::class, 'Cash drawer is required for cash payments');
            }

            // Final stock validation before payment
            if ($register instanceof \App\Models\PosRegister) {
                $this->validateCartStock->handle($draft, (int) $register->store_id);
            }

            // Record payment against the Sale
            $payment = $this->processSalePayment->handle(
                sale: $sale,
                amount: $data->amount,
                method: $data->method,
                reference: $data->reference,
                notes: $data->notes,
                userId: $userId,
            );

            // If this is a cash payment and a cash drawer is assigned, record it to the moneybox.
            if ($data->method->isCash() && $register?->moneybox_id !== null) {
                $this->recordMoneyboxTransaction->handle(new RecordMoneyboxTransactionData(
                    moneybox_id: (int) $register->moneybox_id,
                    type: MoneyboxTransactionTypeEnum::IN,
                    amount: $data->amount,
                    reference: $data->reference,
                    notes: $data->notes ?? 'POS payment received',
                    payment_id: $payment->id,
                    expense_id: null,
                    transfer_to_moneybox_id: null,
                    created_by: $userId,
                ));
            }

            // Complete the sale (creates stock movements)
            $this->completeSale->handle($sale->refresh(), $userId);

            // Clear cart on success
            $this->cart->clear($userId);

            return $sale->refresh();
        });
    }
}
