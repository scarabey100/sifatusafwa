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

function upgrade_module_4_2_0($module)
{
    $oldValue = Configuration::get('OPARTSTAT_USE_SAAS');
    $res = Configuration::deleteByName('OPARTSTAT_USE_SAAS');
    $res &= Configuration::updateValue('OPARTSTAT_PREMIUM_IS_ACTIVE', $oldValue);    

    return $res;
}