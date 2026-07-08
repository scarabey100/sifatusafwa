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

function upgrade_module_1_0_12($object, $install = false)
{
    if ($object->active || $install) {
        $result = true;
        $cols = [
            'id_cart' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' .
                    _DB_PREFIX_ . 'giftcardorder` ADD COLUMN `id_cart` INT(10) NOT NULL DEFAULT 0 AFTER `id_order` ',
            ],
        ];
        $columns = Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'giftcardorder` ');
        foreach ($columns as $c) {
            if (in_array($c['Field'], array_keys($cols))) {
                $cols[$c['Field']]['exist'] = true;
            }
        }

        foreach ($cols as $name => $co) {
            if (in_array($name, [
                'id_cart',
            ])) {
                if (!$co['exist']) {
                    Db::getInstance()->execute($co['sql']);
                }
            } elseif ($co['exist']) {
                Db::getInstance()->execute($co['sql']);
            }
        }
    }

    return $result;
}
