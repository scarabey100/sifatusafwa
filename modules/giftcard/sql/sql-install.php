<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = [];
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardproduct` (
				`id_product` int(10) NOT NULL,
				`period_val` int(3) NOT NULL,
				`amount` decimal(20,6) NOT NULL DEFAULT \'0.000000\',
				`virtualcard` tinyint(1) NOT NULL DEFAULT \'0\',
				`id_customization_field_lastname` int(10) NOT NULL,
				`id_customization_field_from` int(10) NOT NULL,
				`id_customization_field_mailto` int(10) NOT NULL,
				`id_customization_field_message` int(10) NOT NULL,
				`id_currency` int(10) NOT NULL,
				`id_customization_field_deliverydate` int(10) NOT NULL,
				`id_customization_field_template` int(10) NOT NULL,
				`id_customization_field_image` int(10) NOT NULL,
				`isdefaultgiftcard` tinyint(1) NOT NULL DEFAULT \'0\',
				`cr_free_shipping` tinyint(1) NOT NULL DEFAULT \'0\',
				`cr_partial_use` tinyint(1) NOT NULL DEFAULT \'1\',
PRIMARY KEY (`id_product`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardorder` (
				`id_gift_card_order` int(10) NOT NULL AUTO_INCREMENT,
				`id_product` int(10) NOT NULL,
				`customer_mail` varchar(128) NOT NULL,
				`to_mail` varchar(128) DEFAULT NULL,
				`lastname` varchar(128) NOT NULL,
				`from` varchar(128) DEFAULT NULL,
				`id_order` int(10) NOT NULL,
				`id_cart` int(10) NOT NULL,
				`id_currency` int(10) NOT NULL,
				`quantity` int(10) NOT NULL,
				`discountcode` varchar(255) DEFAULT NULL,
				`status` varchar(15) NOT NULL,
				`message` varchar(255) DEFAULT NULL,
				`id_lang` int(10) NOT NULL,
				`id_cart_rule` int(10) DEFAULT NULL,
				`price` decimal(20,6) NOT NULL,
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				`info` text,
				`sended` tinyint(1) DEFAULT \'0\',
				`receptmode` tinyint(1) DEFAULT \'0\',
				`period_val` int(3) NOT NULL DEFAULT \'0\',
				`delivery_date` date DEFAULT NULL,
				`id_gift_card_template` int(10) DEFAULT NULL,
				`id_customization` int(10) DEFAULT NULL,
PRIMARY KEY (`id_gift_card_order`)
) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate` (
				`id_gift_card_template` int(11) NOT NULL AUTO_INCREMENT,
				`active` tinyint(1) NOT NULL DEFAULT \'0\',
				`id_lang_display` int(10) NOT NULL,
				`issvg` tinyint(1) NOT NULL,
				`isdefault` tinyint(1) NOT NULL DEFAULT \'0\',
				`date_add` datetime NOT NULL,
				`date_upd` datetime NOT NULL,
				`var_color1` varchar(32) DEFAULT NULL,
				`var_color2` varchar(32) DEFAULT NULL,
				`var_color3` varchar(32) DEFAULT NULL,
				`var_color4` varchar(32) DEFAULT NULL,
				`var_color5` varchar(32) DEFAULT NULL,
				`var_color6` varchar(32) DEFAULT NULL,
				`var_color7` varchar(32) DEFAULT NULL,
				`var_color8` varchar(32) DEFAULT NULL,
				`var_color9` varchar(32) DEFAULT NULL,
				`var_color10` varchar(32) DEFAULT NULL,
				`var_imgpath1` varchar(155) DEFAULT NULL,
				`var_imgpath2` varchar(155) DEFAULT NULL,
				`var_code_default` varchar(155) DEFAULT NULL,
				`var_lastname_default` varchar(155) DEFAULT NULL,
				`var_from_default` varchar(155) DEFAULT NULL,
				`var_expirate_default` varchar(155) DEFAULT NULL,
				`var_message_default` text,
				`var_price_default` decimal(20,6) DEFAULT NULL,
				`pdf_image_only` tinyint(1) NOT NULL DEFAULT \'0\',
				`physicaluse` tinyint(1) NOT NULL DEFAULT \'1\',
				`virtualuse` tinyint(1) NOT NULL DEFAULT \'1\',
PRIMARY KEY (`id_gift_card_template`)
) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate_lang`(
				`id_gift_card_template` int(10) NOT NULL,
				`id_lang` int(10) NOT NULL,
				`name` varchar(128) NOT NULL,
				`var_text1` varchar(255) DEFAULT NULL,
				`var_text2` varchar(255) DEFAULT NULL,
				`var_text3` varchar(255) DEFAULT NULL,
				`var_text4` varchar(255) DEFAULT NULL,
				`var_text5` varchar(255) DEFAULT NULL,
				`var_text6` varchar(255) DEFAULT NULL,
				`var_text7` varchar(255) DEFAULT NULL,
				`var_text8` varchar(255) DEFAULT NULL,
				`var_text9` varchar(255) DEFAULT NULL,
				`var_text10` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id_gift_card_template`,`id_lang`)
) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardtag` (
				`id_gift_card_tag` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`id_lang` int(10) unsigned NOT NULL,
				`name` varchar(32) NOT NULL,
PRIMARY KEY (`id_gift_card_tag`)
)  ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate_tag` (
				`id_gift_card_tag` int(10) NOT NULL,
				`id_gift_card_template` int(10) NOT NULL,
PRIMARY KEY (`id_gift_card_tag`,`id_gift_card_template`)
) ENGINE=' . _MYSQL_ENGINE_ . ' default CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate_shop` (
				`id_gift_card_template` int(11) unsigned NOT NULL,
				`id_shop` int(11) unsigned NOT NULL,
PRIMARY KEY (`id_gift_card_template`,`id_shop`),
KEY `id_shop` (`id_shop`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcard_order_usage` (
                `id_order_usage` int(11) NOT NULL AUTO_INCREMENT,
                `id_cart_rule` int(11) NOT NULL,
                `id_order` int(11) NOT NULL,
                `value_tax_excl` decimal(20,6) NOT NULL,
                `value_tax_incl` decimal(20,6) NOT NULL,
                PRIMARY KEY (`id_order_usage`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
