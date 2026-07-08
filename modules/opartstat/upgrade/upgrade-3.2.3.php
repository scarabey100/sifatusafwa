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
require_once(dirname(__FILE__) . '/../classes/opartStatTools.php');

function upgrade_module_3_2_3($module)
{
    $res = Db::getInstance()->execute(
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_config` (
            `id` int(10) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) UNIQUE,
            `value` varchar(512),
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
    );

    $res &= OpartStatTools::createSecretTokenKey();

    $tab9 = new Tab();
    $tab9->module = 'opartstat';
    $tab9->active = 0;
    $tab9->class_name = 'AdminOpartStatSettingsSubscription';
    $tab9->id_parent = (int)Tab::getIdFromClassName('AdminStats');
    $tab9->position = Tab::getNewLastPosition($tab9->id_parent);
    foreach (Language::getLanguages(false) as $lang) {
        if ($lang['iso_code'] == "fr") {
            $tab9->name[(int)$lang['id_lang']] = 'OpartStat abonnement';
        } else {
            $tab9->name[(int)$lang['id_lang']] = 'OpartStat subscription';
        }
    }
    $res &= $tab9->add();

    return $res;
}
