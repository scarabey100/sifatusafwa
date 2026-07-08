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

function upgrade_module_3_3_34($module)
{
    $res = Db::getInstance()->execute(
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_shared_reports` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `reportName` varchar(255),
            `ownerUserId` int(10),
            `guestUserId` int(10),
            `rights` int(1),
            PRIMARY KEY (`id`),
            UNIQUE KEY unique_report_access (reportName, ownerUserId, guestUserId)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
    );
    return $res;
}