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
/*
 * Class Link
 *
 * @mixin \AddressCore
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class Address extends AddressCore
{
    /*
    * module: ets_geolocation
    * date: 2025-07-24 17:54:22
    * version: 1.3.5
    */
    public function validateField($field, $value, $id_lang = null, $skip = [], $human_errors = false)
    {
        if ('dni' === $field && (int) $this->id_country && $this->alias === 'auto' && $this->firstname === 'auto' && $this->lastname === 'auto' && static::dniRequired((int) $this->id_country) && Tools::isEmpty($this->dni)) {
            $this->dni = 'auto';
            $this->update();
            return true;
        }
        return parent::validateField($field, $value, $id_lang, $skip, $human_errors);
    }
}
