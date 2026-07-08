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

function getPageViewsPerVisitTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('PageViewsPerVisitTrend', $vars, 'float2');
}

function getPageViewsPerVisitTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/pageViewsPerVisitTrend.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $pageViewsDays = $response['datas']['pageViewsDays'];
        $visitsDays = $response['datas']['visitsDays'];
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
    
        $sql1 = "SELECT SQL_NO_CACHE 
                    COUNT(userIp) as pageViews,
                    DATE_FORMAT(createdAt,'%Y-%m-%d') createdAt
                FROM 
                    `" . pSQL($sessionsTable) . "` opartstat_sessions
                WHERE 
                    `createdAt` >= '" . pSQL($dateFrom) . "'
                AND 
                    `createdAt` <= '" . pSQL($dateTo) . "'
                AND 
                    " . $shopConstraints . "
                    ".$sqlFilters."
                GROUP BY 
                    DATE_FORMAT(createdAt,'%Y-%m-%d')";

        //$pageViewsDays = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql1);
        $pageViewsDays =  OpartStatTools::executeSessionsSelect($sql1);

        $sql2 = "SELECT
                COUNT(DISTINCT CONCAT(DATE(first_visits.first_visit), first_visits.userIp)) as visits,
                DATE_FORMAT(createdAt,'%Y-%m-%d') createdAt
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
                DATE_FORMAT(createdAt,'%Y-%m-%d')";

        //$visitsDays = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql2);
        $visitsDays = OpartStatTools::executeSessionsSelect($sql2);
    /* } */
    $totalGlobal['nbVisite'] = 0;
    $totalGlobal['nbPageViews'] = 0;

    $periodArrays = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom, $dateTo);
    $totalPerYear = $periodArrays['y'];
    $totalPerMonth = $periodArrays['m'];
    $totalPerWeek = $periodArrays['w'];
    $totalPerDay = $periodArrays['d'];

    foreach ($totalPerYear as $y => $array1) {
        $totalPerYear[$y][0]['value'] = 0;
        $totalPerYear[$y][0]['nbVisite'] = 0;
        $totalPerYear[$y][0]['nbPageViews'] = 0;
    }

    foreach ($totalPerMonth as $y => $array1) {
        foreach ($array1 as $m => $array2) {
            $totalPerMonth[$y][$m]['value'] = 0;
            $totalPerMonth[$y][$m]['nbVisite'] = 0;
            $totalPerMonth[$y][$m]['nbPageViews'] = 0;
        }
    }
    foreach ($totalPerWeek as $y => $array1) {
        foreach ($array1 as $w => $array2) {
            $totalPerWeek[$y][$w]['value'] = 0;
            $totalPerWeek[$y][$w]['nbVisite'] = 0;
            $totalPerWeek[$y][$w]['nbPageViews'] = 0;
        }
    }
    foreach ($totalPerDay as $y => $array1) {
        foreach ($array1 as $d => $array2) {
            $totalPerDay[$y][$d]['value'] = 0;
            $totalPerDay[$y][$d]['nbVisite'] = 0;
            $totalPerDay[$y][$d]['nbPageViews'] = 0;
        }
    }

    $days = [];

    foreach ($pageViewsDays as $pageViewsDay) {
        $days[$pageViewsDay['createdAt']]['nbPageViews'] = $pageViewsDay['pageViews'];
        $days[$pageViewsDay['createdAt']]['createdAt'] = $pageViewsDay['createdAt'];
    }

    foreach ($visitsDays as $visitsDay) {
        $days[$visitsDay['createdAt']]['nbVisite'] = $visitsDay['visits'];
        $days[$visitsDay['createdAt']]['createdAt'] = $visitsDay['createdAt'];
    }

    // Remplir les valeurs manquantes par 0
    foreach ($days as $key => $value) {
        if (!isset($value['nbPageViews'])) {
            $days[$key]['nbPageViews'] = 0;
        }
        if (!isset($value['nbVisite'])) {
            $days[$key]['nbVisite'] = 0;
        }
        if ($days[$key]['nbVisite'] == 0)
            $days[$key]['pageViewsPerVisite'] = 0;
        else
            $days[$key]['pageViewsPerVisite'] = $days[$key]['nbPageViews'] / $days[$key]['nbVisite'];
    }

    foreach ($days as $key => $day) {
        //$dayTotal = (float)$day['pageViewsPerVisite'];
        (int)$totalGlobal['nbVisite'] = (isset($totalGlobal['nbVisite'])) ? $totalGlobal['nbVisite'] + $day['nbVisite'] : $day['nbVisite'];
        (int)$totalGlobal['nbPageViews'] = (isset($totalGlobal['nbPageViews'])) ? $totalGlobal['nbPageViews'] + $day['nbPageViews'] : $day['nbPageViews'];

        $y = (int)date("y", strtotime($day['createdAt']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($day['createdAt']));
        $m = (int)date("n", strtotime($day['createdAt']));
        $w = (int)date("W", strtotime($day['createdAt']));
        $d = (int)date("z", strtotime($day['createdAt']));

        (int)$totalPerYear[$y][0]['nbVisite'] = (isset($totalPerYear[$y][0]['nbVisite'])) ? $totalPerYear[$y][0]['nbVisite'] + $day['nbVisite'] : $day['nbVisite'];
        (int)$totalPerYear[$y][0]['nbPageViews'] = (isset($totalPerYear[$y][0]['nbPageViews'])) ? $totalPerYear[$y][0]['nbPageViews'] + $day['nbPageViews'] : $day['nbPageViews'];

        (int)$totalPerMonth[$y][$m]['nbVisite'] = (isset($totalPerMonth[$y][$m]['nbVisite'])) ? $totalPerMonth[$y][$m]['nbVisite'] + $day['nbVisite'] : $day['nbVisite'];
        (int)$totalPerMonth[$y][$m]['nbPageViews'] = (isset($totalPerMonth[$y][$m]['nbPageViews'])) ? $totalPerMonth[$y][$m]['nbPageViews'] + $day['nbPageViews'] : $day['nbPageViews'];

        (int)$totalPerWeek[$yForWeek][$w]['nbVisite'] = (isset($totalPerWeek[$yForWeek][$w]['nbVisite'])) ? $totalPerWeek[$yForWeek][$w]['nbVisite'] + $day['nbVisite'] : $day['nbVisite'];
        (int)$totalPerWeek[$yForWeek][$w]['nbPageViews'] = (isset($totalPerWeek[$yForWeek][$w]['nbPageViews'])) ? $totalPerWeek[$yForWeek][$w]['nbPageViews'] + $day['nbPageViews'] : $day['nbPageViews'];

        (int)$totalPerDay[$y][$d]['nbVisite'] = (isset($totalPerDay[$y][$d]['nbVisite'])) ? $totalPerDay[$y][$d]['nbVisite'] + $day['nbVisite'] : $day['nbVisite'];
        (int)$totalPerDay[$y][$d]['nbPageViews'] = (isset($totalPerDay[$y][$d]['nbPageViews'])) ? $totalPerDay[$y][$d]['nbPageViews'] + $day['nbPageViews'] : $day['nbPageViews'];
    }

    foreach ($totalPerYear as $y => $array1)
        $totalPerYear[$y][0]['value'] = ($totalPerYear[$y][0]['nbVisite'] > 0) ? $totalPerYear[$y][0]['nbPageViews'] / $totalPerYear[$y][0]['nbVisite'] : 0;

    foreach ($totalPerMonth as $y => $array1)
        foreach ($array1 as $m => $array2)
            $totalPerMonth[$y][$m]['value'] = ($totalPerMonth[$y][$m]['nbVisite'] > 0) ? $totalPerMonth[$y][$m]['nbPageViews'] / $totalPerMonth[$y][$m]['nbVisite'] : 0;

    foreach ($totalPerWeek as $y => $array1)
        foreach ($array1 as $w => $array2)
            $totalPerWeek[$y][$w]['value'] = ($totalPerWeek[$y][$w]['nbVisite'] > 0) ? $totalPerWeek[$y][$w]['nbPageViews'] / $totalPerWeek[$y][$w]['nbVisite'] : 0;

    foreach ($totalPerDay as $y => $array1)
        foreach ($array1 as $d => $array2)
            $totalPerDay[$y][$d]['value'] = ($totalPerDay[$y][$d]['nbVisite'] > 0) ? $totalPerDay[$y][$d]['nbPageViews'] / $totalPerDay[$y][$d]['nbVisite'] : 0;

    ksort($totalPerYear);
    ksort($totalPerMonth);
    ksort($totalPerWeek);
    ksort($totalPerDay);

    $result['value'] = [
        'globalValue' => ($totalGlobal['nbVisite'] > 0) ? $totalGlobal['nbPageViews'] / $totalGlobal['nbVisite'] : 0,
        'perYear' => $totalPerYear,
        'perMonth' => $totalPerMonth,
        'perWeek' => $totalPerWeek,
        'perDay' => $totalPerDay
    ];
    $result['conf'] = [
        'globalValueFormat' => 'float2'
    ];
    return $result;
}
