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

function upgrade_module_1_0_6($object, $install = false)
{
    if ($object->active || $install) {
        $result = true;
        if (!Db::getInstance()->Execute('
		CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate_shop` (
  			`id_gift_card_template` int(11) unsigned NOT NULL,
  			`id_shop` int(11) unsigned NOT NULL,
  		PRIMARY KEY (`id_gift_card_template`,`id_shop`),
  		KEY `id_shop` (`id_shop`)
		) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;')) {
            return false;
        }
        $insertshop = [];
        $template_count = (int) Db::getInstance()->getValue('SELECT COUNT(*) FROM `' .
            _DB_PREFIX_ . 'giftcardtemplate_shop`');
        if ($template_count == 0) {
            $result_templates = Db::getInstance()->executeS('SELECT id_gift_card_template FROM ' .
                _DB_PREFIX_ . 'giftcardtemplate');
            foreach ($result_templates as $rowgct) {
                foreach (Db::getInstance()->executeS('SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop') as $rowshop) {
                    $insertshop[] = [
                        'id_gift_card_template' => (int) $rowgct['id_gift_card_template'],
                        'id_shop' => (int) $rowshop['id_shop'],
                    ];
                }
            }
            Db::getInstance()->insert('giftcardtemplate_shop', $insertshop, false, true, Db::INSERT_IGNORE);
        }
    }

    return true;
}
