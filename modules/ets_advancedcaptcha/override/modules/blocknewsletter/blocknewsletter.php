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

class BlocknewsletterOverride extends Blocknewsletter
{
    protected function newsletterRegistration()
    {
        $controller = trim(Tools::getValue('controller'));
        if (Module::isEnabled('ets_advancedcaptcha') && ($captcha = Module::getInstanceByName('ets_advancedcaptcha')) && Validate::isControllerName($controller) && $captcha->hookVal($controller, 'newsletter')) {
            $captcha->captchaVal($this->_errors);
            if (is_array($this->_errors)) {
                $errs = implode(',', $this->_errors);
                if (isset($this->error)) {
                    return $this->error = $errs;
                } else
                    $this->_errors = $errs;
            }
        }
        if (!$this->_errors)
            parent::newsletterRegistration();
    }
}