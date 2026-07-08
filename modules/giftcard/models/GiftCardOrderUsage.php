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
class GiftCardOrderUsage extends ObjectModel
{
    public $id_cart_rule;
    public $id_order;
    public $value_tax_excl;
    public $value_tax_incl;

    public static $definition = [
        'table' => 'giftcard_order_usage',
        'primary' => 'id_order_usage',
        'fields' => [
            'id_cart_rule' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_order' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'value_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
            'value_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
        ],
    ];
}
