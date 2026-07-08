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

function getGoogleAdsBestCampaignsPerCtr($vars,$humanResult=true)
{   
    return OpartStatTools::getBestMetricResult('googleAdsBestCampaignsPerCtr', $vars);
}

function getGoogleAdsBestCampaignsPerCtrValues($dateFrom,$dateTo,$filtersArray,$start,$limit,$vars) {
    $result['conf']['total'] = 'percent';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

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

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/bestCampaignsPerCtr.php",$useCache,$datas);

    $campaigns = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE  
            googleAdsCampaigns.id,
            googleAdsCampaigns.name,
            clicks,
            impressions
        FROM 
            " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
        JOIN 
            " . _DB_PREFIX_ . "opartstat_googleAdsCampaigns googleAdsCampaigns
        ON
            googleAdsCampaigns.id = googleAdsDailyDatas.campaignId
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
            $mergedCampaigns[$p['id']]['impressions'] += $p['impressions'];
            $mergedCampaigns[$p['id']]['clicks'] += $p['clicks'];
        } else {
            $campaignName = ($p['name'] == null)?'Unknow':$p['name'];
            $mergedCampaigns[$p['id']] = [
                'id' => $p['id'],
                'name' => '('.$p['id'].') '.$campaignName,
                'impressions' => $p['impressions'],
                'clicks' => $p['clicks']
            ];
        }
    }
    
    foreach ($mergedCampaigns as $campaignId => $campaignData) {
        if($campaignData['impressions']>0)
            $mergedCampaigns[$campaignId]['total'] = ($campaignData['clicks']/$campaignData['impressions'])*100;
        else
            $mergedCampaigns[$campaignId]['total'] = 0; 
    }

    $result['value'] = $mergedCampaigns;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}