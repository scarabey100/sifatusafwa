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

function getBestTrafficLandingPages($vars)
{
    return OpartStatTools::getBestMetricResult('bestTrafficLandingPages', $vars);
}

function getBestTrafficLandingPagesValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/bestTrafficLandingPages.php", $useCache, $datas);

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

        $sql = "SELECT
                    DISTINCT 
                    CONCAT(DATE(first_visits.first_visit), first_visits.userIp), 
                    opartstat_sessions.pageUrl
                FROM
                    (
                        SELECT
                            opartstat_sessions.userIp,
                            MIN(createdAt) AS first_visit,
                            MIN(opartstat_sessions.visiteId) AS first_id
                        FROM
                           `" . pSQL($sessionsTable) . "` opartstat_sessions
                        WHERE
                            `createdAt` BETWEEN '" . pSQL($dateFrom) . "' AND '" . pSQL($dateTo) . "'
                        GROUP BY
                            userIp, DATE(createdAt)
                    ) AS first_visits
                    JOIN 
                        `" . pSQL($sessionsTable) . "` opartstat_sessions 
                    ON
                        first_visits.first_id = opartstat_sessions.visiteId
                    WHERE
                        " . $shopConstraints . "
                        ".$sqlFilters."
                    LIMIT 
                        " . (int)$start . ", " . (int)$limit;

        $visits = OpartStatTools::executeSessionsSelect($sql);
    /* } */
    if (count($visits) == 0)
        return $result;

    $mergedPageList = [];

    foreach ($visits as $v) {
        $page = opartStatTools::cleanUrl($v['pageUrl']);
        $page = ($page == '') ? 'unknow' : $page;

        if (!empty($mergedPageList[$page]))
            $mergedPageList[$page] = $mergedPageList[$page] + 1;
        else
            $mergedPageList[$page] = 1;
    }

    $pageList = [];
    foreach ($mergedPageList as $name => $visit) {
        $pageList[$name] = [
            'id' => $name,
            'name' => $name,
            'total' => $visit,
        ];
    }

    $result['value'] = $pageList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
