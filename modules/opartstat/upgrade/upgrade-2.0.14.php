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

function upgrade_module_2_0_14($module)
{
    $res = true;
    
    $tab7 = new Tab();
    $tab7->module = 'opartstat';
    $tab7->active = 0;
    $tab7->class_name = 'AdminOpartStatSettingsIps';
    $tab7->id_parent = (int)Tab::getIdFromClassName('AdminStats');
    $tab7->position = Tab::getNewLastPosition($tab7->id_parent);
    foreach (Language::getLanguages(false) as $lang) {
        if ($lang['iso_code'] == "fr") {
            $tab7->name[(int)$lang['id_lang']] = 'Blocage d\'IP';
        } else {
            $tab7->name[(int)$lang['id_lang']] = 'IP Blocking';
        }
    }
    $res &= $tab7->add();

    $res &= Db::getInstance()->execute(
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_ips_blocking` (
        `ipId` int(10) NOT NULL AUTO_INCREMENT,
        `ip` varchar(64) NOT NULL,
        PRIMARY KEY (`ipId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
    );
    return $res;
}