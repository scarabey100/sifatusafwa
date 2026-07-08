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

function getRevenuesPerVisitTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('RevenuesPerVisitTrend', $vars, 'price');
}

function getRevenuesPerVisitTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $shopConstraints2 = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
    $lastStatDate = opartSession::getLastStatDate();
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    if ($lastStatDate == false) {
        $result['value'] = 0;
        $result['conf'] = [
            'total' => '',
        ];
        return $result;
    }

    if ($dateFrom < $lastStatDate)
        $dateFrom = $lastStatDate;

    $totalVisits = 0;
    $totalRevenues = 0;

    $periodArrays = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom, $dateTo);
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

    $sqlSessionFilters = '';
    $sqlIn = "";
    $whereSession = "";
    if(is_array($filtersArray) && count($filtersArray) > 0) {
        foreach($filtersArray as $excludeInclude => $array) {
            foreach ($array as $filterName => $filterValue) {
                if(!array_key_exists('values', $filterValue)) 
                    continue;            

                if ($filterName == 'categories') {
                    $selectIn = 'product.id_category_default';
                    $whereSession = "AND opartstat_sessions.controllerName = 'CategoryController'";
                    $sqlSessionFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'opartstat_sessions','shopId');    
                }        

                if ($filterName == 'brands') {
                    $selectIn = 'product.id_manufacturer';
                    $whereSession = "AND opartstat_sessions.controllerName = 'ManufacturerController'";
                    $sqlSessionFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'opartstat_sessions','shopId'); 
                }

                if ($filterName == 'products') {
                    $selectIn = 'product.id_product';
                    $whereSession = "AND opartstat_sessions.controllerName = 'ProductController'";
                    $sqlSessionFilters .= OpartStatTools::getselectedItemsConstraints($filterName, $filterValue, $excludeInclude, false, 'opartstat_sessions','shopId');
                }
                $sqlJoins2 = OpartStatTools::getJoins($filtersArray);             
            }
        }
        $sqlIn = "AND opartstat_sessions.elementId IN (
                    SELECT SQL_NO_CACHE 
                        ".pSQL($selectIn)." 
                    FROM                               
                        `" . _DB_PREFIX_ . "orders` orders 
                    " .$sqlJoins2." 
                    WHERE 
                        ".pSQL($shopConstraints2)."
                    ".$sqlSessionFilters."     
                    AND
                        orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'                        
                )";

    }

    //get visits
    $sql =  "SELECT SQL_NO_CACHE 
                count(DISTINCT userIp) as nbVisite, 
                createdAt
            FROM 
                `" . _DB_PREFIX_ . "opartstat_sessions` opartstat_sessions
            WHERE 
                `createdAt` >= '" . pSQL($dateFrom) . "'
            AND 
                `createdAt` <= '" . pSQL($dateTo) . "'
            ".$whereSession."
            AND 
                ".$shopConstraints2."
            ".$sqlIn."
            GROUP BY 
                DATE_FORMAT(createdAt,'%Y-%m-%d')";

    $days = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
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

    //get revenues
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);   
    $fields = OpartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);

    $sql =  "SELECT SQL_NO_CACHE 
                ".$fields." as total,                
                orders.`".bqSQL($dateColumn)."` as date_add
            FROM 
                `" . _DB_PREFIX_ . "orders` orders            
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
            AND
                order_cart_rule.free_shipping = 1
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_detail order_detail 
            ON
                orders.id_order = order_detail.id_order
            ".$sqlJoins."
            WHERE 
                " . $shopConstraints . "
            AND 
                " . $orderStateCondition . "
                ".$excludeFreeOrder."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "' 
            ".$sqlFilters."                 
            ".$groupBy."
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC";

    //echo $sql;
    $days = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    foreach ($days as $day) {
        $dayTotal = (float)$day['total'];
        $totalRevenues = (isset($totalRevenues)) ? $totalRevenues + $dayTotal : $dayTotal;

        $y = (int)date("y", strtotime($day['date_add']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($day['date_add']));
        $m = (int)date("n", strtotime($day['date_add']));
        $w = (int)date("W", strtotime($day['date_add']));
        $d = (int)date("z", strtotime($day['date_add']));

        (float)$totalPerYear[$y][0]['total'] = (isset($totalPerYear[$y][0]['total'])) ? $totalPerYear[$y][0]['total'] + $dayTotal : $dayTotal;
        (float)$totalPerMonth[$y][$m]['total'] = (isset($totalPerMonth[$y][$m]['total'])) ? $totalPerMonth[$y][$m]['total'] + $dayTotal : $dayTotal;
        (float)$totalPerWeek[$yForWeek][$w]['total'] = (isset($totalPerWeek[$yForWeek][$w]['total'])) ? $totalPerWeek[$yForWeek][$w]['total'] + $dayTotal : $dayTotal;
        (float)$totalPerDay[$y][$d]['total'] = (isset($totalPerDay[$y][$d]['total'])) ? $totalPerDay[$y][$d]['total'] + $dayTotal : $dayTotal;
    }

    foreach ($totalPerYear as $y => $array1)
        $totalPerYear[$y][0]['value'] = (empty($array1[0]['visits']) || empty($array1[0]['total'])) ? 0 : $array1[0]['total'] / $array1[0]['visits'];

    foreach ($totalPerMonth as $y => $array1)
        foreach ($array1 as $m => $array2)
            $totalPerMonth[$y][$m]['value'] = (empty($array2['visits']) || empty($array2['total'])) ? 0 : $array2['total'] / $array2['visits'];


    foreach ($totalPerWeek as $y => $array1)
        foreach ($array1 as $w => $array2)
            $totalPerWeek[$y][$w]['value'] = (empty($array2['visits']) || empty($array2['total'])) ? 0 : $array2['total'] / $array2['visits'];

    foreach ($totalPerDay as $y => $array1)
        foreach ($array1 as $d => $array2)
            $totalPerDay[$y][$d]['value'] = (empty($array2['visits']) || empty($array2['total'])) ? 0 : $array2['total'] / $array2['visits'];

    $totalGlobal = (empty($totalRevenues) || empty($totalVisits)) ? 0 : $totalRevenues / $totalVisits;

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
        'globalValueFormat' => 'price'
    ];
    return $result;
}
