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
if (!defined('_PS_VERSION_')) {
    exit;
}

return [
    'root' => [
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'type' => 'library',
        'install_path' => __DIR__ . '/../',
        'aliases' => [],
        'reference' => 'd3ca4f22d75e21f0aaae724eba07e2b7e2fe4426',
        'name' => 'zuko/geoip2',
        'dev' => false,
    ],
    'versions' => [
        'composer/ca-bundle' => [
            'pretty_version' => '1.3.5',
            'version' => '1.3.5.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../../../../../../Onedrive/Z-Data/git/ps1787/modules/ets_geolocation/vendor/geoip2/composer/ca-bundle',
            'aliases' => [],
            'reference' => '74780ccf8c19d6acb8d65c5f39cd72110e132bbd',
            'dev_requirement' => false,
        ],
        'geoip2/geoip2' => [
            'pretty_version' => 'v2.10.0',
            'version' => '2.10.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../../../../../../Onedrive/Z-Data/git/ps1787/modules/ets_geolocation/vendor/geoip2/geoip2/geoip2',
            'aliases' => [],
            'reference' => '419557cd21d9fe039721a83490701a58c8ce784a',
            'dev_requirement' => false,
        ],
        'maxmind-db/reader' => [
            'pretty_version' => 'v1.6.0',
            'version' => '1.6.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../../../../../../Onedrive/Z-Data/git/ps1787/modules/ets_geolocation/vendor/geoip2/maxmind-db/reader',
            'aliases' => [],
            'reference' => 'febd4920bf17c1da84cef58e56a8227dfb37fbe4',
            'dev_requirement' => false,
        ],
        'maxmind/web-service-common' => [
            'pretty_version' => 'v0.7.0',
            'version' => '0.7.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../../../../../../Onedrive/Z-Data/git/ps1787/modules/ets_geolocation/vendor/geoip2/maxmind/web-service-common',
            'aliases' => [],
            'reference' => '74c996c218ada5c639c8c2f076756e059f5552fc',
            'dev_requirement' => false,
        ],
        'zuko/geoip2' => [
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'type' => 'library',
            'install_path' => __DIR__ . '/../',
            'aliases' => [],
            'reference' => 'd3ca4f22d75e21f0aaae724eba07e2b7e2fe4426',
            'dev_requirement' => false,
        ],
    ],
];
