<?php

namespace AthosFeed\Configuration;

final class ModuleConfiguration
{
    const MODULE_VERSION = '0.2.3';
    const PREFIX = 'ATHOS_FEED_';
    const MODE = 'MODE';
    const BATCH_SIZE = 'BATCH_SIZE';
    const MINIMUM_RECORDS = 'MINIMUM_RECORDS';
    const CURRENCY_ID = 'CURRENCY_ID';
    const COUNTRY_ID = 'COUNTRY_ID';
    const GROUP_ID = 'GROUP_ID';
    const INCLUDE_INACTIVE = 'INCLUDE_INACTIVE';
    const INCLUDE_OUT_OF_STOCK = 'INCLUDE_OUT_OF_STOCK';
    const ACCESS_TOKEN = 'ACCESS_TOKEN';
    const CRON_TOKEN = 'CRON_TOKEN';
    const ALLOWED_IPS = 'ALLOWED_IPS';
    const FRONTEND_ENABLED = 'FRONTEND_ENABLED';
    const SNAP_SCRIPT_URL = 'SNAP_SCRIPT_URL';
    const SNAP_PUBLIC_CONFIG = 'SNAP_PUBLIC_CONFIG';
    const ACCOUNT_ID = 'ACCOUNT_ID';
    const INDEX_ID = 'INDEX_ID';
    const SEARCH_ENABLED = 'SEARCH_ENABLED';
    const CATEGORY_ENABLED = 'CATEGORY_ENABLED';
    const PRODUCT_ZONE = 'PRODUCT_ZONE';
    const CART_ZONE = 'CART_ZONE';
    const STATUS = 'STATUS';

    public static function key($name)
    {
        return self::PREFIX . $name;
    }

    public static function get($name, $shopId = null, $default = null)
    {
        $shopId = $shopId === null && isset(\Context::getContext()->shop->id) ? (int) \Context::getContext()->shop->id : (int) $shopId;
        $value = \Configuration::get(self::key($name), null, null, $shopId);
        return $value === false ? $default : $value;
    }

    public static function set($name, $value, $shopId = null, $html = false)
    {
        $shopId = $shopId === null && isset(\Context::getContext()->shop->id) ? (int) \Context::getContext()->shop->id : (int) $shopId;
        return \Configuration::updateValue(self::key($name), $value, (bool) $html, null, $shopId);
    }

    public static function deleteAll()
    {
        foreach (self::names() as $name) {
            \Configuration::deleteByName(self::key($name));
        }
    }

    public static function names()
    {
        return array(self::MODE, self::BATCH_SIZE, self::MINIMUM_RECORDS, self::CURRENCY_ID, self::COUNTRY_ID, self::GROUP_ID,
            self::INCLUDE_INACTIVE, self::INCLUDE_OUT_OF_STOCK, self::ACCESS_TOKEN, self::CRON_TOKEN,
            self::ALLOWED_IPS, self::FRONTEND_ENABLED, self::SNAP_SCRIPT_URL, self::SNAP_PUBLIC_CONFIG, self::ACCOUNT_ID, self::INDEX_ID,
            self::SEARCH_ENABLED, self::CATEGORY_ENABLED, self::PRODUCT_ZONE, self::CART_ZONE, self::STATUS);
    }

    public static function randomToken()
    {
        return bin2hex(random_bytes(32));
    }
}
