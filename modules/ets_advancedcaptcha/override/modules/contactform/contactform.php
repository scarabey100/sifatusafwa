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

/**
 * Class ContactformOverride
 * @package ETS Advanced Captcha
 * @since 1.3.6
 */
class ContactformOverride extends Contactform
{
    public function sendMessage()
    {
        if(Module::isEnabled('ets_advancedcaptcha') && $instance = Module::getInstanceByName('ets_advancedcaptcha')){
            /** @var \Ets_advancedcaptcha $instance */
            return $instance->sendMessage();
        }
        return parent::sendMessage();
    }
}