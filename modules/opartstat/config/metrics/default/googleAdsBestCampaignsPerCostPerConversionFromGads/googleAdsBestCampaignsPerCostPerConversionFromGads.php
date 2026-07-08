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

function getGoogleAdsBestCampaignsPerCostPerConversionFromGads($vars, $humanResult = true)
{
    return OpartStatTools::getBestMetricResult('googleAdsBestCampaignsPerCostPerConversionFromGads', $vars);
}

function getGoogleAdsBestCampaignsPerCostPerConversionFromGadsValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
{
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $sqlFilters = OpartStatTools::getFilters($filtersArray);

    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

  /*   $datas = [
        'sqlFilters' => $sqlFilters,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'start' => $start,
        'limit' => $limit
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/bestCampaignsPerCostPerConversionFromGads.php", $useCache, $datas);

    $campaigns = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE DISTINCT
            googleAdsDailyDatas.campaignId,
            googleAdsDailyDatas.adId,
            googleAdsDailyDatas.createdAt,
            googleAdsCampaigns.name,                        
            (costMicros/1000000) as costs,            
            conversions as conversions 
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

    $mergedResults = [];

    foreach ($campaigns as $p) {
        if (isset($mergedResults[$p['campaignId']])) {
            $mergedResults[$p['campaignId']]['conversions'] += $p['conversions'];
            $mergedResults[$p['campaignId']]['costs'] += $p['costs'];
        } else {
            $campaignName = ($p['name'] == null) ? 'Unknow' : $p['name'];
            $mergedResults[$p['campaignId']] = [
                'id' => $p['campaignId'],
                'name' => '(' . $p['campaignId'] . ') ' . $campaignName,
                'total' => $p['costs'],
                'conversions' => $p['conversions'],
                'costs' => $p['costs']
            ];
        }
    }

    foreach ($mergedResults as $k => $p) {
        $p['conversions'] = ($p['conversions'] == 0)?1:$p['conversions'];
        $mergedResults[$k]['total'] = $p['costs'] / $p['conversions'];
    }

    $result['value'] = $mergedResults;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
