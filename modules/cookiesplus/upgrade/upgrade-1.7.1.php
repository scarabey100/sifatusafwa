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

function upgrade_module_1_7_1($module)
{
    Configuration::updateValue('C_P_GTM_URL_PASSTHROUGH', false);
    Configuration::updateValue('C_P_GTM_ADS_DATA_REDACTION', true);

    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_finality'
            AND COLUMN_NAME = 'body_code';"
    );

    if (!$columnExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'cookiesplus_finality`
            ADD `body_code` TEXT NULL AFTER `js_script`;';

        Db::getInstance()->execute($query);
    }

    $tableExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_stats';"
    );

    if (!$tableExists) {
        $query = '
            CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_stats (
              `id_cookiesplus_stats` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `action` int NOT NULL,
              `date_add` datetime NOT NULL
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        Db::getInstance()->execute($query);
    }

    $newTab = [
        [
            'class_name' => 'AdminCookiesPlusIntegration',
            'parent_class_name' => 'COOKIES',
            'name' => [
                'en' => 'Integration with other platforms',
                'es' => 'Integración con otras plataformas',
                'de' => 'Integration mit anderen Plattformen',
                'fr' => 'Intégration avec d\'autres plateformes',
                'it' => 'Integrazione con altre piattaforme',
                'nl' => 'Integratie met andere platforms',
                'pl' => 'Integracja z innymi platformami',
                'pt' => 'Integração com outras plataformas',
                'ro' => 'Integrare cu alte platforme',
                'ru' => 'Интеграция с другими платформами',
                'se' => 'Integration med andra plattformar',
            ],
            'module' => $module->name,
        ],
    ];

    $module->installTabs($newTab, $force = true);

    $tabsToDelete = [
        'AdminCookiesPlusGTM',
        'AdminCookiesPlusFB',
        'AdminCookiesPlusYT',
    ];

    foreach ($tabsToDelete as $tabToDelete) {
        $tab = new Tab(Tab::getIdFromClassName($tabToDelete));
        $tab->delete();
    }

    Tools::generateHtaccess();

    $module->unregisterHook('actionHtaccessCreate');

    return true;
}
