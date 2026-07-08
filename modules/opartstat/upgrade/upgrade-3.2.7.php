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

function upgrade_module_3_2_7($module)
{
    $res = Db::getInstance()->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . 'opartstat_sessions`
        ADD `gclid` varchar(255) DEFAULT NULL'
    );

    return $res;
}
