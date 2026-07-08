<?php
/**
 * 2019 (c) Egio digital
 *
 * MODULE EgWishList
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'egwishlist_product`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
