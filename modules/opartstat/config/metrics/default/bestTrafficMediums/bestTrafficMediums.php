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

function getBestTrafficMediums($vars)
{
    return OpartStatTools::getBestMetricResult('bestTrafficMediums', $vars);
}

function getBestTrafficMediumsValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/bestTrafficMediums.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $visits = $response['datas'];
    } 
    else { */
        $lastStatDate = opartSession::getLastStatDate();
        if ($lastStatDate == false)
            return $result;

        if ($dateFrom < $lastStatDate)
            $dateFrom = $lastStatDate;

        $sql = "SELECT SQL_NO_CACHE 
                opartstat_sessions.visiteId, 
                opartstat_sessions.utm_medium
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

    $mergedMediumList = [];

    foreach ($visits as $v) {
        $medium = ($v['utm_medium'] == '') ? 'Unknow' : $v['utm_medium'];

        if (!empty($mergedMediumList[$medium]))
            $mergedMediumList[$medium] = $mergedMediumList[$medium] + 1;
        else
            $mergedMediumList[$medium] = 1;
    }

    $mediumList = [];
    foreach ($mergedMediumList as $name => $visit) {
        $mediumList[$name] = [
            'id' => $name,
            'name' => $name,
            'total' => $visit,
        ];
    }

    $result['value'] = $mediumList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
