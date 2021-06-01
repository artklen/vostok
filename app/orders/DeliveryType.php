<?php

final class DeliveryType extends BasicEnum
{
    public const POST = '1';
    public const EMS = '2';
    public const CDEK_POINT = '3';
    public const CDEK_COURIER = '5';
    public const PICKUP = '4';

    /** @var string */
    private $value;

    /** @var Delivery_variant */
    private $variant;

    public function __construct($value)
    {
        $this->value = $value;
        $this->variant = d()->Delivery_variant->f($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function variant(): Delivery_variant
    {
        return $this->variant;
    }

    public static function checkoutTemplates(): array
    {
        foreach (self::getConstants() as $name => $value) {
            $template = '/basket/delivery_variants/' . strtolower($name) . '.html';

            if (! is_file($_SERVER['DOCUMENT_ROOT'] . '/app' . $template)) {
                continue;
            }

            $result[$value] = $template;
        }

        return $result ?? [];
    }

}