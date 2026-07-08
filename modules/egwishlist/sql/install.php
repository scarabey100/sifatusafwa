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

/* Information on content EG egwishlist_product */
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'egwishlist_product` (
        `id_egwishlist_product` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_product` int(10) unsigned NOT NULL,
        `id_product_attribute` int(10) unsigned NOT NULL,
        `id_customer` int(10) unsigned NOT NULL,
        `id_shop` int(10) unsigned NOT NULL,
        `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id_egwishlist_product`)
    ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8 ;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}


