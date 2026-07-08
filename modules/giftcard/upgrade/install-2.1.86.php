<?php
/**
 * GIFT CARD
 *
 * @author    EIRL Timactive De Véra
 * @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 * @license   Commercial license
 *
 * @category pricing_promotion
 *
 * @version 1.1.0
 *************************************
 **         GIFT CARD                *
 **          V 1.0.0                 *
 *************************************
 * +
 * + Languages: EN, FR, ES
 * + PS version: 1.5,1.6,1.7
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_86($object, $install = false)
{
    Configuration::updateValue('GIFTCARD_COUPON_TO_PAYMENT', 0);
    // create table for order cart rule move in payment
    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcard_order_usage` (
        `id_order_usage` int(11) NOT NULL AUTO_INCREMENT,
        `id_cart_rule` int(11) NOT NULL,
        `id_order` int(11) NOT NULL,
        `value_tax_excl` decimal(20,6) NOT NULL,
        `value_tax_incl` decimal(20,6) NOT NULL,
        PRIMARY KEY (`id_order_usage`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';
    if (!Db::getInstance()->execute($sql)) {
        return false;
    }

    return true;
}
