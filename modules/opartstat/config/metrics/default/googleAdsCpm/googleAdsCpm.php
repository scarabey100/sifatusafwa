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

function getGoogleAdsCpm($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('GoogleAdsCpm',$vars,'price',false);
}

function getGoogleAdsCpmValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'filtersArray' => $filtersArray,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/cpm.php",$useCache,$datas);

    $total = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE 
            SUM(costMicros/1000000) / SUM(impressions/1000)
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        WHERE 
            createdAt >= '".pSQL($dateFrom)."' 
        AND 
            createdAt <= '".pSQL($dateTo)."'
        ";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'price');

    $valueType = 'price';
    $result['value'] = $total;
    $result['conf'] = [
        'total' => $valueType
    ];

    return $result;
}