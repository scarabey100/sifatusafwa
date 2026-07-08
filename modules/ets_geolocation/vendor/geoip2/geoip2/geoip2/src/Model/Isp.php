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
 * This class provides the GeoIP2 ISP model.
 *
 * @property int|null $autonomousSystemNumber The autonomous system number
 *                                            associated with the IP address.
 * @property string|null $autonomousSystemOrganization The organization
 *                                                     associated with the registered autonomous system number for the IP
 *                                                     address.
 * @property string|null $isp The name of the ISP associated with the IP
 *                            address.
 * @property string|null $organization The name of the organization associated
 *                                     with the IP address.
 * @property string $ipAddress The IP address that the data in the model is
 *                             for.
 * @property string $network The network in CIDR notation associated with
 *                           the record. In particular, this is the largest network where all of the
 *                           fields besides $ipAddress have the same value.
 */
class Isp extends AbstractModel
{
    protected $autonomousSystemNumber;
    protected $autonomousSystemOrganization;
    protected $isp;
    protected $organization;
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
        $this->autonomousSystemNumber = $this->get('autonomous_system_number');
        $this->autonomousSystemOrganization =
            $this->get('autonomous_system_organization');
        $this->isp = $this->get('isp');
        $this->organization = $this->get('organization');

        $ipAddress = $this->get('ip_address');
        $this->ipAddress = $ipAddress;
        $this->network = Util::cidr($ipAddress, $this->get('prefix_len'));
    }
}
