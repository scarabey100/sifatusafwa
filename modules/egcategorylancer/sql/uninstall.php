<?php
/**
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */

$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'eg_category_lancer`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'eg_category_lancer_lang`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'eg_category_lancer_shop`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
