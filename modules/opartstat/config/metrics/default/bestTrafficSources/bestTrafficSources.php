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

function getBestTrafficSources($vars)
{
    return OpartStatTools::getBestMetricResult('bestTrafficSources', $vars);
}

function getBestTrafficSourcesValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/bestTrafficSources.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $visits = $response['datas'];
    } else { */
        $lastStatDate = opartSession::getLastStatDate();
        if ($lastStatDate == false)
            return $result;

        if ($dateFrom < $lastStatDate)
            $dateFrom = $lastStatDate;

        /* $sql = "
                SELECT SQL_NO_CACHE DISTINCT
                    first_visits.date,
                    first_visits.userIp,
                    opartstat_sessions.referrer
                FROM
                    (
                        SELECT
                            DATE_FORMAT(createdAt, '%Y-%m-%d') AS date,
                            userIp,
                            MIN(createdAt) AS first_visit
                        FROM
                            `" . _DB_PREFIX_ . "opartstat_sessions` opartstat_sessions
                        WHERE
                            `createdAt` >= '" . pSQL($dateFrom) . "' 
                        AND 
                            `createdAt` <= '" . pSQL($dateTo) . "' 
                        AND 
                            " . $shopConstraints . " 
                            ".$sqlFilters."
                        GROUP BY
                            date,
                            userIp
                    ) AS first_visits
                JOIN
                    `" . _DB_PREFIX_ . "opartstat_sessions` opartstat_sessions 
                ON 
                    first_visits.first_visit = opartstat_sessions.createdAt 
                AND 
                    first_visits.userIp = opartstat_sessions.userIp
                LIMIT 
                    " . (int)$start . ", " . (int)$limit; */

        $sql = "SELECT SQL_NO_CACHE 
                    opartstat_sessions.visiteId, 
                    opartstat_sessions.referrer
                FROM 
                    `" . pSQL($sessionsTable) . "` opartstat_sessions
                WHERE 
                    `createdAt` >= '" . pSQL($dateFrom) . "'
                AND 
                    `createdAt` <= '" . pSQL($dateTo) . "'
                AND 
                    " . $shopConstraints . "
                    " . $sqlFilters . "
                LIMIT 
                    " . (int)$start . ", " . (int)$limit;

        $visits = OpartStatTools::executeSessionsSelect($sql);
    /* } */

    if (count($visits) == 0)
        return $result;

    $mergedReferrerList = [];

    $shopUrl = _PS_BASE_URL_ . __PS_BASE_URI__;

    $shopDomain = opartStatTools::getDomainNameFromUrl($shopUrl);

    foreach ($visits as $v) {
        $domain = $v['referrer'];

        /* if ($domain == $shopDomain)
            $domain = 'Direct'; */

        if ($domain == $shopDomain)
            continue;

        $domain = ($domain == '') ? 'unknow' : $domain;

        if (!empty($mergedReferrerList[$domain]))
            $mergedReferrerList[$domain] = $mergedReferrerList[$domain] + 1;
        else
            $mergedReferrerList[$domain] = 1;
    }

    $referrerList = [];
    foreach ($mergedReferrerList as $name => $visit) {
        $referrerList[$name] = [
            'id' => $name,
            'name' => $name,
            'urlLink' => 'https://' . $name,
            'total' => $visit
        ];
    }

    $result['value'] = $referrerList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
