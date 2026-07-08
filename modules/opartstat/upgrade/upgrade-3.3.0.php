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

function upgrade_module_3_3_0($module)
{
    $res = true;    
    $res &= Configuration::updateValue('OPARTSTAT_PURGE_CACHE_DELAY', '7');
    $res &= Configuration::updateValue('OPARTSTAT_CACHE_LAST_PURGE', '0000-00-00 00:00:00');
    $res &= Configuration::updateValue('OPARTSTAT_CACHE_FILE_MAX_AGE', '90');
    return $res;
}
