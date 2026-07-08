<?php
/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_5_0($module)
{
    if ($module->psv >= 1.7) {
        Configuration::updateValue('HIFAQ_ICONS', 'material');
    } else {
        Configuration::updateValue('HIFAQ_ICONS', 'fontAwesome');
    }

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqrelatedproductfeature` (
            `id_hifaqrelatedproductfeature` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_faq` int(10) unsigned NOT NULL,
            `id_feature` int(10) unsigned NOT NULL,
            `id_feature_value` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_hifaqrelatedproductfeature`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    $productPageHook = Configuration::get('HI_FAQ_PRODUCT_PAGE_HOOK');
    if ($productPageHook == 'displayProductButtons') {
        Configuration::updateValue('HI_FAQ_PRODUCT_PAGE_HOOK', 'displayProductAdditionalInfo');
    }

    return true;
}
