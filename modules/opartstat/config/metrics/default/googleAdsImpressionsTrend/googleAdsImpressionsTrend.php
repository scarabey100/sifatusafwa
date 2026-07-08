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

function getGoogleAdsImpressionsTrend($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('googleAdsImpressionsTrend',$vars);
}

function getGoogleAdsImpressionsTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'filtersArray' => $filtersArray,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/impressionsTrend.php",$useCache,$datas);
    $cpms = $response['datas']; */

    //$orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $sql = "SELECT SQL_NO_CACHE 
            SUM(impressions) as total,
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

    $r = OpartStatTools::populatePeriodArray($dateFrom,$dateTo,$cpms,'createdAt','total');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => ''
    ];

    return $result;
}