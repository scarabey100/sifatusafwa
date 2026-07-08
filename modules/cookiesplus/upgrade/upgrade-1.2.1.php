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

function upgrade_module_1_2_1($module)
{
    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_finality'
            AND COLUMN_NAME = 'position';"
    );

    if (!$columnExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'cookiesplus_finality`
            ADD `position` INT(11) AFTER `js_script`;';

        Db::getInstance()->execute($query);

        $query = 'UPDATE `' . _DB_PREFIX_ . 'cookiesplus_finality`
            JOIN (SELECT @rank := -1) r
            SET position=@rank:=@rank+1;';

        Db::getInstance()->execute($query);
    }

    $indexExists = Db::getInstance()->execute(
        'SHOW index FROM `' . _DB_PREFIX_ . "cookiesplus_finality_lang` WHERE column_name = 'id_lang';"
    );

    if (!$indexExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'cookiesplus_finality_lang`
                ADD INDEX `id_lang` (`id_lang`);';

        Db::getInstance()->execute($query);
    }

    if (!is_writable(_PS_MODULE_DIR_ . $module->name)) {
        return false;
    }

    $override_folder_name = 'override';

    // Remove /override_previous to /override
    $version_override_folder = _PS_MODULE_DIR_ . $module->name . '/' . $override_folder_name . '_previous';
    $override_folder = _PS_MODULE_DIR_ . $module->name . '/' . $override_folder_name;

    if (file_exists($override_folder) && is_dir($override_folder)) {
        $module->recursiveRmdir($override_folder);
    }

    if (is_dir($version_override_folder)) {
        $module->copyDir($version_override_folder, $override_folder);
    }

    $module->removeOverride('Hook');

    // Rename Rename /override_new to /override
    $psVersion = Tools::substr(str_replace('.', '', _PS_VERSION_), 0, 2);
    $version_override_folder = _PS_MODULE_DIR_ . $module->name . '/' . $override_folder_name . '_' . $psVersion;
    $override_folder = _PS_MODULE_DIR_ . $module->name . '/' . $override_folder_name;

    if (file_exists($override_folder) && is_dir($override_folder)) {
        $module->recursiveRmdir($override_folder);
    }

    if (is_dir($version_override_folder)) {
        $module->copyDir($version_override_folder, $override_folder);
    }

    $module->addOverride('Hook');

    return true;
}
