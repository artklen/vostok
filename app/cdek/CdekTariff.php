<?php

class CdekTariff
{
    /** @var int */
    public $code;

    /** @var string */
    public $name;

    /** @var string */
    public $description;

    /** @var int */
    public $deliveryMode;

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

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function deliveryMode(int $deliveryMode): self
    {
        $this->deliveryMode = $deliveryMode;
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