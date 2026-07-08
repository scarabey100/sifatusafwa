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

function upgrade_module_1_0_5($object, $install = false)
{
    $cols = [
        'cr_free_shipping' => [
            'exist' => false,
            'sql' => 'ALTER TABLE `' .
                _DB_PREFIX_ . 'giftcardproduct` ADD `cr_free_shipping` tinyint(1) NOT NULL DEFAULT 0 ',
        ],
        'cr_partial_use' => [
            'exist' => false,
            'sql' => 'ALTER TABLE `' .
                _DB_PREFIX_ . 'giftcardproduct` ADD `cr_partial_use` tinyint(1) NOT NULL DEFAULT 1 ',
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
            'cr_free_shipping',
            'cr_partial_use',
        ])) {
            if (!$co['exist']) {
                Db::getInstance()->execute($co['sql']);
            }
        }
    }

    return true;
}
