<?php

final class CdekCity
{
    /** @var string */
    public $code;

    /** @var string */
    public $title;

    /** @var string */
    public $region;

    /** @var string */
    public $subregion;

    /** @var string */
    public $fias;

    public function code($code): self
    {
        $this->code = $code;
        return $this;
    }

    public function title($title): self
    {
        $this->title = $title;
        return $this;
    }

    public function region($region): self
    {
        $this->region = $region;
        return $this;
    }

    public function subregion($subregion): self
    {
        $this->subregion = $subregion;
        return $this;
    }

    public function fias($fias): self
    {
        $this->fias = $fias;
        return $this;
    }
}