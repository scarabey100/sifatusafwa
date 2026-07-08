<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }


function upgrade_module_1_0_9($object)
{
    $res =  $object->registerHooks() && $object->installConfig(true);

    /*updateValue position*/
    $positions = array();
    if (Configuration::get('PA_CAPTCHA_CONTACT'))
        $positions[] = 'contact';
    if (Configuration::get('PA_CAPTCHA_REGISTRATION'))
        $positions[] = 'register';
    $res &= Configuration::updateValue('PA_CAPTCHA_POSITION', ($positions? implode(',', $positions) : 'register,contact'), true);

    /*updateValue override template contact form*/
    if (($contact = Configuration::get('PA_CAPTCHA_OVERRIDES')))
        Configuration::updateValue('PA_CAPTCHA_TMP_CONTACT', $contact);

    /*update Override*/
    if ($object->getOverrides() != null)
    {
        $object->uninstallOverrides();
        try {
            $object->installOverrides();
        } catch (Exception $e) {
            $object->_errors[] = $e->getMessage();
        }
        if (!$object->_errors)
            $res &= true;
    }
    return $res;
}