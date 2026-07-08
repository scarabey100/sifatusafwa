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

function getVisitorsTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('VisitorsTrend', $vars);
}

function getVisitorsTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{            
    $result['value'] = 0;
    $result['conf'] = [
        'total' => '',
    ];

    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);
       
    $ipList = [];

    $periodArrays = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom, $dateTo);
    $totalPerYear = $periodArrays['y'];
    $totalPerMonth = $periodArrays['m'];
    $totalPerWeek = $periodArrays['w'];
    $totalPerDay = $periodArrays['d'];

    foreach ($totalPerYear as $y => $array1) {
        $totalPerYear[$y][0]['value'] = 0;
        $totalPerYear[$y][0]['usersIp'] = [];
    }

    foreach ($totalPerMonth as $y => $array1) {
        foreach ($array1 as $m => $array2) {
            $totalPerMonth[$y][$m]['value'] = 0;
            $totalPerMonth[$y][$m]['usersIp'] = [];
        }
    }
    foreach ($totalPerWeek as $y => $array1) {
        foreach ($array1 as $w => $array2) {
            $totalPerWeek[$y][$w]['value'] = 0;
            $totalPerWeek[$y][$w]['usersIp'] = [];
        }
    }
    foreach ($totalPerDay as $y => $array1) {
        foreach ($array1 as $d => $array2) {
            $totalPerDay[$y][$d]['value'] = 0;
            $totalPerDay[$y][$d]['usersIp'] = [];
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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/visitorsTrend.php", $useCache, $datas);

        if ($response['datas'] == null)
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
                    DISTINCT first_visits.userIp as userIp,
                    createdAt 
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

        $days = OpartStatTools::executeSessionsSelect($sql);
    /* } */
    foreach ($days as $day) {
        $y = (int)date("y", strtotime($day['createdAt']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($day['createdAt']));
        $m = (int)date("n", strtotime($day['createdAt']));
        $w = (int)date("W", strtotime($day['createdAt']));
        $d = (int)date("z", strtotime($day['createdAt']));

        if (!in_array($day['userIp'], $totalPerYear[$y][0]['usersIp'])) {
            array_push($totalPerYear[$y][0]['usersIp'], $day['userIp']);
            $totalPerYear[$y][0]['value']++;
        }

        if (!in_array($day['userIp'], $totalPerMonth[$y][$m]['usersIp'])) {
            array_push($totalPerMonth[$y][$m]['usersIp'], $day['userIp']);
            $totalPerMonth[$y][$m]['value']++;
        }
        if (!in_array($day['userIp'], $totalPerWeek[$yForWeek][$w]['usersIp'])) {
            array_push($totalPerWeek[$yForWeek][$w]['usersIp'], $day['userIp']);
            $totalPerWeek[$yForWeek][$w]['value']++;
        }
        if (!in_array($day['userIp'], $totalPerDay[$y][$d]['usersIp'])) {
            array_push($totalPerDay[$y][$d]['usersIp'], $day['userIp']);
            $totalPerDay[$y][$d]['value']++;
        }
        if (!in_array($day['userIp'], $ipList)) {
            array_push($ipList, $day['userIp']);
        }
    }

    ksort($totalPerYear);
    ksort($totalPerMonth);
    ksort($totalPerWeek);
    ksort($totalPerDay);

    $result['value'] = [
        'globalValue' => count($ipList),
        'perYear' => $totalPerYear,
        'perMonth' => $totalPerMonth,
        'perWeek' => $totalPerWeek,
        'perDay' => $totalPerDay
    ];
    $result['conf'] = [
        'globalValueFormat' => ''
    ];
    return $result;
}
