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

function getGoogleAdsCtrTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('googleAdsCtrTrend', $vars, 'float4');
}

function getGoogleAdsCtrTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = [
        'globalValue' => 0
    ];
    $result['conf'] = [
        'globalValueFormat' => 'percent'
    ];
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'filtersArray' => $filtersArray,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/ctrTrend.php",$useCache,$datas);
    if ($response['datas'] == null)
        return $result;
    $ctrs = $response['datas']; */

    //$orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $sql = "SELECT SQL_NO_CACHE 
                (
                    IF(
                        SUM(impressions) = 0, 0, (SUM(clicks)/SUM(impressions))*100
                    )
                ) as total,
                SUM(clicks) as clicks,
                SUM(impressions) impressions,
                createdAt 
            FROM 
                " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
            WHERE 
                createdAt >= '" . pSQL($dateFrom) . "' 
            AND 
                createdAt <= '" . pSQL($dateTo) . "'
            GROUP BY
                createdAt
        ";

    $ctrs = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom, $dateTo, $ctrs, 'createdAt', 'total');

    $totalClicks = 0;
    $totalImpressions = 0;
    foreach ($ctrs as $ctr) {
        $totalClicks += $ctr['clicks'];
        $totalImpressions += $ctr['impressions'];
    }

    $r['totalGlobal'] = ($totalImpressions == 0) ? 0 : ($totalClicks / $totalImpressions) * 100;

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    return $result;
}
