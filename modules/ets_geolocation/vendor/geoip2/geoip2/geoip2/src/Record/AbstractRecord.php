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

namespace GeoIp2\Record;

if (!defined('_PS_VERSION_')) {
    exit;
}
abstract class AbstractRecord implements \JsonSerializable
{
    private $record;

    /**
     * @ignore
     *
     * @param mixed $record
     */
    public function __construct($record)
    {
        $this->record = isset($record) ? $record : [];
    }

    /**
     * @ignore
     *
     * @param mixed $attr
     */
    public function __get($attr)
    {
        // XXX - kind of ugly but greatly reduces boilerplate code
        $key = $this->attributeToKey($attr);

        if ($this->__isset($attr)) {
            return $this->record[$key];
        } elseif ($this->validAttribute($attr)) {
            if (preg_match('/^is_/', $key)) {
                return false;
            }

            return null;
        }
        throw new \RuntimeException("Unknown attribute: $attr");
    }

    public function __isset($attr)
    {
        return $this->validAttribute($attr) &&
             isset($this->record[$this->attributeToKey($attr)]);
    }

    private function attributeToKey($attr)
    {
        return strtolower(preg_replace('/([A-Z])/', '_\1', $attr));
    }

    private function validAttribute($attr)
    {
        return \in_array($attr, $this->validAttributes, true);
    }
}
