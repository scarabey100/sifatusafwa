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

function upgrade_module_1_2_0($module)
{
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaq_shop` (
            `id_faq` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id_faq`,`id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqcategory_shop` (
            `id` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
          PRIMARY KEY (`id`,`id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblock_shop` (
            `id_block` int(10) unsigned NOT NULL,
            `id_shop` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_block`, `id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    $id_shop = (int) Configuration::get('PS_SHOP_DEFAULT');

    $faqs = Db::getInstance()->executeS('SELECT `id_faq` from `' . _DB_PREFIX_ . 'hifaq`');
    if (is_array($faqs) && $faqs) {
        foreach ($faqs as $faq) {
            Db::getInstance()->insert('hifaq_shop', [
                'id_faq' => (int) $faq['id_faq'],
                'id_shop' => (int) $id_shop,
            ]);
        }
    }

    $categories = Db::getInstance()->executeS('SELECT `id` from `' . _DB_PREFIX_ . 'hifaqcategory`');
    if (is_array($categories) && $categories) {
        foreach ($categories as $category) {
            Db::getInstance()->insert('hifaqcategory_shop', [
                'id' => (int) $category['id'],
                'id_shop' => (int) $id_shop,
            ]);
        }
    }

    $blocks = Db::getInstance()->executeS('SELECT `id_block` from `' . _DB_PREFIX_ . 'hifaqblock`');
    if (is_array($blocks) && $blocks) {
        foreach ($blocks as $block) {
            Db::getInstance()->insert('hifaqblock_shop', [
                'id_block' => (int) $block['id_block'],
                'id_shop' => (int) $id_shop,
            ]);
        }
    }

    return true;
}
