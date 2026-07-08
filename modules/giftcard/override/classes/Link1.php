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
class Link extends LinkCore
{
    /**
     * @author
     */
    public function getProductLink(
        $product,
        $alias = null,
        $category = null,
        $ean13 = null,
        $idLang = null,
        $idShop = null,
        $idProductAttribute = null,
        $force_routes = false,
        $relativeProtocol = false,
        $withIdInAnchor = false,
        $extraParams = [],
        $addAnchor = true
    ) {
        $giftcard = Module::getInstanceByName('giftcard');
        if ($giftcard && $giftcard->active) {
            if (!is_object($product)) {
                if (is_array($product) && isset($product['id_product'])) {
                    $id_product = $product['id_product'];
                } elseif ((int) $product) {
                    $id_product = (int) $product;
                }
            } else {
                $id_product = $product->id;
            }
            if ((int) $id_product > 0 && $giftcard->isGiftCard($id_product)) {
                $params = [];
                $params['id_product'] = $id_product;

                return $this->getModuleLink('giftcard', 'choicegiftcard', $params);
            }
        }
        if (version_compare(_PS_VERSION_, '1.6.0.10', '<') === true) {
            return parent::getProductLink(
                $product,
                $alias,
                $category,
                $ean13,
                $idLang,
                $idShop,
                $idProductAttribute,
                $force_routes
            );
        }
        if (version_compare(_PS_VERSION_, '1.6.1.1', '<') === true) {
            return parent::getProductLink(
                $product,
                $alias,
                $category,
                $ean13,
                $idLang,
                $idShop,
                $idProductAttribute,
                $force_routes,
                $relativeProtocol
            );
        }
        if (version_compare(_PS_VERSION_, '1.7.0.0', '<') === true) {
            return parent::getProductLink(
                $product,
                $alias,
                $category,
                $ean13,
                $idLang,
                $idShop,
                $idProductAttribute,
                $force_routes,
                $relativeProtocol,
                $withIdInAnchor
            );
        }
        if (version_compare(_PS_VERSION_, '1.7.8.0', '<') === true) {
            return parent::getProductLink(
                $product,
                $alias,
                $category,
                $ean13,
                $idLang,
                $idShop,
                $idProductAttribute,
                $force_routes,
                $relativeProtocol,
                $withIdInAnchor,
                $extraParams
            );
        }

        return parent::getProductLink(
            $product,
            $alias,
            $category,
            $ean13,
            $idLang,
            $idShop,
            $idProductAttribute,
            $force_routes,
            $relativeProtocol,
            $withIdInAnchor,
            $extraParams,
            $addAnchor
        );
    }
}
