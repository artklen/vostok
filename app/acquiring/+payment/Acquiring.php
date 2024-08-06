<?php

interface Acquiring
{
    public function payForOrder(PaymentForOrder $purpose): ?string;
}