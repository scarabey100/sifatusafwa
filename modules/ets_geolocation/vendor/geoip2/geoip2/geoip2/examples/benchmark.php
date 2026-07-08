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
require __DIR__ . '/../vendor/autoload.php';

use GeoIp2\Database\Reader;

srand(0);

$reader = new Reader('GeoIP2-City.mmdb');
$count = 500000;
$startTime = microtime(true);
for ($i = 0; $i < $count; ++$i) {
    $ip = long2ip(rand(0, pow(2, 32) - 1));
    try {
        $t = $reader->city($ip);
    } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
    }
    if ($i % 10000 === 0) {
        echo $i . ' ' . $ip . "\n";
    }
}
$endTime = microtime(true);

$duration = $endTime - $startTime;
echo 'Requests per second: ' . $count / $duration . "\n";
