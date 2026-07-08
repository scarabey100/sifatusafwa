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

function getBestTrafficCampaigns($vars)
{
    return OpartStatTools::getBestMetricResult('bestTrafficCampaigns', $vars);
}

function getBestTrafficCampaignsValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
{
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');

    $sqlFilters = OpartStatTools::getFiltersForOpartSessionTable($filtersArray);
    $sessionsTable = OpartStatTools::getSessionsTableName(); 
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);


    /* if (Configuration::get('OPARTSTAT_USE_SAAS')) {
        $shops = opartStatTools::getShops();        
        $datas = [
            'filtersArray' => $filtersArray,
            'shops' => $shops,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'start' => $start,
            'limit' => $limit
        ];

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/bestTrafficCampaigns.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $visits = $response['datas'];
    } else { */
        $lastStatDate = opartSession::getLastStatDate();
        if ($lastStatDate == false)
            return $result;

        if ($dateFrom < $lastStatDate)
            $dateFrom = $lastStatDate;

        $sql = "SELECT SQL_NO_CACHE 
                opartstat_sessions.visiteId, 
                opartstat_sessions.utm_campaign
            FROM 
                `" . pSQL($sessionsTable) . "` opartstat_sessions
            WHERE 
                `createdAt` >= '" . pSQL($dateFrom) . "'
            AND 
                `createdAt` <= '" . pSQL($dateTo) . "'
            AND 
                " . $shopConstraints . "
                ".$sqlFilters."
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

        $visits = OpartStatTools::executeSessionsSelect($sql);
    /* } */
    if (count($visits) == 0)
        return $result;

    $mergedCampaignList = [];

    foreach ($visits as $v) {
        $campaign = ($v['utm_campaign'] == '') ? 'Unknow' : $v['utm_campaign'];

        if (!empty($mergedCampaignList[$campaign]))
            $mergedCampaignList[$campaign] = $mergedCampaignList[$campaign] + 1;
        else
            $mergedCampaignList[$campaign] = 1;
    }

    $campaignList = [];
    foreach ($mergedCampaignList as $name => $visit) {
        $campaignList[$name] = [
            'total' => $visit,
            'name' => $name,
            'id' => $name
        ];
    }

    $result['value'] = $campaignList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
