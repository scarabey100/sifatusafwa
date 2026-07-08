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
 * City-level data associated with an IP address.
 *
 * This record is returned by all location services and databases besides
 * Country.
 *
 * @property int|null $confidence A value from 0-100 indicating MaxMind's
 *                                confidence that the city is correct. This attribute is only available
 *                                from the Insights service and the GeoIP2 Enterprise database.
 * @property int|null $geonameId The GeoName ID for the city. This attribute
 *                               is returned by all location services and databases.
 * @property string|null $name The name of the city based on the locales list
 *                             passed to the constructor. This attribute is returned by all location
 *                             services and databases.
 * @property array|null $names A array map where the keys are locale codes
 *                             and the values are names. This attribute is returned by all location
 *                             services and databases.
 */
class City extends AbstractPlaceRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = ['confidence', 'geonameId', 'names'];
}
