<?php

abstract class PaymentPurpose
{
    public static function createFromPayment(Payment $payment): ?self
    {
        if ($payment->is_empty()) {
            return null;
        }

        if ($payment->order_id !== '') {
            return PaymentForOrder::create((new Order())->f($payment->order_id),$payment);
        }

        return null;
    }

    /** @var Payment */
    public $payment;

    protected function __construct(?Payment $payment)
    {
        $this->payment = $payment;
    }

    public function isCanceled(): bool
    {
        if (! isset($this->payment)) {
            return false;
        }

        return (bool) $this->payment->is_canceled;
    }

    public function isPaid(): bool
    {
        if ($this->isCanceled()) {
            return false;
        }

        if (! isset($this->payment)) {
            return false;
        }

        return (bool) $this->payment->is_paid;
    }

    public function status(): string
    {
        if (! $this->needToPay()) {
            if ($this->isPaid()) {
                return PaymentPurposeStatus::Paid;
            }
            return PaymentPurposeStatus::Free;
        }

        if ($this->isCanceled()) {
            return PaymentPurposeStatus::Canceled;
        }
        if ($this->isPaid()) {
            return PaymentPurposeStatus::Paid;
        }
        if ($this->payment === null) {
            return PaymentPurposeStatus::None;
        }
        return PaymentPurposeStatus::Pending;
    }

    abstract public function title(): string;

    abstract public function description(): string;

    abstract public function logData(): array;

    abstract public function needToPay(): bool;

    abstract public function amountToPay(): float;

    abstract public function createPayment(): Payment;

    abstract public function pay(Acquiring $acquiring): ?string;

    abstract public function onSuccess(): void;

    abstract public function statusUrl(): string;
}