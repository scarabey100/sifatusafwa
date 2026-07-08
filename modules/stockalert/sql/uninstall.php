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

$tableExists = Db::getInstance()->getRow(
    "SELECT *
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "'
        AND TABLE_NAME = '" . _DB_PREFIX_ . "stockalert_alert';"
);

if ($tableExists) {
    $queries[] =
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stockalert_alert_' . time() . '` AS SELECT * FROM `' . _DB_PREFIX_ . 'stockalert_alert`;
';
}

$tableExists = Db::getInstance()->getRow(
    "SELECT *
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "'
        AND TABLE_NAME = '" . _DB_PREFIX_ . "stockalert_alert_lang';"
);

if ($tableExists) {
    $queries[] =
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stockalert_alert_lang_' . time() . '` AS SELECT * FROM `' . _DB_PREFIX_ . 'stockalert_alert_lang`;
';
}

$tableExists = Db::getInstance()->getRow(
    "SELECT *
    FROM information_schema.TABLES
    WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "'
        AND TABLE_NAME = '" . _DB_PREFIX_ . "stockalert_subscriber';"
);

if ($tableExists) {
    $queries[] =
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'stockalert_subscriber_' . time() . '` AS SELECT * FROM `' . _DB_PREFIX_ . 'stockalert_subscriber`;
';
}

$queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'stockalert_alert`;';
$queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'stockalert_alert_lang`;';
$queries[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'stockalert_subscriber`;';

foreach ($queries as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}

return true;
