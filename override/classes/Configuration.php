<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Class Configuration
 */
class Configuration extends ConfigurationCore
{
    /**
     * {@inheritDoc}
     */
    /*
    * module: ets_geolocation
    * date: 2025-07-24 17:54:22
    * version: 1.3.5
    */
    public static function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        if ($key === 'PS_COUNTRY_DEFAULT'
            && Module::isEnabled('ets_geolocation')
            && !defined('_PS_ADMIN_DIR_')
            && debug_backtrace(~1)[1]['function'] === 'getAvailableCarrierList'
            && isset(Context::getContext()->country->id_zone)) {
            return Context::getContext()->country->id_zone;
        }
        return parent::get($key, $idLang, $idShopGroup, $idShop, $default);
    }
}
