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

function getVisits($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('Visits', $vars);
}

function getVisitsValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => '',
    ];

    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* if (Configuration::get('OPARTSTAT_USE_SAAS')) {
        $shops = opartStatTools::getShops();        
        $datas = [
            'filtersArray' => $filtersArray,
            'shops' => $shops,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/visits.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $result['value'] = $response['datas'];
        return $result;
    } */

    $sqlFilters = OpartStatTools::getFiltersForOpartSessionTable($filtersArray);
    $shopConstraints = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
    $lastStatDate = opartSession::getLastStatDate();
    $sessionsTable = OpartStatTools::getSessionsTableName();
    if ($lastStatDate == false)
        return $result;

    if ($dateFrom < $lastStatDate)
        $dateFrom = $lastStatDate;

    $sql ="SELECT SQL_NO_CACHE 
            COUNT(*) AS total
            FROM (
                    SELECT
                        MIN(createdAt) AS first_visit
                    FROM
                         `" . pSQL($sessionsTable) . "` opartstat_sessions
                    WHERE
                        `createdAt` BETWEEN '" . pSQL($dateFrom) . "' AND '" . pSQL($dateTo) . "'
                    AND
                        " . $shopConstraints . "
                        ".$sqlFilters."
                    GROUP BY
                        userIp,
                        DATE(createdAt)
                ) AS subquery";
            
    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'',true);
}
