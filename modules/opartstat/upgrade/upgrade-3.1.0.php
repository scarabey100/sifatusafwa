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

function upgrade_module_3_1_0($module)
{

    $res = true;

    if(!Configuration::updateValue('OPARTSTAT_USE_COMMISSIONS', '0'))
        $res = false;
    
    $tab8 = new Tab();
    $tab8->module = 'opartstat';
    $tab8->active = 0;
    $tab8->class_name = 'AdminOpartStatSettingsCommissions';
    $tab8->id_parent = (int)Tab::getIdFromClassName('AdminStats');
    $tab8->position = Tab::getNewLastPosition($tab8->id_parent);
    foreach (Language::getLanguages(false) as $lang) {
        if ($lang['iso_code'] == "fr") {
            $tab8->name[(int)$lang['id_lang']] = 'Frais et commissions';
        } else {
            $tab8->name[(int)$lang['id_lang']] = 'Fees and Commissions';
        }
    }
    $res &= $tab8->add();

    $mysqlVersion = Db::getInstance()->getVersion();
    $dateTimOrTimeStamp = ($mysqlVersion < '5.6.5') ? 'TIMESTAMP' : 'datetime';

    $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'opartstat_commissions` (
        `commissionId` int(10) NOT NULL AUTO_INCREMENT,
        `startDate` ' . $dateTimOrTimeStamp . ' NOT NULL,
        `endDate` ' . $dateTimOrTimeStamp . ',
        `fixedFees` decimal(20,6),        
        `variableFees` decimal(20,6),
        `paymentMethod` varchar(255),
        PRIMARY KEY (`commissionId`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';

    $res &= Db::getInstance()->execute($sql);

    return $res;
}