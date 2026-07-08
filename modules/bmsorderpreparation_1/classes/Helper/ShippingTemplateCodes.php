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

class ShippingTemplatesCodes
{

    public static function hydrateOrder(&$order)
    {
        $order->date_yyyymmdd = date('Ymd', strtotime($order->date_add));

        //if socolissimo module is installed, append specific tables
        if (CompatibilityOrderPreparation::socolissimoModuleIsInstalled())
        {
            $socolissimoFields = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'socolissimo_delivery_info` WHERE `id_cart` = ' . pSQL($order->id_cart));

            foreach($socolissimoFields as $k => $v)
                $order->{'colissimo_'.$k} = $v;
        }

        $order = ShippingTemplatesCodes::cleanArrayFromJsonObject($order);

        return $order;
    }

    public static function hydrateOrderCarrier(&$orderCarrier)
    {
        $orderCarrier->weight_2_decimals = number_format($orderCarrier->weight, 2, ".", "");
        $orderCarrier->weight_integer = (int)$orderCarrier->weight;
        $orderCarrier->weight_grams = (int)($orderCarrier->weight * 1000);

        $orderCarrier = ShippingTemplatesCodes::cleanArrayFromJsonObject($orderCarrier);

        return $orderCarrier;
    }

    public static function hydrateAddress(&$address)
    {
        $country = new Country($address->id_country);
        $address->country_code = $country->iso_code;

        $address = ShippingTemplatesCodes::cleanArrayFromJsonObject($address);

        return $address;
    }

    public static function cleanArrayFromJsonObject($object)
    {
        $objectArray = json_decode(json_encode($object), true);
        foreach($objectArray as $index => $element) {
            if(!is_string($element))
                unset($objectArray[$index]);
        }
        $object = json_decode(json_encode($objectArray));

        return $object;
    }
}