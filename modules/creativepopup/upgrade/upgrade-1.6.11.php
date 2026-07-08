<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_6_11($module)
{
    return Configuration::deleteByName('CP_DB_VERSION')
        && Configuration::deleteByName('CP_PLUGIN_VERSION');
}
