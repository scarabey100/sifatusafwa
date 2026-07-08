<?php
/**
 * GIFT CARD
 *
 * @author    EIRL Timactive De Véra
 * @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 * @license   Commercial license
 *
 * @category pricing_promotion
 *
 * @version 1.1.0
 *************************************
 **         GIFT CARD                *
 **          V 1.0.0                 *
 *************************************
 * +
 * + Languages: EN, FR, ES
 * + PS version: 1.5,1.6,1.7
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_0($object, $install = false)
{
    if ($object->active || $install) {
        $result = true;
        Configuration::updateValue('GIFTCARD_THEME_CGC', 'classic');
        $cols = [
            'virtualcard' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' . _DB_PREFIX_ .
                    'giftcardproduct`  ADD COLUMN `virtualcard` TINYINT(1) NOT NULL DEFAULT \'1\' AFTER `cr_partial_use` ',
            ],
        ];
        $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'giftcardproduct` ');
        foreach ($columns as $c) {
            if (in_array($c['Field'], array_keys($cols))) {
                $cols[$c['Field']]['exist'] = true;
            }
        }
        foreach ($cols as $name => $co) {
            if (in_array($name, [
                'virtualcard',
            ])) {
                if (!$co['exist']) {
                    Db::getInstance()->execute($co['sql']);
                }
            }
        }

        $cols = [
            'virtualuse' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' . _DB_PREFIX_ .
                    'giftcardtemplate` ADD COLUMN `virtualuse` TINYINT(1) NOT NULL DEFAULT 1 AFTER `pdf_image_only` ',
            ],
            'physicaluse' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' . _DB_PREFIX_ .
                    'giftcardtemplate` ADD COLUMN `physicaluse` TINYINT(1) NOT NULL DEFAULT 1 AFTER `active` ',
            ],
        ];
        $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'giftcardtemplate` ');
        foreach ($columns as $c) {
            if (in_array($c['Field'], array_keys($cols))) {
                $cols[$c['Field']]['exist'] = true;
            }
        }
        foreach ($cols as $name => $co) {
            if (in_array($name, [
                'virtualuse',
                'physicaluse',
            ])) {
                if (!$co['exist']) {
                    Db::getInstance()->execute($co['sql']);
                }
            }
        }
        $admin_tab_parent_order_id = Tab::getIdFromClassName('AdminParentOrders');
        if ((int) $admin_tab_parent_order_id) {
            $id_tab_order = Tab::getIdFromClassName('AdminGiftCardOrder');
            if ((int) $id_tab_order != 0) {
                $tab_order = new Tab($id_tab_order);
                $tab_order->id_parent = $admin_tab_parent_order_id;
                $tab_order->update();

                return true;
            }
        }
    }

    return $result;
}
