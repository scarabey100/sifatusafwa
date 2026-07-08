<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

class Ps_ContactinfoOverride extends Ps_Contactinfo
{

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $address = $this->context->shop->getAddress();

        $is_state_multilang = !empty(State::$definition['multilang']);
        $state_name = (new State($address->id_state))->name;

        $contact_infos = [
            'company' => Configuration::get('PS_SHOP_NAME'),
            'address' => [
                'formatted' => AddressFormat::generateAddress($address, [], ', '),
                'address1' => $address->address1,
                'address2' => $address->address2,
                'postcode' => $address->postcode,
                'city' => $address->city,
                'state' => (!empty($address->id_state) ? (new State($address->id_state))->name[$this->context->language->id] : null),
                'country' => (new Country($address->id_country))->name[$this->context->language->id],
            ],
            'phone' => Configuration::get('PS_SHOP_PHONE'),
            'fax' => Configuration::get('PS_SHOP_FAX'),
            'email' => Configuration::get('PS_SHOP_EMAIL'),
            'details' => Configuration::get('PS_SHOP_DETAILS'),
        ];

        return [
            'contact_infos' => $contact_infos,
            'display_email' => Configuration::get('PS_CONTACT_INFO_DISPLAY_EMAIL'),
        ];
    }
}
