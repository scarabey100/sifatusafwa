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

namespace GeoIp2\Model;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * @ignore
 */
abstract class AbstractModel implements \JsonSerializable
{
    protected $raw;

    /**
     * @ignore
     *
     * @param mixed $raw
     */
    public function __construct($raw)
    {
        $this->raw = $raw;
    }

    /**
     * @ignore
     *
     * @param mixed $field
     */
    protected function get($field)
    {
        if (isset($this->raw[$field])) {
            return $this->raw[$field];
        }
        if (preg_match('/^is_/', $field)) {
            return false;
        }

        return null;
    }

    /**
     * @ignore
     *
     * @param mixed $attr
     */
    public function __get($attr)
    {
        if ($attr !== 'instance' && property_exists($this, $attr)) {
            return $this->$attr;
        }

        throw new \RuntimeException("Unknown attribute: $attr");
    }

    /**
     * @ignore
     *
     * @param mixed $attr
     */
    public function __isset($attr)
    {
        return $attr !== 'instance' && isset($this->$attr);
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }
}
