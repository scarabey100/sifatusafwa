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
use GeoIp2\Util;

/**
 * This class provides the GeoIP2 Domain model.
 *
 * @property string|null $domain The second level domain associated with the
 *                               IP address. This will be something like "example.com" or
 *                               "example.co.uk", not "foo.example.com".
 * @property string $ipAddress The IP address that the data in the model is
 *                             for.
 * @property string $network The network in CIDR notation associated with
 *                           the record. In particular, this is the largest network where all of the
 *                           fields besides $ipAddress have the same value.
 */
class Domain extends AbstractModel
{
    protected $domain;
    protected $ipAddress;
    protected $network;

    /**
     * @ignore
     *
     * @param mixed $raw
     */
    public function __construct($raw)
    {
        parent::__construct($raw);

        $this->domain = $this->get('domain');
        $ipAddress = $this->get('ip_address');
        $this->ipAddress = $ipAddress;
        $this->network = Util::cidr($ipAddress, $this->get('prefix_len'));
    }
}
