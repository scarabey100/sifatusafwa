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
 * Contains data for the subdivisions associated with an IP address.
 *
 * This record is returned by all location databases and services besides
 * Country.
 *
 * @property int|null $confidence This is a value from 0-100 indicating
 *                                MaxMind's confidence that the subdivision is correct. This attribute is
 *                                only available from the Insights service and the GeoIP2 Enterprise
 *                                database.
 * @property int|null $geonameId This is a GeoName ID for the subdivision.
 *                               This attribute is returned by all location databases and services besides
 *                               Country.
 * @property string|null $isoCode This is a string up to three characters long
 *                                contain the subdivision portion of the ISO 3166-2 code. See
 *                                https://en.wikipedia.org/wiki/ISO_3166-2. This attribute is returned by all
 *                                location databases and services except Country.
 * @property string|null $name The name of the subdivision based on the
 *                             locales list passed to the constructor. This attribute is returned by all
 *                             location databases and services besides Country.
 * @property array|null $names An array map where the keys are locale codes
 *                             and the values are names. This attribute is returned by all location
 *                             databases and services besides Country.
 */
class Subdivision extends AbstractPlaceRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = [
        'confidence',
        'geonameId',
        'isoCode',
        'names',
    ];
}
