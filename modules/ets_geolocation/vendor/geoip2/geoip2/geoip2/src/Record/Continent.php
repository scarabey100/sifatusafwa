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
/**
 * Contains data for the continent record associated with an IP address.
 *
 * This record is returned by all location services and databases.
 *
 * @property string|null $code A two character continent code like "NA" (North
 *                             America) or "OC" (Oceania). This attribute is returned by all location
 *                             services and databases.
 * @property int|null $geonameId The GeoName ID for the continent. This
 *                               attribute is returned by all location services and databases.
 * @property string|null $name Returns the name of the continent based on the
 *                             locales list passed to the constructor. This attribute is returned by all location
 *                             services and databases.
 * @property array|null $names An array map where the keys are locale codes
 *                             and the values are names. This attribute is returned by all location
 *                             services and databases.
 */
class Continent extends AbstractPlaceRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = [
        'code',
        'geonameId',
        'names',
    ];
}
