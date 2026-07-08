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

function upgrade_module_1_0_7($object, $install = false)
{
    if ($object->active || $install) {
        $result = true;
        Db::getInstance()->Execute('
		ALTER TABLE `' . _DB_PREFIX_ . 'giftcardorder` 
				CHANGE COLUMN `id_gift_card_template` `id_gift_card_template` INT(10) NULL DEFAULT NULL');
    }

    return $result;
}
