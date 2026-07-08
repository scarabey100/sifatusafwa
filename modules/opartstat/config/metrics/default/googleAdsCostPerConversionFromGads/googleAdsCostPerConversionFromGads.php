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

function getGoogleAdsCostPerConversionFromGads($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('GoogleAdsCostPerConversionFromGads',$vars,'price',false);
}

function getGoogleAdsCostPerConversionFromGadsValues($dateFrom,$dateTo,$filtersArray,$vars) {
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

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/costPerConversionFromGads.php",$useCache,$datas);

    $total = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE 
            SUM(costMicros/1000000) / SUM(conversions)
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        WHERE 
            createdAt >= '".pSQL($dateFrom)."' 
        AND 
            createdAt <= '".pSQL($dateTo)."'
        ";
        
    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'price');

}