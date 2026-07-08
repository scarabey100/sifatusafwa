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

function upgrade_module_1_0_5()
{
    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
        AND TABLE_NAME = '" . _DB_PREFIX_ . "stockalert_alert'
        AND COLUMN_NAME = 'currencies';"
    );

    if ($columnExists) {
        Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'stockalert_alert`
            DROP `currencies`;'
        );
    }

    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
        AND TABLE_NAME = '" . _DB_PREFIX_ . "stockalert_alert'
        AND COLUMN_NAME = 'groups_excluded';"
    );

    if (!$columnExists) {
        Db::getInstance()->execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'stockalert_alert`
            ADD `groups_excluded` text NULL AFTER `groups`,
            ADD `customers_excluded` text NULL AFTER `customers`,
            ADD `countries_excluded` text NULL AFTER `countries`,
            ADD `zones_excluded` text NULL AFTER `zones`,
            ADD `categories_excluded` text NULL AFTER `categories`,
            ADD `products_excluded` text NULL AFTER `products`,
            ADD `manufacturers_excluded` text NULL AFTER `manufacturers`,
            ADD `suppliers_excluded` text NULL AFTER `suppliers`,
            ADD `attributes_excluded` text NULL AFTER `attributes`,
            ADD `features_excluded` text NULL AFTER `features`
            ;'
        );

        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . "stockalert_alert`
                SET `groups_excluded` = '',
                `customers_excluded` = '',
                `countries_excluded` = '',
                `zones_excluded` = '',
                `categories_excluded` = '',
                `products_excluded` = '',
                `manufacturers_excluded` = '',
                `suppliers_excluded` = '',
                `attributes_excluded` = '',
                `features_excluded` = '';"
        );
    }

    return true;
}
