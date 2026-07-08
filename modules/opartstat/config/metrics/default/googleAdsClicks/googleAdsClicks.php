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

function getGoogleAdsClicks($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('GoogleAdsClicks',$vars);
}

function getGoogleAdsClicksValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $result['value'] = 0;
    $result['conf'] = [
        'total' => ''
    ];
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'filtersArray' => $filtersArray,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/clicks.php",$useCache,$datas);
    if ($response['datas'] == null)
        return $result; */

    $sql = "SELECT SQL_NO_CACHE 
            SUM(clicks) 
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        WHERE 
            createdAt >= '".pSQL($dateFrom)."' 
        AND 
            createdAt <= '".pSQL($dateTo)."'
        ";
    
    return OpartStatTools::getSingleNumberJsonResult($sql, $dateTo, $useCache);
}