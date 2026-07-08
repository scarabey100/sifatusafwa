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
    CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_finality (
        `id_cookiesplus_finality` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_shop` INT(11) UNSIGNED NOT NULL,
        `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
        `technical` tinyint(1) unsigned NOT NULL,
        `modules` TEXT NULL,
        `js_script` TEXT NULL,
        `body_code` TEXT NULL,
        `js_not_script` TEXT NULL,
        `position` INT(11),
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_cookiesplus_finality`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';

$queries[] = '
    CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_finality_lang (
        `id_cookiesplus_finality` int(11) unsigned NOT NULL,
        `id_lang` int(11) unsigned NOT NULL,
        `name` varchar(64) NOT NULL,
        `description` text NULL,
        PRIMARY KEY `id_cookiesplus_finality_id_lang` (`id_cookiesplus_finality`, `id_lang`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
;

$queries[] = '
    CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_cookie (
        `id_cookiesplus_cookie` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_shop` INT(11) UNSIGNED NOT NULL,
        `active` tinyint(1) unsigned NOT NULL DEFAULT 1,
        `id_cookiesplus_finality` int(11) unsigned NOT NULL,
        `name` text NOT NULL,
        `provider` TEXT NULL,
        `provider_url` TEXT NULL,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_cookiesplus_cookie`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';

$queries[] = '
    CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_cookie_lang (
        `id_cookiesplus_cookie` int(11) unsigned NOT NULL,
        `id_lang` int(11) unsigned NOT NULL,
        `purpose` text NULL,
        `expiry` text NULL,
        PRIMARY KEY `id_cookiesplus_cookie_id_lang` (`id_cookiesplus_cookie`, `id_lang`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;'
;

$queries[] = '
    CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_user_consent (
        `id_cookiesplus_user_consent` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `id_shop` INT(11) UNSIGNED NOT NULL,
        `hash` varchar(41) NOT NULL,
        `data` text NOT NULL,
        `date` datetime NOT NULL,
        `ip` varchar(39) NOT NULL,
        `date_add` datetime NOT NULL,
        PRIMARY KEY (`id_cookiesplus_user_consent`),
        INDEX `idx_hash` (`hash`),
        INDEX `idx_shop_date` (id_shop, date_add)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';

$queries[] = '
    CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_stats (
      `id_cookiesplus_stats` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `action` int NOT NULL,
      `date_add` datetime NOT NULL,
      INDEX idx_date_action (date_add, action)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
';

foreach ($queries as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
