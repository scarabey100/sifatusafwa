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
function getPagesViewsPerVisit($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('PagesViewsPerVisit', $vars);
}

function getPagesViewsPerVisitValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => 'float2',
    ];

    $lastStatDate = opartSession::getLastStatDate();
    if ($lastStatDate == false)  
        return $result;
    
    if ($dateFrom < $lastStatDate)
        $dateFrom = $lastStatDate;   

    $useCache = true;

    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* if (Configuration::get('OPARTSTAT_USE_SAAS')) {
        $shops = opartStatTools::getShops();        
        $datas = [
            'filtersArray' => $filtersArray,
            'shops' => $shops,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/pageViewsPerVisit.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $result['value'] = $response['datas'];
        return $result;
    } */

    $shopConstraints = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
    $sqlFilters = OpartStatTools::getFiltersForOpartSessionTable($filtersArray);
     $sessionsTable = OpartStatTools::getSessionsTableName(); 
    $sql = "SELECT
        (
            (
                SELECT  
                    COUNT(userIp)
                FROM 
                     `" . pSQL($sessionsTable) . "` opartstat_sessions
                WHERE 
                    `createdAt` >= '" . pSQL($dateFrom) . "'
                AND 
                    `createdAt` <= '" . pSQL($dateTo) . "'
                AND 
                    " . $shopConstraints . "
                ".$sqlFilters."
                ) / 
                (
                    SELECT
                        COUNT(DISTINCT CONCAT(DATE(first_visits.first_visit), first_visits.userIp))
                    FROM
                    (
                        SELECT
                            opartstat_sessions.userIp,
                            MIN(createdAt) AS first_visit
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
                        first_visits.first_visit = opartstat_sessions.createdAt 
                    AND 
                        first_visits.userIp = opartstat_sessions.userIp
                    WHERE
                        " . $shopConstraints . "
                        ".$sqlFilters."
                )
            ) as pageViewsPerVisite";  

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'float2',true);
}
