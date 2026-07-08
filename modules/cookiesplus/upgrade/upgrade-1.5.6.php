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

function upgrade_module_1_5_6()
{
    $query = 'DELETE FROM `' . _DB_PREFIX_ . "cookiesplus_user_consent`
        WHERE `data` = '';";

    Db::getInstance()->execute($query);

    $query = 'ALTER TABLE `' . _DB_PREFIX_ . "cookiesplus_user_consent`
        CHANGE `hash` `hash` varchar(41) COLLATE 'utf8_general_ci' NOT NULL AFTER `id_shop`;";

    Db::getInstance()->execute($query);

    $query = 'ALTER TABLE `' . _DB_PREFIX_ . "cookiesplus_user_consent`
        CHANGE `ip` `ip` varchar(39) COLLATE 'utf8_general_ci' NOT NULL AFTER `date`;";

    Db::getInstance()->execute($query);

    $indexExists = Db::getInstance()->executeS(
        'SHOW index FROM `' . _DB_PREFIX_ . "cookiesplus_user_consent` WHERE column_name = 'hash';"
    );

    if (!$indexExists) {
        $query = 'ALTER TABLE `' . _DB_PREFIX_ . 'cookiesplus_user_consent`
                ADD INDEX `hash` (`hash`);';

        Db::getInstance()->execute($query);
    }

    return true;
}
