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
class AuthController extends AuthControllerCore
{
    /*
    * module: ets_advancedcaptcha
    * date: 2025-07-22 19:17:35
    * version: 1.4.8
    */
    public function processSubmitAccount()
    {
        $controller = trim(Tools::getValue('controller'));
        if (version_compare(_PS_VERSION_, '1.7', '<') && Module::isEnabled('ets_advancedcaptcha') && ($captcha = Module::getInstanceByName('ets_advancedcaptcha')) && Validate::isControllerName($controller) && $captcha->hookVal($controller, 'register')) {
            $captcha->captchaVal($this->errors);
        }
        call_user_func(['parent', 'processSubmitAccount']);
    }
    /*
    * module: ets_advancedcaptcha
    * date: 2025-07-22 19:17:35
    * version: 1.4.8
    */
    protected function processSubmitLogin()
    {
        $controller = trim(Tools::getValue('controller'));
        if (version_compare(_PS_VERSION_, '1.7', '<') && Module::isEnabled('ets_advancedcaptcha') && ($captcha = Module::getInstanceByName('ets_advancedcaptcha')) && Validate::isControllerName($controller) && $captcha->hookVal($controller, 'login')) {
            $captcha->captchaVal($this->errors);
        }
        if (!$this->errors)
            call_user_func(['parent', 'processSubmitLogin']);
    }
}