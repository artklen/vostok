<?php

class PaymentForOrder extends PaymentPurpose
{
    /** @var Order */
    public $order;

    public function __construct(Order $order, ?Payment $payment) {
        parent::__construct($payment);
        $this->order = $order;
    }

    public static function create(Order $order, ?Payment $payment = null): ?self
    {
        if ($order->is_empty()) {
            return null;
        }
        if ($payment === null) {
            $payment = new Payment();

            if ($order->payment_id !== '') {
                $payment->f($order->payment_id);
            } else {
                // нужен оплаченный, если такого нет - последний созданный
                $payment
                    ->where('`order_id`=?', $order->id)
                    ->order('`is_paid` desc, `id`')
                    ->limit(1);
            }

            if ($payment->is_empty()) {
                $payment = null;
            }
        }

        return new self($order, $payment);
    }

    public function title(): string
    {
        return 'Заказ №' . $this->order->id;
    }

    public function description(): string
    {
        return 'Оплата заказа №' . $this->order->id . ' на сайте ' . $_ENV['SITE_MAIN_DOMAIN'];
    }

    public function logData(): array
    {
        return [
            'order_id' => $this->order->id,
            'payment_id' => isset($this->payment) ? $this->payment->id : null,
        ];
    }

    public function needToPay(): bool
    {
        return true;
    }

    public function isCanceled(): bool
    {
        if ($this->order->status_id == Order::CANCELED) {
            return true;
        }
        return parent::isCanceled();
    }

    public function amountToPay(): float
    {
        return $this->order->order_price();
    }

    public function createPayment(): Payment
    {
        $payment = (new Payment())->new();
        $payment->order_id = $this->order->id;
        return $payment;
    }

    public function pay(Acquiring $acquiring): ?string
    {
        return $acquiring->payForOrder($this);
    }

    public function onSuccess(): void
    {
        $this->order->orders_payment_id = $this->payment->id;
        $this->order->is_paid = 1;
        $this->order->payed_amount = $this->payment->sum;
        $this->order = $this->order->save_and_load();

        if (! $_ENV['YOOKASSA_TEST']) {
            d()->emit('aquiring.successfull_paid', [$this->order]);
        }
    }

    public function statusUrl(): string
    {
        return '/orders/payment-status?id=' . urlencode($this->order->secret);
    }
}