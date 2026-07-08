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

function upgrade_module_1_5_4($module)
{
    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_finality'
            AND COLUMN_NAME = 'js_not_script';"
    );

    if (!$columnExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'cookiesplus_finality`
            ADD `js_not_script` TEXT NULL AFTER `js_script`;';

        Db::getInstance()->execute($query);
    }

    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_user_consent'
            AND COLUMN_NAME = 'data';"
    );

    if (!$columnExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . "cookiesplus_user_consent`
            ADD `data` text COLLATE 'utf8_general_ci' NOT NULL AFTER `hash`;";

        Db::getInstance()->execute($query);
    }

    $objects = scandir(_PS_MODULE_DIR_ . 'cookiesplus/consent/');
    if ($objects) {
        foreach ($objects as $object) {
            if ($object !== '.' && $object !== '..') {
                if (filetype(_PS_MODULE_DIR_ . 'cookiesplus/consent/' . $object) === 'dir') {
                    $module->recursiveRmdir(_PS_MODULE_DIR_ . 'cookiesplus/consent/' . $object);
                } else {
                    unlink(_PS_MODULE_DIR_ . 'cookiesplus/consent/' . $object);
                }
            }
        }
    }

    rmdir(_PS_MODULE_DIR_ . 'cookiesplus/consent/');

    return true;
}
