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
 * Model class for the data returned by GeoIP2 City web service and database.
 *
 * The only difference between the City and Insights model classes is which
 * fields in each record may be populated. See
 * https://dev.maxmind.com/geoip/geoip2/web-services for more details.
 *
 * @property \GeoIp2\Record\City $city City data for the requested IP
 *                                     address.
 * @property \GeoIp2\Record\Location $location Location data for the
 *                                             requested IP address.
 * @property \GeoIp2\Record\Postal $postal Postal data for the
 *                                         requested IP address.
 * @property array $subdivisions An array \GeoIp2\Record\Subdivision
 *                               objects representing the country subdivisions for the requested IP
 *                               address. The number and type of subdivisions varies by country, but a
 *                               subdivision is typically a state, province, county, etc. Subdivisions
 *                               are ordered from most general (largest) to most specific (smallest).
 *                               If the response did not contain any subdivisions, this method returns
 *                               an empty array.
 * @property \GeoIp2\Record\Subdivision $mostSpecificSubdivision An object
 *                                                               representing the most specific subdivision returned. If the response
 *                                                               did not contain any subdivisions, this method returns an empty
 *                                                               \GeoIp2\Record\Subdivision object.
 */
class City extends Country
{
    /**
     * @ignore
     */
    protected $city;
    /**
     * @ignore
     */
    protected $location;
    /**
     * @ignore
     */
    protected $postal;
    /**
     * @ignore
     */
    protected $subdivisions = [];

    /**
     * @ignore
     *
     * @param mixed $raw
     * @param mixed $locales
     */
    public function __construct($raw, $locales = ['en'])
    {
        parent::__construct($raw, $locales);

        $this->city = new \GeoIp2\Record\City($this->get('city'), $locales);
        $this->location = new \GeoIp2\Record\Location($this->get('location'));
        $this->postal = new \GeoIp2\Record\Postal($this->get('postal'));

        $this->createSubdivisions($raw, $locales);
    }

    private function createSubdivisions($raw, $locales)
    {
        if (!isset($raw['subdivisions'])) {
            return;
        }

        foreach ($raw['subdivisions'] as $sub) {
            array_push(
                $this->subdivisions,
                new \GeoIp2\Record\Subdivision($sub, $locales)
            );
        }
    }

    /**
     * @ignore
     *
     * @param mixed $attr
     */
    public function __get($attr)
    {
        if ($attr === 'mostSpecificSubdivision') {
            return $this->$attr();
        }

        return parent::__get($attr);
    }

    /**
     * @ignore
     *
     * @param mixed $attr
     */
    public function __isset($attr)
    {
        if ($attr === 'mostSpecificSubdivision') {
            // We always return a mostSpecificSubdivision, even if it is the
            // empty subdivision
            return true;
        }

        return parent::__isset($attr);
    }

    private function mostSpecificSubdivision()
    {
        return empty($this->subdivisions) ?
            new \GeoIp2\Record\Subdivision([], $this->locales) :
            end($this->subdivisions);
    }
}
