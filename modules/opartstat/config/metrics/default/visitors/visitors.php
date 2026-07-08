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

function getVisitors($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('Visitors', $vars);
}

function getVisitorsValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => '',
    ];

    $shopConstraints = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sqlFilters = '';
    //$sqlIn = "";
    if (
        is_array($filtersArray) && count($filtersArray) > 0) {
        foreach($filtersArray as $excludeInclude => $array) {
            foreach ($array as $filterName => $filterValue) {
                if (!array_key_exists('values', $filterValue))
                    continue;

                if ($filterName == 'categories') {
                    $filterName = 'categoriesVisits';
                    $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'opartstat_sessions','shopId');
                }
                                

                if ($filterName == 'brands') {
                    $filterName = 'brandsVisits';
                    $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'opartstat_sessions','shopId'); 
                }
                             

                if ($filterName == 'products') {
                    $filterName = 'productsVisits';
                    $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'opartstat_sessions','shopId');
                }    
                
                if ($filterName == 'device') {
                    $filterName = 'device';
                    $sqlFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'opartstat_sessions','shopId');
                }
            }
        }
        
    }

    /* if (Configuration::get('OPARTSTAT_USE_SAAS')) {
        $shops = opartStatTools::getShops();        
        $datas = [
            'filtersArray' => $filtersArray,
            'shops' => $shops,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/visitors.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $result['value'] = $response['datas'];
        return $result;
    } */

    $lastStatDate = opartSession::getLastStatDate();
    $sessionsTable = OpartStatTools::getSessionsTableName();
    if ($lastStatDate == false) 
        return $result;
    
    if ($dateFrom < $lastStatDate)
        $dateFrom = $lastStatDate;

    $sql ="SELECT
                COUNT(DISTINCT first_visits.userIp)
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
                    userIp
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
            ";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'',true);
}
