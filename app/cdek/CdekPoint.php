<?php

class CdekPoint
{
    /** @var string */
    public $code;

    /** @var string */
    public $name;

    /** @var string */
    public $address;

    /** @var string */
    public $comment;

    /** @var string */
    public $coords;

    /** @var string */
    public $workingHours;

    public function code($code): self
    {
        $this->code = $code;
        return $this;
    }

    public function name($name): self
    {
        $this->name = $name;
        return $this;
    }

    public function address($address): self
    {
        $this->address = $address;
        return $this;
    }

    public function comment($comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    public function coords($coords): self
    {
        $this->coords = $coords;
        return $this;
    }

    public function workingHours($workingHours): self
    {
        $this->workingHours = $workingHours;
        return $this;
    }
}