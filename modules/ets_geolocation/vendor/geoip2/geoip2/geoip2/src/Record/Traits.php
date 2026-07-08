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
use GeoIp2\Util;

class Traits extends AbstractRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = [
        'autonomousSystemNumber',
        'autonomousSystemOrganization',
        'connectionType',
        'domain',
        'ipAddress',
        'isAnonymous',
        'isAnonymousProxy',
        'isAnonymousVpn',
        'isHostingProvider',
        'isLegitimateProxy',
        'isp',
        'isPublicProxy',
        'isSatelliteProvider',
        'isTorExitNode',
        'network',
        'organization',
        'staticIpScore',
        'userCount',
        'userType',
    ];

    public function __construct($record)
    {
        if (!isset($record['network']) && isset($record['ip_address'], $record['prefix_len'])) {
            $record['network'] = Util::cidr($record['ip_address'], $record['prefix_len']);
        }

        parent::__construct($record);
    }
}
