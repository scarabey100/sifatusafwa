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
 * This class provides the GeoIP2 Connection-Type model.
 *
 * @property string|null $connectionType The connection type may take the
 *                                       following values: "Dialup", "Cable/DSL", "Corporate", "Cellular".
 *                                       Additional values may be added in the future.
 * @property string $ipAddress The IP address that the data in the model is
 *                             for.
 * @property string $network The network in CIDR notation associated with
 *                           the record. In particular, this is the largest network where all of the
 *                           fields besides $ipAddress have the same value.
 */
class ConnectionType extends AbstractModel
{
    protected $connectionType;
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

        $this->connectionType = $this->get('connection_type');
        $ipAddress = $this->get('ip_address');
        $this->ipAddress = $ipAddress;
        $this->network = Util::cidr($ipAddress, $this->get('prefix_len'));
    }
}
