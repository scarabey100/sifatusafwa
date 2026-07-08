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

function upgrade_module_2_0_13($module)
{
    $res = true;
    
    $tab6 = new Tab();
    $tab6->module = 'opartstat';
    $tab6->active = 0;
    $tab6->class_name = 'AdminOpartStatSettingsTrackableLinksCreator';
    $tab6->id_parent = (int)Tab::getIdFromClassName('AdminStats');
    $tab6->position = Tab::getNewLastPosition($tab6->id_parent);
    foreach (Language::getLanguages(false) as $lang) {
        if ($lang['iso_code'] == "fr") {
            $tab6->name[(int)$lang['id_lang']] = 'Création de liens trackés';
        } else {
            $tab6->name[(int)$lang['id_lang']] = 'Trackable links creator';
        }
    }
    $res &= $tab6->add();

    $res &= Db::getInstance()->execute(
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_trackable_links_preset` (
            `presetId` int(10) NOT NULL AUTO_INCREMENT,
            `utmSource` varchar(250),
            `utmMedium` varchar(250),
            `utmCampaign` varchar(250),
            PRIMARY KEY (`presetId`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8'
    );

    return $res;
}