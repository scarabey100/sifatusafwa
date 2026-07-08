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

function getGoogleAdsCpcTrend($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('googleAdsCpcTrend',$vars,'price',false);
}

function getGoogleAdsCpcTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
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

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/cpcTrend.php",$useCache,$datas);
    
    if ($response['datas'] == null)
        return $result;

    $cpcs = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE 
            (
                IF(
                    SUM(clicks) = 0, 0, SUM(costMicros/1000000)/SUM(clicks)
                )
            ) as total,
            SUM(costMicros/1000000) as costs,
            SUM(clicks) as clicks,
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

    $cpcs = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    //$orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom,$dateTo,$cpcs,'createdAt','total');
    
    $totalCosts = 0;
    $totalClicks = 0;
    foreach($cpcs as $cpc) {
        $totalCosts += $cpc['costs'];
        $totalClicks += $cpc['clicks'];
    }

    $r['totalGlobal'] = ($totalClicks == 0)?0:$totalCosts / $totalClicks;

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    return $result;
}