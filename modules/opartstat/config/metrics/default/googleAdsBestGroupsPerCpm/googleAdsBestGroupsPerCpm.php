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

function getGoogleAdsBestGroupsPerCpm($vars,$humanResult=true)
{   
    return OpartStatTools::getBestMetricResult('googleAdsBestGroupsPerCpm', $vars);
}

function getGoogleAdsBestGroupsPerCpmValues($dateFrom,$dateTo,$filtersArray,$start,$limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'filtersArray' => $filtersArray,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'start' => $start,
        'limit' => $limit
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/bestGroupsPerCpm.php",$useCache,$datas);

    $campaigns = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE  
            googleAdsGroups.id,
            googleAdsGroups.name,
            (impressions/1000) as impressions,
            (costMicros/1000000) as costs
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        JOIN 
            " . _DB_PREFIX_ . "opartstat_googleAdsGroups googleAdsGroups
        ON
            googleAdsGroups.id = googleAdsDailyDatas.groupId
        WHERE 
            createdAt >= '".pSQL($dateFrom)."' 
        AND 
            createdAt <= '".pSQL($dateTo)."'
        LIMIT 
            ".pSQL($start).",".pSQL($limit)."
        ";

    $campaigns = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if ($campaigns == null || count($campaigns) == 0)
        return $result;    

    $mergedCampaigns = [];
    
    foreach ($campaigns as $p) {
        if (isset($mergedCampaigns[$p['id']])) {
            $mergedCampaigns[$p['id']]['costs'] += $p['costs'];
            $mergedCampaigns[$p['id']]['impressions'] += $p['impressions'];
        } else {
            $campaignName = ($p['name'] == null)?'Unknow':$p['name'];
            $mergedCampaigns[$p['id']] = [
                'id' => $p['id'],
                'name' => '('.$p['id'].') '.$campaignName,
                'costs' => $p['costs'],
                'impressions' => $p['impressions']
            ];
        }
    }
    
    foreach ($mergedCampaigns as $groupId => $campaignData) {
        if($campaignData['impressions']>0)
            $mergedCampaigns[$groupId]['total'] = $campaignData['costs']/$campaignData['impressions'];
        else
            $mergedCampaigns[$groupId]['total'] = 0; 
    }

    $result['value'] = $mergedCampaigns;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}