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
class Product extends ProductCore
{
    /**
     * @author
     */
    public static function getPriceStatic(
        $id_product,
        $usetax = true,
        $id_product_attribute = null,
        $decimals = 6,
        $divisor = null,
        $only_reduc = false,
        $usereduc = true,
        $quantity = 1,
        $force_associated_tax = false,
        $id_customer = null,
        $id_cart = null,
        $id_address = null,
        &$specific_price_output = null,
        $with_ecotax = true,
        $use_group_reduction = true,
        Context $context = null,
        $use_customer_price = true,
        $id_customization = null
    ) {
        $giftcard = Module::getInstanceByName('giftcard');
        if ($giftcard && $giftcard->active && (int) $id_product > 0 && $giftcard->isGiftCard($id_product)) {
            return (float) GiftCardProduct::getAmount($id_product);
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<') === true) {
            return parent::getPriceStatic(
                $id_product,
                $usetax,
                $id_product_attribute,
                $decimals,
                $divisor,
                $only_reduc,
                $usereduc,
                $quantity,
                $force_associated_tax,
                $id_customer,
                $id_cart,
                $id_address,
                $specific_price_output,
                $with_ecotax,
                $use_group_reduction,
                $context,
                $use_customer_price
            );
        }

        return parent::getPriceStatic(
            $id_product,
            $usetax,
            $id_product_attribute,
            $decimals,
            $divisor,
            $only_reduc,
            $usereduc,
            $quantity,
            $force_associated_tax,
            $id_customer,
            $id_cart,
            $id_address,
            $specific_price_output,
            $with_ecotax,
            $use_group_reduction,
            $context,
            $use_customer_price,
            $id_customization
        );
    }
}
