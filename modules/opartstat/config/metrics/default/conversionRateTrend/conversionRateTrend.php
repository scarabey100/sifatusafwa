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

function getConversionRateTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('ConversionRateTrend', $vars, 'percent');
}

function getConversionRateTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => '',
    ];

    $shopConstraints = OpartStatTools::getShopConstraints();

    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    $totalVisits = 0;
    $totalOrders = 0;

    $periodArrays = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom, $dateTo, $filtersArray, $vars);
    $totalPerYear = $periodArrays['y'];
    $totalPerMonth = $periodArrays['m'];
    $totalPerWeek = $periodArrays['w'];
    $totalPerDay = $periodArrays['d'];

    foreach ($totalPerYear as $y => $array1) {
        $totalPerYear[$y][0]['visits'] = 0;
        $totalPerYear[$y][0]['orders'] = 0;
    }

    foreach ($totalPerMonth as $y => $array1) {
        foreach ($array1 as $m => $array2) {
            $totalPerMonth[$y][$m]['visits'] = 0;
            $totalPerMonth[$y][$m]['orders'] = 0;
        }
    }
    foreach ($totalPerWeek as $y => $array1) {
        foreach ($array1 as $w => $array2) {
            $totalPerWeek[$y][$w]['visits'] = 0;
            $totalPerWeek[$y][$w]['orders'] = 0;
        }
    }
    foreach ($totalPerDay as $y => $array1) {
        foreach ($array1 as $d => $array2) {
            $totalPerDay[$y][$d]['visits'] = 0;
            $totalPerDay[$y][$d]['orders'] = 0;
        }
    }

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";

    
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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/visitsTrend.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $days = $response['datas'];
    }
    else { */
        $sqlFilters2 = OpartStatTools::getFiltersForOpartSessionTable($filtersArray);
        $shopConstraints2 = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
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
                    ".$shopConstraints2."
                    " . $sqlFilters2 . "
                GROUP BY
                    DATE_FORMAT(createdAt,'%Y-%m-%d')
                ";

        $days = OpartStatTools::executeSessionsSelect($sql);
    /* } */

    foreach ($days as $day) {
        $dayTotal = (float)$day['nbVisite'];
        $totalVisits = (isset($totalVisits)) ? $totalVisits + $dayTotal : $dayTotal;

        $y = (int)date("y", strtotime($day['createdAt']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($day['createdAt']));
        $m = (int)date("n", strtotime($day['createdAt']));
        $w = (int)date("W", strtotime($day['createdAt']));
        $d = (int)date("z", strtotime($day['createdAt']));

        (float)$totalPerYear[$y][0]['visits'] = (isset($totalPerYear[$y][0]['visits'])) ? $totalPerYear[$y][0]['visits'] + $dayTotal : $dayTotal;
        (float)$totalPerMonth[$y][$m]['visits'] = (isset($totalPerMonth[$y][$m]['visits'])) ? $totalPerMonth[$y][$m]['visits'] + $dayTotal : $dayTotal;
        (float)$totalPerWeek[$yForWeek][$w]['visits'] = (isset($totalPerWeek[$yForWeek][$w]['visits'])) ? $totalPerWeek[$yForWeek][$w]['visits'] + $dayTotal : $dayTotal;
        (float)$totalPerDay[$y][$d]['visits'] = (isset($totalPerDay[$y][$d]['visits'])) ? $totalPerDay[$y][$d]['visits'] + $dayTotal : $dayTotal;
    }

    //get orders
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);

    //important !
    //here we do not use date_add from order_history table because what we need is the moment where the order was created !
    //if the customer visits the website one day, create an order the same day and the order is validated 2 days later, this still is a conversion !
    $sql =  "SELECT SQL_NO_CACHE 
                COUNT(DISTINCT(orders.id_order)) as nbOrder,
                orders.`".bqSQL($dateColumn)."` as date_add
            FROM 
                `" . _DB_PREFIX_ . "orders` orders               
            LEFT JOIN
                " . _DB_PREFIX_ . "order_detail order_detail
            ON
                orders.id_order = order_detail.id_order
            ".$excludeFreeOrder."
            WHERE 
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
            AND
                " . $shopConstraints . "
            AND 
                " . $orderStateCondition . "
            ".$excludeFreeOrder."
            ".$sqlFilters."
            GROUP BY 
                DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d')";

    $days = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);

    foreach ($days as $day) {
        $dayTotal = (float)$day['nbOrder'];
        $totalOrders = (isset($totalOrders)) ? $totalOrders + $dayTotal : $dayTotal;

        $y = (int)date("y", strtotime($day['date_add']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($day['date_add']));
        $m = (int)date("n", strtotime($day['date_add']));
        $w = (int)date("W", strtotime($day['date_add']));
        $d = (int)date("z", strtotime($day['date_add']));

        (float)$totalPerYear[$y][0]['orders'] = (isset($totalPerYear[$y][0]['orders'])) ? $totalPerYear[$y][0]['orders'] + $dayTotal : $dayTotal;
        (float)$totalPerMonth[$y][$m]['orders'] = (isset($totalPerMonth[$y][$m]['orders'])) ? $totalPerMonth[$y][$m]['orders'] + $dayTotal : $dayTotal;
        (float)$totalPerWeek[$yForWeek][$w]['orders'] = (isset($totalPerWeek[$yForWeek][$w]['orders'])) ? $totalPerWeek[$yForWeek][$w]['orders'] + $dayTotal : $dayTotal;
        (float)$totalPerDay[$y][$d]['orders'] = (isset($totalPerDay[$y][$d]['orders'])) ? $totalPerDay[$y][$d]['orders'] + $dayTotal : $dayTotal;
    }

    foreach ($totalPerYear as $y => $array1)
        $totalPerYear[$y][0]['value'] = (empty($array1[0]['visits']) || empty($array1[0]['orders'])) ? 0 : $array1[0]['orders'] / $array1[0]['visits'] * 100;

    foreach ($totalPerMonth as $y => $array1)
        foreach ($array1 as $m => $array2)
            $totalPerMonth[$y][$m]['value'] = (empty($array2['visits']) || empty($array2['orders'])) ? 0 : $array2['orders'] / $array2['visits'] * 100;


    foreach ($totalPerWeek as $y => $array1)
        foreach ($array1 as $w => $array2)
            $totalPerWeek[$y][$w]['value'] = (empty($array2['visits']) || empty($array2['orders'])) ? 0 : $array2['orders'] / $array2['visits'] * 100;

    foreach ($totalPerDay as $y => $array1)
        foreach ($array1 as $d => $array2)
            $totalPerDay[$y][$d]['value'] = (empty($array2['visits']) || empty($array2['orders'])) ? 0 : $array2['orders'] / $array2['visits'] * 100;

    $totalGlobal = (empty($totalOrders) || empty($totalVisits)) ? 0 : $totalOrders / $totalVisits * 100;

    ksort($totalPerYear);
    ksort($totalPerMonth);
    ksort($totalPerWeek);
    ksort($totalPerDay);

    $result['value'] = [
        'globalValue' => $totalGlobal,
        'perYear' => $totalPerYear,
        'perMonth' => $totalPerMonth,
        'perWeek' => $totalPerWeek,
        'perDay' => $totalPerDay
    ];
    $result['conf'] = [
        'globalValueFormat' => 'percent'
    ];
    return $result;
}
