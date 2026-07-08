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

function upgrade_module_1_4_0($module)
{
    Configuration::updateValue('HIFAQ_FEEDBACK', false);
    Configuration::updateValue('HIFAQ_FEEDBACK_POSITION', 1);
    Configuration::updateValue('HIFAQ_FEEDBACK_ACCORDION', 0);
    Configuration::updateValue('HIFAQ_FEEDBACK_COUNT', 0);

    Db::getInstance()->execute('
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'hifaqfeedback` (
            `id_feedback` int(10) unsigned not null auto_increment,
            `id_faq` int(10) unsigned not null,
            `id_customer` int(10) unsigned not null,
            `id_guest` int(10) unsigned not null,
            `ip_address` varchar(100) not null,
            `feedback` tinyint not null,
            `comment` text not null,
            `date_add` datetime NOT NULL,
            `date_upd` datetime NOT NULL,
            PRIMARY KEY (`id_feedback`, `id_faq`, `id_customer`, `id_guest`, `ip_address`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
    ');

    return true;
}
