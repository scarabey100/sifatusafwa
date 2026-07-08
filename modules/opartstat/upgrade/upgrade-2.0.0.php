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

function upgrade_module_2_0_0($module)
{
    $res = true;
    
    $tab3 = new Tab();
        $tab3->module = 'opartstat';
        $tab3->active = 0;
        $tab3->class_name = 'AdminOpartStatSettingsGlobal';
        $tab3->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab3->position = Tab::getNewLastPosition($tab3->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab3->name[(int)$lang['id_lang']] = 'OpartStat réglage généraux';
            } else {
                $tab3->name[(int)$lang['id_lang']] = 'OpartStat global settings';
            }
        }
        $res = $tab3->add();

        //add globalSetting tab (but hide it)
        $tab4 = new Tab();
        $tab4->module = 'opartstat';
        $tab4->active = 0;
        $tab4->class_name = 'AdminOpartStatSettingsRobots';
        $tab4->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab4->position = Tab::getNewLastPosition($tab4->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab4->name[(int)$lang['id_lang']] = 'OpartStat réglage des robots';
            } else {
                $tab4->name[(int)$lang['id_lang']] = 'OpartStat bots settings';
            }
        }
        $tab4->add();

        //add globalSetting tab (but hide it)
        $tab5 = new Tab();
        $tab5->module = 'opartstat';
        $tab5->active = 0;
        $tab5->class_name = 'AdminOpartStatSettingsModules';
        $tab5->id_parent = (int)Tab::getIdFromClassName('AdminStats');
        $tab5->position = Tab::getNewLastPosition($tab5->id_parent);
        foreach (Language::getLanguages(false) as $lang) {
            if ($lang['iso_code'] == "fr") {
                $tab5->name[(int)$lang['id_lang']] = 'OpartStat réglage des modules partenaires';
            } else {
                $tab5->name[(int)$lang['id_lang']] = 'OpartStat partners modules settings';
            }
        }
        $res = $tab5->add();

    return $res;
}