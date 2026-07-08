<?php
/**
 * 2007-2022 Boostmyshop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 Boostmyshop
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class CompatibilityOrderPreparation
{

    public static function getProductLink($productId, $context)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>='))
            return $context->link->getAdminLink('AdminProducts', true, array('id_product' => (int)$productId));
        else
            return $context->link->getAdminLink('AdminProducts', true).'&id_product='.$productId.'&updateproduct';
    }


    public static function getOrderLink($orderId, $context)
    {
        /*if (version_compare(_PS_VERSION_, '1.7', '>='))
            return $context->link->getAdminLink('AdminOrders', true, array('id_order' => (int)$orderId));
        else*/
        return $context->link->getAdminLink('AdminOrders', true).'&id_order='.$orderId.'&vieworder';
    }

    public static function getPrestashopUrl($controller, $params, $context)
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $url = $context->link->getAdminLink($controller, true, array(), $params);
        }
        else
        {
            $querystring = array();
            foreach($params as $k => $v) {
                $querystring[] = $k.'='.$v;
            }
            $url = $context->link->getAdminLink($controller, true).'&'.implode('&', $querystring);
        }

        return $url;
    }

    public static function advancedStockModuleIsInstalled()
    {
        return (Module::isInstalled('advancedstock'));
    }

    public static function socolissimoModuleIsInstalled()
    {
        return (Module::isInstalled('soflexibilite'));
    }

}
