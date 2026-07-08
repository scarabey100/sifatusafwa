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
 * Contains data for the postal record associated with an IP address.
 *
 * This record is returned by all location databases and services besides
 * Country.
 *
 * @property string|null $code The postal code of the location. Postal codes
 *                             are not available for all countries. In some countries, this will only
 *                             contain part of the postal code. This attribute is returned by all location
 *                             databases and services besides Country.
 * @property int|null $confidence A value from 0-100 indicating MaxMind's
 *                                confidence that the postal code is correct. This attribute is only
 *                                available from the Insights service and the GeoIP2 Enterprise
 *                                database.
 */
class Postal extends AbstractRecord
{
    /**
     * @ignore
     */
    protected $validAttributes = ['code', 'confidence'];
}
