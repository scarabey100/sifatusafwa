<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2024 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_8_13($module)
{
    if (Configuration::get('REVOLUT_SIGNUP_BANNER_ENABLE') == null) {
        Configuration::updateValue('REVOLUT_SIGNUP_BANNER_ENABLE', $module->default_revolut_reward_banner);
    }

    if (Configuration::get('REVOLUT_BENEFITS_BANNER_ENABLE') == null) {
        Configuration::updateValue('REVOLUT_BENEFITS_BANNER_ENABLE', $module->default_revolut_benifits_banner);
    }

    if (Configuration::get('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT') == null) {
        Configuration::updateValue('REVOLUT_PAY_INFORMATIONAL_ICON_VARIANT', $module->default_revolut_informational_icon);
    }

    return $module->registerHook('displayPaymentTop');
}
