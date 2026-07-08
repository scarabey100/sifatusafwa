<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$queries = [];

$queries[] = '
    CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stockalert_alert` (
        `id_stockalert_alert` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_shop` int unsigned NOT NULL,
        `active` tinyint(1) unsigned NOT NULL DEFAULT "0",
        `name` text NULL,
        `stock` int(10) NOT NULL,
        `out_of_stock` tinyint(1) unsigned NOT NULL DEFAULT "0",
        `send_mail` tinyint(1) unsigned NOT NULL DEFAULT "1",
        `send_mail_admin` tinyint(1) unsigned NOT NULL DEFAULT "0",
        `send_mail_admin_addresses` text NULL,
        `stock_over` int(10) NOT NULL,
        `groups` text NULL,
        `groups_excluded` text NULL,
        `customers` text NULL,
        `customers_excluded` text NULL,
        `countries` text NULL,
        `countries_excluded` text NULL,
        `zones` text NULL,
        `zones_excluded` text NULL,
        `categories` text NULL,
        `categories_excluded` text NULL,
        `products` text NULL,
        `products_excluded` text NULL,
        `manufacturers` text NULL,
        `manufacturers_excluded` text NULL,
        `suppliers` text NULL,
        `suppliers_excluded` text NULL,
        `attributes` text NULL,
        `attributes_excluded` text NULL,
        `features` text NULL,
        `features_excluded` text NULL,
        `priority` int(10) unsigned NOT NULL DEFAULT "0",
        `popup` tinyint(1) unsigned NOT NULL DEFAULT "0",
        `product_list` tinyint(1) unsigned NOT NULL DEFAULT "0",
        `product_list_product` tinyint(1) unsigned NOT NULL DEFAULT "0",
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
      PRIMARY KEY (`id_stockalert_alert`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';

$queries[] = '
    CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stockalert_alert_lang` (
        `id_stockalert_alert` int(10) unsigned NOT NULL,
        `id_lang` int unsigned NOT NULL,
        `custom_text` text NOT NULL,
      PRIMARY KEY (`id_stockalert_alert`, `id_lang`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;
';

$queries[] = '
    CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stockalert_subscriber` (
        `id_stockalert_subscriber` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_customer` int(10) unsigned NOT NULL,
        `customer_email` varchar(128) NOT NULL,
        `id_product` int(10) unsigned NOT NULL,
        `id_product_attribute` int(10) unsigned NOT NULL,
        `send_mail` tinyint(1) unsigned NOT NULL DEFAULT "1",
        `id_shop` int(10) unsigned NOT NULL,
        `id_lang` int(10) unsigned NOT NULL,
        `id_stockalert_alert` int(10) unsigned NOT NULL,
        `date_send` datetime NOT NULL,
        `date_add` datetime NOT NULL,
        PRIMARY KEY (`id_customer`,`customer_email`,`id_product`,`id_product_attribute`,`id_shop`, `date_send`),
        KEY `id_stockalert_subscriber` (`id_stockalert_subscriber`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';

foreach ($queries as $query) {
    Db::getInstance()->execute($query);
}

return true;
