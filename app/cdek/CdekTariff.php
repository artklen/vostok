<?php

class CdekTariff
{
    /** @var int */
    public $code;

    /** @var float */
    public $sum;

    /** @var int */
    public $deliveryWorkingDaysMin;

    /** @var int */
    public $deliveryWorkingDaysMax;

    public function code(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function sum(float $sum): self
    {
        $this->sum = $sum;
        return $this;
    }

    public function deliveryWorkingDaysMin(int $deliveryWorkingDaysMin): self
    {
        $this->deliveryWorkingDaysMin = $deliveryWorkingDaysMin;
        return $this;
    }

    public function deliveryWorkingDaysMax(int $deliveryWorkingDaysMax): self
    {
        $this->deliveryWorkingDaysMax = $deliveryWorkingDaysMax;
        return $this;
    }
}