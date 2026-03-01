<?php

declare(strict_types=1);

namespace App\Actions\Payment;

use App\Actions\GenerateReferenceNo;
use App\Actions\Shared\UpdatePaymentStatus;
use App\Data\Payment\RecordPaymentData;
use App\Enums\PaymentStateEnum;
use App\Enums\PurchaseStatusEnum;
use App\Enums\ReturnStatusEnum;
use App\Enums\SaleStatusEnum;
use App\Exceptions\InvalidPaymentMethodException;
use App\Exceptions\OverpaymentException;
use App\Exceptions\StateTransitionException;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * @param  Sale|SaleReturn|Purchase|PurchaseReturn  $payable
 */
final readonly class RecordPayment
{
    public function __construct(
        private UpdatePaymentStatus $updatePaymentStatus,
        private GenerateReferenceNo $generateReferenceNo,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(Sale|SaleReturn|Purchase|PurchaseReturn $payable, RecordPaymentData $data): Payment
    {
        return DB::transaction(function () use ($payable, $data): Payment {
            /** @var Sale|SaleReturn|Purchase|PurchaseReturn $payable */
            $payable = match ($payable::class) {
                Sale::class => Sale::query()->lockForUpdate()->findOrFail($payable->id),
                SaleReturn::class => SaleReturn::query()->lockForUpdate()->findOrFail($payable->id),
                Purchase::class => Purchase::query()->lockForUpdate()->findOrFail($payable->id),
                PurchaseReturn::class => PurchaseReturn::query()->lockForUpdate()->findOrFail($payable->id),
            };

            $this->validatePaymentMethod($data->payment_method_id);

            $this->validatePayable($payable);

            $this->validateNoOverpayment($payable, $data);

            $payment = Payment::query()->forceCreate([
                'payment_method_id' => $data->payment_method_id,
                'user_id' => $data->user_id,
                'reference_no' => $this->generateReferenceNo->handle('PAY', Payment::class),
                'payable_type' => $payable::class,
                'payable_id' => $payable->id,
                'amount' => $data->amount,
                'payment_date' => $data->payment_date,
                'note' => $data->note,
                'status' => PaymentStateEnum::Active,
            ]);

            $this->updatePaymentStatus->handle($payable);

            return $payment->refresh();
        });
    }

    /**
     * @throws Throwable
     */
    private function validatePaymentMethod(int $paymentMethodId): void
    {
        $paymentMethod = PaymentMethod::query()->find($paymentMethodId);

        throw_if($paymentMethod === null || ! $paymentMethod->is_active, InvalidPaymentMethodException::class, $paymentMethodId);
    }

    /**
     * @throws StateTransitionException
     */
    private function validatePayable(Sale|SaleReturn|Purchase|PurchaseReturn $payable): void
    {
        $canAcceptPayment = $this->checkCanAcceptPayment($payable);

        if (! $canAcceptPayment) {
            $statusValue = $payable->status->value;
            throw new StateTransitionException(
                $statusValue,
                'Payment'
            );
        }
    }

    /**
     * @throws Throwable
     */
    private function validateNoOverpayment(Sale|SaleReturn|Purchase|PurchaseReturn $payable, RecordPaymentData $data): void
    {
        if ($data->amount < 0) {
            throw new InvalidPaymentMethodException($data->payment_method_id, 'Payment amount cannot be negative.');
        }

        /** @var int $currentPaid */
        $currentPaid = Payment::query()
            ->where('payable_type', $payable::class)
            ->where('payable_id', $payable->id)
            ->where('status', PaymentStateEnum::Active)
            ->lockForUpdate()
            ->sum('amount');

        if ($payable instanceof Sale) {
            $maxAllowedPayment = $payable->total_amount * 2;
            if (($currentPaid + $data->amount) > $maxAllowedPayment) {
                throw new OverpaymentException($data->amount, $maxAllowedPayment, $currentPaid);
            }

            return;
        }

        if (($currentPaid + $data->amount) > $payable->total_amount) {
            throw new OverpaymentException($data->amount, $payable->total_amount, $currentPaid);
        }
    }

    private function checkCanAcceptPayment(Sale|SaleReturn|Purchase|PurchaseReturn $payable): bool
    {
        $statusValid = match ($payable::class) {
            Sale::class => $payable->status === SaleStatusEnum::Completed,
            SaleReturn::class => $payable->status === ReturnStatusEnum::Completed,
            Purchase::class => $payable->status === PurchaseStatusEnum::Received,
            PurchaseReturn::class => $payable->status === ReturnStatusEnum::Completed,
        };

        return $statusValid && $payable->payment_status->canAcceptPayment();
    }
}
