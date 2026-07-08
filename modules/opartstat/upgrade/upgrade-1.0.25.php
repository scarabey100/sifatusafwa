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

function upgrade_module_1_0_25($module)
{
    $result = true;

    $result &= Db::getInstance()->execute(
        'ALTER TABLE `'._DB_PREFIX_.'opartstat_sessions`
        ADD `utm_campaign` varchar(250) AFTER `shopId`,
        ADD `utm_medium` varchar(250) AFTER `shopId`'
    );

    return $result;
}