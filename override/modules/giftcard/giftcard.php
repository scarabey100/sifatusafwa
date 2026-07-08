<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
} 
 
class GiftcardOverride extends Giftcard
{
    public function enable($force_all = false)
    {
        if (!$this->installModuleTab()) {
            return false;
        }
        $result = true;

        $result &= parent::enable($force_all);

        return (bool) $result;
    }
    
    /**
     * Désactive les onglets du module Giftcard
     */
    public function disable($force_all = false)
    {
        if (!$this->uninstallModuleTab()) {
            return false;
        }
        $result = true;

        $result &= parent::disable($force_all);

        return (bool) $result;
    }
    /* Installation de l'onglet */
    private function installModuleTab()
    {
        $admin_tab_catalog_id = Tab::getIdFromClassName('AdminCatalog');
        $admin_tab_order_id = Tab::getIdFromClassName('AdminParentOrders');
        /* Gift Card Template */
        $tabgiftcardtemplate = new Tab();
        $languages = Language::getLanguages(false);
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $tabgiftcardtemplate->name[(int) $language['id_lang']] = 'Cartes Cadeaux Modèles';
            } else {
                $tabgiftcardtemplate->name[(int) $language['id_lang']] = 'Templates Gift Cards';
            }
        }
        $tabgiftcardtemplate->class_name = 'AdminGiftCardTemplate';
        $tabgiftcardtemplate->module = 'giftcard';
        $tabgiftcardtemplate->id_parent = $admin_tab_catalog_id;
        if (!$tabgiftcardtemplate->save()) {
            return false;
        }
        $tabgiftcardproduct = new Tab();
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $tabgiftcardproduct->name[(int) $language['id_lang']] = 'Cartes Cadeaux';
            } else {
                $tabgiftcardproduct->name[(int) $language['id_lang']] = 'Gift Cards';
            }
        }
        $tabgiftcardproduct->class_name = 'AdminGiftCard';
        $tabgiftcardproduct->module = 'giftcard';
        $tabgiftcardproduct->id_parent = $admin_tab_catalog_id;
        if (!$tabgiftcardproduct->save()) {
            return false;
        }
        $tabgiftcardorder = new Tab();
        foreach ($languages as $language) {
            if ($language['iso_code'] == 'fr') {
                $tabgiftcardorder->name[(int) $language['id_lang']] = 'Cartes Cadeaux';
            } else {
                $tabgiftcardorder->name[(int) $language['id_lang']] = 'Gift Cards';
            }
        }
        $tabgiftcardorder->class_name = 'AdminGiftCardOrder';
        $tabgiftcardorder->module = 'giftcard';
        $tabgiftcardorder->id_parent = $admin_tab_order_id;
        if (!$tabgiftcardorder->save()) {
            return false;
        }

        return true;
    }

    private function uninstallModuleTab()
    {
        $id_tab_product = Tab::getIdFromClassName('AdminGiftCard');
        $id_tab_order = Tab::getIdFromClassName('AdminGiftCardOrder');
        $id_tab_template = Tab::getIdFromClassName('AdminGiftCardTemplate');
        if ($id_tab_template != 0) {
            $tab_template = new Tab($id_tab_template);
            $tab_template->delete();
        }
        if ($id_tab_product != 0) {
            $tab_product = new Tab($id_tab_product);
            $tab_product->delete();
        }
        if ($id_tab_order != 0) {
            $tab_order = new Tab($id_tab_order);
            $tab_order->delete();

            return true;
        }

        return false;
    }
}