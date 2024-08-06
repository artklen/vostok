<?php

abstract class PaymentPurposeStatus extends BasicEnum
{
    public const None = 'none';
    public const Paid = 'paid';
    public const Free = 'free';
    public const Pending = 'pending';
    public const Canceled = 'canceled';
}
