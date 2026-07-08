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

function upgrade_module_1_1_0($module)
{
    Configuration::updateValue('HIFAQ_SIDEBAR_POSITION', 'left');
    Configuration::updateValue('HIFAQ_SEARCH', true);
    Configuration::updateValue('HIFAQ_LAYOUT', 3);
    Configuration::updateValue('HIFAQ_FAQS_COUNT', 3);
    Configuration::updateValue('HIFAQ_RELATED_PRODUCTS', true);
    Configuration::updateValue('HIFAQ_STRUCTURED_DATA', true);
    Configuration::updateValue('HIFAQ_FAQS_URL', 'faqs');
    Configuration::updateValue('HIFAQ_CATEGORY_URL', 'category');
    Configuration::updateValue('HIFAQ_DETAILS_URL', 'faq');
    Configuration::updateValue('HIFAQ_SEARCH_URL', 'search');

    $module->registerHook('overrideLayoutTemplate');
    $module->registerHook('displayHiFAQ');
    $module->registerHook('displayHiFAQProduct');

    // We'll not use category image anymore
    Db::getInstance()->Execute('ALTER TABLE `' . _DB_PREFIX_ . 'hifaqcategory` DROP COLUMN `image`');

    // Add category position for sorting
    $module->hiPrestaClass->addTableColumn('hifaqcategory', 'position', 'int unsigned', 'active');
    Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'hifaqcategory` SET position = id');

    // Add FAQ new columns
    $module->hiPrestaClass->addTableColumn('hifaq', 'position', 'int unsigned', 'active');
    Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'hifaq` SET position = id_faq');

    $module->hiPrestaClass->addTableColumn('hifaq_lang', 'meta_title', 'varchar (255)');
    $module->hiPrestaClass->addTableColumn('hifaq_lang', 'meta_description', 'varchar (255)');
    $module->hiPrestaClass->addTableColumn('hifaq_lang', 'meta_keywords', 'varchar (255)');
    $module->hiPrestaClass->addTableColumn('hifaq_lang', 'friendly_url', 'varchar (255)');

    // reset blocks table
    Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'hifaqcustomlist`');
    Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'hifaqcustomlist_lang`');
    Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'hifaqmultiplelist`');

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblock` (
            `id_block` int unsigned NOT NULL AUTO_INCREMENT,
            `active` TINYINT  NOT NULL,
            `title_active` TINYINT  NOT NULL,
            `type` varchar (100) NOT NULL,
            `count` int NOT NULL,
            `hook` varchar (100) NOT NULL,
            `accordion` TINYINT  NOT NULL,
            `position` int unsigned NOT NULL,
            PRIMARY KEY (`id_block`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblock_lang` (
            `id_block` int unsigned NOT NULL,
            `id_lang` int unsigned NOT NULL,
            `title` varchar(255) NOT NULL,
          PRIMARY KEY (`id_block`,`id_lang`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqblockfaqs` (
            `id_block` int unsigned NOT NULL,
            `id_faq` int unsigned NOT NULL,
          PRIMARY KEY (`id_block`, `id_faq`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    // related products
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqrelatedproduct` (
            `id_hifaqrelatedproduct` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_faq` int(10) unsigned NOT NULL,
            `id_product` int(10) unsigned NOT NULL,
            `position` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_hifaqrelatedproduct`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    $module->hiPrestaClass->deleteTableColumn('hifaq', 'id_product');

    // related categories
    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqrelatedcategory` (
            `id_hifaqrelatedcategory` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_faq` int(10) unsigned NOT NULL,
            `id_category` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id_hifaqrelatedcategory`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    return true;
}
