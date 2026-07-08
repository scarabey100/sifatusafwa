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
class Ps_EmailAlertsOverride extends Ps_EmailAlerts
{
    public function __construct()
    {
        parent::__construct();
        if (trim(Tools::getValue('process')) === 'add' && trim(Tools::getValue('module')) === $this->name && trim(Tools::getValue('controller')) === 'actions') {
            if (Module::isEnabled('ets_advancedcaptcha')) {
                
                $captcha = Module::getInstanceByName('ets_advancedcaptcha');
                if ($captcha->hookVal('product', 'out_of_stock')) {
                    $captcha->captchaVal($this->_errors);
                    if (is_array($this->_errors) && count($this->_errors)) {
                        $errs = implode(',', $this->_errors);
                        exit(json_encode(
                            [
                                'error' => true,
                                'message' => $this->trans($errs, [], 'Modules.Emailalerts.Shop'),
                            ]
                        ));
                    }
                }
            }
        }
    }
}
