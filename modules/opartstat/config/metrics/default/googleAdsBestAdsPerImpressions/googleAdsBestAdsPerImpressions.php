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

function getGoogleAdsBestAdsPerImpressions($vars,$humanResult=true)
{   
    return OpartStatTools::getBestMetricResult('googleAdsBestAdsPerImpressions', $vars);
}

function getGoogleAdsBestAdsPerImpressionsValues($dateFrom,$dateTo,$filtersArray,$start,$limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'start' => $start,
        'limit' => $limit
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/bestAdsPerImpressions.php",$useCache,$datas);

    $campaigns = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE  
            googleAdsAds.id,
            googleAdsAds.name,
            impressions as total 
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        JOIN
            " . _DB_PREFIX_ . "opartstat_googleAdsAds googleAdsAds
        ON
            googleAdsAds.id = googleAdsDailyDatas.adId
        WHERE 
            createdAt >= '".pSQL($dateFrom)."' 
        AND 
            createdAt <= '".pSQL($dateTo)."'
        LIMIT 
            ".(int)$start.",".(int)$limit."
        ";

    $campaigns = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if ($campaigns == null || count($campaigns) == 0)
        return $result;    

    $mergedCampaigns = [];
    
    foreach ($campaigns as $p) {
        if (isset($mergedCampaigns[$p['id']])) {
            $mergedCampaigns[$p['id']]['total'] += $p['total'];
        } else {
            $campaignName = ($p['name'] == null)?'Unknow':$p['name'];
            $mergedCampaigns[$p['id']] = [
                'id' => $p['id'],
                'name' => '('.$p['id'].') '.$campaignName,
                'total' => $p['total'],
                'count' => 1,
            ];
        }
    }

    $result['value'] = $mergedCampaigns;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}