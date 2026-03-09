<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\GenerateReferenceNo;
use App\Data\Payment\PaymentData;
use App\Enums\PaymentStateEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Exceptions\OverpaymentException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class RecordPayment
{
    public function __construct(
        private GenerateReferenceNo $referenceGenerator,
        private UpdatePaymentStatus $updatePaymentStatus,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(
        Sale|Purchase|SaleReturn|PurchaseReturn $payable,
        PaymentData $data,
    ): Payment {
        /** @var Payment $payment */
        $payment = DB::transaction(function () use ($payable, $data): Payment {
            $method = PaymentMethod::query()
                ->lockForUpdate()
                ->find($data->payment_method_id);

            if (! $method instanceof PaymentMethod) {
                throw new InvalidPaymentMethodException(
                    $data->payment_method_id,
                    'Payment method not found.'
                );
            }

            if (! $payable->payment_status->canAcceptPayment()) {
                throw new InvalidPaymentMethodException(
                    $data->payment_method_id,
                    sprintf(
                        '%s #%d is already fully paid.',
                        class_basename($payable),
                        $payable->id,
                    )
                );
            }

            $currentPaid = Payment::sumForPayable($payable, lockForUpdate: true);
            $dueAmount = $payable->total_amount - $currentPaid;
            $maxAcceptable = $this->resolveMaxAcceptable($payable, $dueAmount);

            if ($data->amount > $maxAcceptable) {
                throw new OverpaymentException(
                    amount: $data->amount,
                    maxAllowed: $maxAcceptable,
                    currentPaid: $currentPaid,
                );
            }

            $payment = Payment::query()->forceCreate([
                'payment_method_id' => $data->payment_method_id,
                'user_id' => auth()->id(),
                'reference_no' => $this->referenceGenerator->handle('PAY', Payment::class),
                'payable_type' => $payable->getMorphClass(),
                'payable_id' => $payable->getKey(),
                'amount' => $data->amount,
                'payment_date' => $data->payment_date,
                'note' => $data->note,
                'status' => PaymentStateEnum::Active,
            ]);

            $this->updatePaymentStatus->handle($payable);

            return $payment->load('paymentMethod');
        });

        return $payment;
    }

    private function resolveMaxAcceptable(
        Sale|Purchase|SaleReturn|PurchaseReturn $payable,
        int $dueAmount,
    ): int {
        if ($payable instanceof Sale && $payable->customer_id === null) {
            return PHP_INT_MAX;
        }

        return $dueAmount;
    }
}
