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

class CookiesPlusUserConsent extends ObjectModel
{
    public $id_cookiesplus_user_consent;
    public $id_shop;
    public $hash;
    public $data;
    public $date;
    public $ip;
    public $date_add;

    public static $definition = [
        'table' => 'cookiesplus_user_consent',
        'primary' => 'id_cookiesplus_user_consent',
        'fields' => [
            'id_cookiesplus_user_consent' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'id_shop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'hash' => ['type' => self::TYPE_STRING, 'required' => true],
            'data' => ['type' => self::TYPE_STRING, 'required' => true],
            'date' => ['type' => self::TYPE_STRING, 'required' => true],
            'ip' => ['type' => self::TYPE_STRING, 'required' => true],
            'date_add' => ['type' => self::TYPE_DATE, 'copy_post' => false],
        ],
    ];

    public function add($autodate = true, $null_values = false)
    {
        $this->id_shop = ($this->id_shop) ?: Context::getContext()->shop->id;

        return parent::add($autodate, $null_values);
    }

    public static function getCookiesPlusUserConsentExpired($shopId)
    {
        if (!Configuration::get('C_P_EXPIRY')) {
            return [];
        }

        $query = "SHOW TABLES LIKE '" . _DB_PREFIX_ . "cookiesplus_user_consent'";

        if (Db::getInstance()->executeS($query)) {
            $query = 'SELECT `id_cookiesplus_user_consent`
                FROM ' . _DB_PREFIX_ . 'cookiesplus_user_consent
                WHERE id_shop = ' . (int) $shopId . ' 
                    AND `date_add` < NOW() - INTERVAL ' . Configuration::get('C_P_EXPIRY') * 24 . ' HOUR;'
            ;

            return Db::getInstance()->executeS($query);
        }

        return [];
    }

    public static function getCookiesPlusUserConsentDataByHash($hash)
    {
        $query = 'SELECT `data` 
            FROM `' . _DB_PREFIX_ . "cookiesplus_user_consent`
            WHERE `hash` = '" . pSQL($hash) . "'";

        return Db::getInstance()->getValue($query);
    }
}
