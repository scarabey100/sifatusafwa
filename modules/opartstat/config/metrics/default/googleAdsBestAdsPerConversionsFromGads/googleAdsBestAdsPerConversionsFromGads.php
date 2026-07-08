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

function getGoogleAdsBestAdsPerConversionsFromGads($vars, $humanResult = true)
{
    return OpartStatTools::getBestMetricResult('googleAdsBestAdsPerConversionsFromGads', $vars);
}

function getGoogleAdsBestAdsPerConversionsFromGadsValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
{
    $result['conf']['total'] = 'float2';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    //$sqlFilters = OpartStatTools::getFilters($filtersArray);
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $datas = [
        'sqlFilters' => $sqlFilters,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'start' => $start,
        'limit' => $limit
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/bestAdsPerConversionsFromGads.php", $useCache, $datas);

    $ads = $response['datas']; */

    $sql = "SELECT SQL_NO_CACHE DISTINCT
        googleAdsDailyDatas.groupId,
        googleAdsDailyDatas.adId,
        googleAdsDailyDatas.createdAt,
        googleAdsAds.name,
        conversions as total 
    FROM 
        "._DB_PREFIX_."opartstat_googleAdsDailyDatas googleAdsDailyDatas
    JOIN 
        "._DB_PREFIX_."opartstat_googleAdsAds googleAdsAds
    ON
        googleAdsAds.id = googleAdsDailyDatas.adId
    WHERE 
        createdAt >= '".pSQL($dateFrom)."' 
    AND 
        createdAt <= '".pSQL($dateTo)."'
    LIMIT 
        ".pSQL($start).",".pSQL($limit)."
    ";

    $ads = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if ($ads == null || count($ads) == 0)
        return $result;

    $mergedResults = [];

    foreach ($ads as $p) {
        if (isset($mergedResults[$p['adId']])) {
            $mergedResults[$p['adId']]['total'] += $p['total'];
        } else {
            $adName = ($p['name'] == null) ? 'Unknow' : $p['name'];
            $mergedResults[$p['adId']] = [
                'id' => $p['adId'],
                'name' => '(' . $p['adId'] . ') ' . $adName,
                'total' => $p['total'],
                'count' => 1,
            ];
        }
    }

    $result['value'] = $mergedResults;
    $result['conf']['allDataLoaded'] = true;

    return $result;
}
