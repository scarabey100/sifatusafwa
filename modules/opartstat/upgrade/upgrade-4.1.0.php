<?php

/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <manit4c@gmail.com>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_1_0($module)
{
    $res = Db::getInstance()->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . 'opartstat_sessions`
        ADD `userId` int(12) DEFAULT NULL'
    );

    //if(_PS_VERSION_ >= 1.7) {
    if (version_compare(_PS_VERSION_, '1.7', '>=')) {
        $res &= $module->registerHook('displayAdminCustomers');
        $res &= $module->registerHook('displayAdminOrderMainBottom');    
    }

    return $res;
}