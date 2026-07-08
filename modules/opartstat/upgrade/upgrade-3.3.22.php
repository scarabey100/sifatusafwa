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

function upgrade_module_3_3_22($module)
{
    //add tab but hide it
    $tab = new Tab();
    $tab->module = $module->name;
    $tab->active = 0;
    $tab->class_name = 'AdminOpartStatSettingsAdvanced';
    $tab->id_parent = (int)Tab::getIdFromClassName('AdminStats');
    $tab->position = Tab::getNewLastPosition($tab->id_parent);
    foreach (Language::getLanguages(false) as $lang) {
        if ($lang['iso_code'] == "fr") {
            $tab->name[(int)$lang['id_lang']] = 'OpartStat réglages avancés';
        } else {
            $tab->name[(int)$lang['id_lang']] = 'OpartStat advanced settings';
        }
    }
    $res = $tab->add();


    $res &= Configuration::updateValue('OPARTSTAT_ACTIVE_DEBUG_MODE', '0');
    return $res;
}
