<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_6_7($module)
{
    $db = Db::getInstance();

    try {
        $fixCpIndex = !$db->getValue('SELECT 1 FROM ' . _DB_PREFIX_ . 'creativepopup_revisions WHERE `id` = -1');
    } catch (Exception $ex) {
        $fixCpIndex = false;
    }

    if ($fixCpIndex) {
        $popups = $db->executeS('SELECT `id` FROM ' . _DB_PREFIX_ . 'creativepopup WHERE `flag_hidden` = 0 AND `flag_deleted` = 0 ORDER BY `id` DESC');

        if ($popups) {
            require_once _PS_MODULE_DIR_ . 'creativepopup/helper.php';
            require_once _PS_MODULE_DIR_ . 'creativepopup/base/core.php';

            $ids = [];
            foreach ($popups as &$popup) {
                $ids[] = $popup['id'];
            }

            CpPopups::init();
            CpPopups::addIndex($ids);
        }

        Configuration::deleteByName('CP_POPUP_INDEX');
    }

    return true;
}
