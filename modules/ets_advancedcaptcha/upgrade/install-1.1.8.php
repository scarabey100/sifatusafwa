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


function upgrade_module_1_1_8($object)
{
    if (!Configuration::updateValue('PA_GOOGLE_CAPTCHA_THEME', 'light', true) ||
        !Configuration::updateValue('PA_GOOGLE_V3_POSITION', 'bottomright', true)) {
        return false;
    }
    if ($object->getOverrides() != null) {
        $object->uninstallOverrides();
        try {
            $object->installOverrides();
        } catch (Exception $e) {
            $object->_errors[] = $e->getMessage();
        }
        if ($object->_errors)
            return false;
    }

    return true;
}