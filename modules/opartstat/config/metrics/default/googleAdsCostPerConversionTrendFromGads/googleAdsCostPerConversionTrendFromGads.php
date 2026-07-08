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

function getGoogleAdsCostPerConversionTrendFromGads($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('googleAdsCostPerConversionTrendFromGads',$vars,'price',false);
}

function getGoogleAdsCostPerConversionTrendFromGadsValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $result['value'] = 0;
    $result['conf'] = [
        'total' => 'price'
    ];
    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $sqlFilters = OpartStatTools::getFilters($filtersArray);

    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'sqlFilters' => $sqlFilters,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/costPerConversionTrendFromGads.php",$useCache,$datas);
    
    if ($response['datas'] == null)
        return $result; */

    $sql = "SELECT SQL_NO_CACHE 
                (
                    IFNULL(
                        (costMicros/1000000)/IFNULL(conversions, 1),0
                    )
                ) as total,
                createdAt 
            FROM 
                " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
            WHERE 
                createdAt >= '".pSQL($dateFrom)."' 
            AND 
                createdAt <= '".pSQL($dateTo)."'";
        
    $resultDatas = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom,$dateTo,$resultDatas,'createdAt','total');

    $sql = "SELECT SQL_NO_CACHE 
            SUM(costMicros/1000000) / SUM(conversions)
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        WHERE 
            createdAt >= '".pSQL($dateFrom)."' 
        AND 
            createdAt <= '".pSQL($dateTo)."'
        ";

    $totalGlobal = Db::getInstance()->getValue($sql);

    /* $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/costPerConversionFromGads.php",$useCache,$datas);    
    $totalGlobal = $response['datas']; */

    $result['value'] = [
        'globalValue' => $totalGlobal,
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => 'price'
    ];

    return $result;
}