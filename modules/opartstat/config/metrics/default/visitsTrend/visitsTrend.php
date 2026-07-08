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

function getVisitsTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('VisitsTrend', $vars);
}

function getVisitsTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => '',
    ];

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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/visitsTrend.php",$useCache,$datas); 

        if($response['datas'] == null)
            return $result;

        $days = $response['datas'];
    }
    else { */
        $shopConstraints = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
        $sqlFilters = OpartStatTools::getFiltersForOpartSessionTable($filtersArray);
        $lastStatDate = opartSession::getLastStatDate();
        $sessionsTable = OpartStatTools::getSessionsTableName();
        if ($lastStatDate == false) 
            return $result;

        if ($dateFrom < $lastStatDate)
            $dateFrom = $lastStatDate;

        $sql = "SELECT
                    COUNT(DISTINCT CONCAT(DATE(first_visits.first_visit), first_visits.userIp)) as nbVisite, 
                    MIN(createdAt) as createdAt
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
                GROUP BY 
                    DATE_FORMAT(createdAt,'%Y-%m-%d')
                "; 
        $days = OpartStatTools::executeSessionsSelect($sql);
    /* } */

    $r = OpartStatTools::populatePeriodArray($dateFrom, $dateTo, $days, 'createdAt', 'nbVisite');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => ''
    ];
    return $result;
}
