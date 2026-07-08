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
class CustomerAddressForm extends CustomerAddressFormCore
{
    /*
    * module: ets_geolocation
    * date: 2025-07-24 17:54:22
    * version: 1.3.5
    */
    public $address;
    /*
    * module: ets_geolocation
    * date: 2025-07-24 17:54:22
    * version: 1.3.5
    */
    public function fillWith(array $params = [])
    {
        if(!isset($params['id_country']) && !$this->address)
        {
            if(Module::isEnabled('ets_geolocation'))
            {
                $ets_geolocation = Module::getInstanceByName('ets_geolocation');
                if(!$ets_geolocation->getConfigService()->isManualSelectCountry())
                {
                    if(($country = $ets_geolocation->detectUserCountry()) && $country->active)
                    {
                        $params['id_country'] = $country->id;
                    }
                }
                elseif(Context::getContext()->country->id)
                    $params['id_country'] = Context::getContext()->country->id;
            }
        }
        return parent::fillWith($params);
    }
}