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

function upgrade_module_1_4_0($module)
{
    $tableExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_user_consent';"
    );

    if (!$tableExists) {
        $query = '
            CREATE TABLE IF NOT EXISTS ' . _DB_PREFIX_ . 'cookiesplus_user_consent (
                `id_cookiesplus_user_consent` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `id_shop` INT(11) UNSIGNED NOT NULL,
                `hash` text NOT NULL,
                `date` text NOT NULL,
                `ip` text NOT NULL,
                `date_add` datetime NOT NULL,
                PRIMARY KEY (`id_cookiesplus_user_consent`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
        ';

        Db::getInstance()->execute($query);
    }

    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_finality'
            AND COLUMN_NAME = 'date_add'"
    );

    if (!$columnExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'cookiesplus_finality`
            ADD `date_add` datetime NOT NULL AFTER `js_script`,
            ADD `date_upd` datetime NOT NULL AFTER `date_add`;';

        Db::getInstance()->execute($query);
    }

    $columnExists = Db::getInstance()->getRow(
        "SELECT *
        FROM information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = '" . _DB_NAME_ . "'
            AND TABLE_NAME = '" . _DB_PREFIX_ . "cookiesplus_cookie'
            AND COLUMN_NAME = 'date_add'"
    );

    if (!$columnExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'cookiesplus_cookie`
            ADD `date_add` datetime NOT NULL AFTER `provider_url`,
            ADD `date_upd` datetime NOT NULL AFTER `date_add`;';

        Db::getInstance()->execute($query);
    }

    $tab = [
        [
            'class_name' => 'AdminCookiesPlusUsersConsent',
            'parent_class_name' => 'COOKIES',
            'name' => [
                'en' => 'Users consent',
                'es' => 'Consentimiento de los usuarios',
                'de' => 'Zustimmung der Benutzer',
                'fr' => 'Consentement des utilisateurs',
                'it' => 'Consenso degli utenti',
                'nl' => 'Gebruikers toestemming',
                'pl' => 'Zgoda użytkowników',
                'pt' => 'Consentimento dos usuários',
                'ro' => 'Utilizatorii sunt de acord',
                'ru' => 'Согласие пользователей',
                'se' => 'Användarens samtycke',
            ],
            'module' => $module->name,
        ],
    ];

    $module->installTabs($tab);

    Configuration::updateValue('C_P_REVOKE_CONSENT', date('Y-m-d H:i:s', time()));

    return true;
}
