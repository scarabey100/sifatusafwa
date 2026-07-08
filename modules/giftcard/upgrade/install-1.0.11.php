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

function upgrade_module_1_0_11($object, $install = false)
{
    if ($object->active || $install) {
        $result = true;
        $cols = [
            'var_lastname_default' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' .
                    _DB_PREFIX_ . 'giftcardtemplate`  ADD COLUMN `var_lastname_default` VARCHAR(155) NULL DEFAULT NULL ',
            ],
            'var_from_default' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' .
                    _DB_PREFIX_ . 'giftcardtemplate` ADD COLUMN `var_from_default` VARCHAR(155) NULL DEFAULT NULL ',
            ],
            'var_message_default' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' .
                    _DB_PREFIX_ . 'giftcardtemplate` ADD COLUMN `var_message_default` TEXT NULL ',
            ],
            'var_expirate_default' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' .
                    _DB_PREFIX_ . 'giftcardtemplate`  ADD COLUMN `var_expirate_default` VARCHAR(155) NULL DEFAULT NULL ',
            ],
            'pdf_image_only' => [
                'exist' => false,
                'sql' => 'ALTER TABLE `' .
                    _DB_PREFIX_ . 'giftcardtemplate`  ADD COLUMN `pdf_image_only` tinyint(1) NOT NULL DEFAULT \'0\' ',
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
                'var_lastname_default',
                'var_from_default',
                'var_message_default',
                'var_expirate_default',
                'pdf_image_only',
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
