<?php

/**
 * @see doitClass::catalog_menu
 */
d()->singleton('catalog_menu', static function () {
    $result = [];

    $result[] = new CatalogMenuItem(t('Все часы'), '/catalog/chasy', false);

    /** @var Menu_main_element $menuMainElement */
    foreach (d()->Menu_main_element as $mainElement) {
        $mainItem = new CatalogMenuItem(
            $mainElement->title,
            $mainElement->page_url,
            (bool) $mainElement->is_duplicate_horizontally
        );
        $mainItem->adminControls = return_printed(static function () use ($mainElement) {
            edit([$mainElement]);
            delete([$mainElement]);
        });
        $mainItem->submenuAdminControls = return_printed(static function () use ($mainElement) {
            add(['menu_elements', 'menu_main_element_id' => $mainElement->id]);
            sort_icon();
        });

        $result[] = $mainItem;

        /** @var Menu_element $menuElement */
        foreach (d()->Menu_element->where('`menu_main_element_id`=?', $mainElement->id)->all() as $menuElement) {
            $item = new CatalogMenuItem($menuElement->title, $menuElement->link());

            $number = $menuElement->number();
            if ($number !== 0) {
                $item->number = $number;
            }

            $item->adminControls = return_printed(static function () use ($menuElement) {
                edit([$menuElement]);
                delete([$menuElement]);
            });

            $mainItem->submenu[] = $item;
        }
    }

    $result[] = new CatalogMenuItem(t('Часы с символикой'), '/symbols');

    return $result;
});

function return_printed(callable $callable)
{
    ob_start();
    $callable();
    return ob_get_clean();
}
