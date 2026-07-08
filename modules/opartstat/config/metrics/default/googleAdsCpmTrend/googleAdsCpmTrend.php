<?php
/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/../../../../classes/opartStatTools.php');

function getGoogleAdsCpmTrend($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('googleAdsCpmTrend',$vars,'price',false);
}

function getGoogleAdsCpmTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $result['value'] = [
        'globalValue' => 0
    ];
    $result['conf'] = [
        'globalValueFormat' => 'price'
    ];

    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'filtersArray' => $filtersArray,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/cpmTrend.php",$useCache,$datas);
    if ($response['datas'] == null)
        return $result;

    $cpms = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE 
            (
                IF(
                    SUM(impressions/1000) = 0, 0, SUM(costMicros/1000000)/SUM(impressions/1000)
                )
            ) as total,
            SUM(costMicros/1000000) as costs,
            SUM(impressions/1000) impressions,
            createdAt
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        WHERE 
            createdAt >= '".pSQL($dateFrom)."' 
        AND 
            createdAt <= '".pSQL($dateTo)."'
        GROUP BY
            createdAt
        ";

    $cpms = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom,$dateTo,$cpms,'createdAt','total');

    $totalCosts = 0;
    $totalImpressions = 0;
    foreach($cpms as $cpm) {
        $totalCosts += $cpm['costs'];
        $totalImpressions += $cpm['impressions'];
    }

    $r['totalGlobal'] = ($totalImpressions == 0)?0:$totalCosts / $totalImpressions;

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    return $result;
}