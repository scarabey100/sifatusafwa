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

namespace GeoIp2;

if (!defined('_PS_VERSION_')) {
    exit;
}
class Util
{
    /**
     * This returns the network in CIDR notation for the given IP and prefix
     * length. This is for internal use only.
     *
     * @internal
     *
     * @ignore
     *
     * @param mixed $ipAddress
     * @param mixed $prefixLen
     */
    public static function cidr($ipAddress, $prefixLen)
    {
        $ipBytes = inet_pton($ipAddress);
        $networkBytes = str_repeat("\0", \strlen($ipBytes));

        $curPrefix = $prefixLen;
        for ($i = 0; $i < \strlen($ipBytes) && $curPrefix > 0; ++$i) {
            $b = $ipBytes[$i];
            if ($curPrefix < 8) {
                $shiftN = 8 - $curPrefix;
                $b = \chr(0xFF & (\ord($b) >> $shiftN) << $shiftN);
            }
            $networkBytes[$i] = $b;
            $curPrefix -= 8;
        }

        $network = inet_ntop($networkBytes);

        return "$network/$prefixLen";
    }
}
