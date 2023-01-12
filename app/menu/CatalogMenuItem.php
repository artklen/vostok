<?php

class CatalogMenuItem
{
    /** @var string */
    public $title;

    /** @var ?int */
    public $number;

    /** @var string */
    public $link;

    /** @var bool */
    public $duplicateHorizontally;

    /** @var string */
    public $adminControls = '';

    /** @var list<CatalogMenuItem> */
    public $submenu = [];

    /** @var string */
    public $submenuAdminControls = '';

    public function __construct(string $title, string $link, bool $duplicateHorizontally = true)
    {
        $this->title = $title;
        $this->link = $link;
        $this->duplicateHorizontally = $duplicateHorizontally;
    }
}