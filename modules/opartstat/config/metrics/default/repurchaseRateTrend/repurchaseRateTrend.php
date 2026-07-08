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

function getRepurchaseRateTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('RepurchaseRateTrend',$vars);
}

function getRepurchaseRateTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition1 = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER','orders');
    $excludeFreeOrder1 = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);   

    $sql1 = "SELECT
                SUM(repurchase) AS total,
                sub1.dayOfOrder AS date_add,
                RIGHT(YEAR(sub1.dayOfOrder), 2) AS y,
                MONTH(sub1.dayOfOrder) AS m,
                WEEK(sub1.dayOfOrder, 3) AS w,
                DAYOFYEAR(sub1.dayOfOrder) AS d
            FROM
                (
                SELECT
                    orders.id_customer, 
                    COUNT(orders.id_customer)-1 as repurchase,
                    DATE(orders.`".bqSQL($dateColumn)."`) AS dayOfOrder       
                FROM
                    `" . _DB_PREFIX_ . "orders` orders                   
               ".$sqlJoins."
                WHERE
                    " .$orderStateCondition1. "
                    ".pSQL($excludeFreeOrder1)."
                AND
                    ".$shopConstraints." 
                AND
                    orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                AND 
                    orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'  
                ".$sqlFilters." 
                GROUP BY 
                    orders.id_customer 
                HAVING 
                    COUNT(orders.id_customer) > 1
            ) AS sub1
            GROUP BY DATE(sub1.dayOfOrder)
            ";

//echo $sql1;      
    $repurchases = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql1);
    $totalRepurchases = 0;

    $repurchasesPerYear = [];
    $repurchasesPerMonth = [];
    $repurchasesPerWeek = [];
    $repurchasesPerDay = [];

    foreach($repurchases as $r) {
        $repurchasesPerYear[$r['y']][0] = (isset($repurchasesPerYear[$r['y']][0]))?$repurchasesPerYear[$r['y']][0]+$r['total']:$r['total'];
        $repurchasesPerMonth[$r['y']][$r['m']] = (isset($repurchasesPerMonth[$r['y']][$r['m']]))?$repurchasesPerMonth[$r['y']][$r['m']]+$r['total']:$r['total'];
        $repurchasesPerWeek[$r['y']][$r['w']] = (isset($repurchasesPerWeek[$r['y']][$r['w']]))?$repurchasesPerWeek[$r['y']][$r['w']]+$r['total']:$r['total'];
        $repurchasesPerDay[$r['y']][$r['d']] = (isset($repurchasesPerDay[$r['y']][$r['d']]))?$repurchasesPerDay[$r['y']][$r['d']]+$r['total']:$r['total'];

        $totalRepurchases = $totalRepurchases + $r['total'];
    }

    $sql2 = "SELECT
                COUNT(orders.id_order) AS total,
                DATE(orders.`".bqSQL($dateColumn)."`) AS date_add,
                RIGHT(YEAR(orders.`".bqSQL($dateColumn)."`), 2) AS y,
                MONTH(orders.`".bqSQL($dateColumn)."`) AS m,
                WEEK(orders.`".bqSQL($dateColumn)."`, 3) AS w,
                DAYOFYEAR(orders.`".bqSQL($dateColumn)."`) AS d
            FROM
                `" . _DB_PREFIX_ . "orders` orders
            ".$sqlJoins."            
            WHERE
                ".$shopConstraints."
            AND
                " .$orderStateCondition1. "
                ".pSQL($excludeFreeOrder1)."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "' 
            ".$sqlFilters."  
            GROUP BY 
                DATE(orders.`".bqSQL($dateColumn)."`)
            ";

    $orders = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql2);

    $totalOrders = 0;
    foreach($orders as $o) {
        $ordersPerYear[$o['y']][0] = (isset($ordersPerYear[$o['y']][0]))?$ordersPerYear[$o['y']][0]+$o['total']:$o['total'];
        $ordersPerMonth[$o['y']][$o['m']] = (isset($ordersPerMonth[$o['y']][$o['m']]))?$ordersPerMonth[$o['y']][$o['m']]+$o['total']:$o['total'];
        $ordersPerWeek[$o['y']][$o['w']] = (isset($ordersPerWeek[$o['y']][$o['w']]))?$ordersPerWeek[$o['y']][$o['w']]+$o['total']:$o['total'];
        $ordersPerDay[$o['y']][$o['d']] = (isset($ordersPerDay[$o['y']][$o['d']]))?$ordersPerDay[$o['y']][$o['d']]+$o['total']:$o['total'];       
        $totalOrders = $totalOrders+$o['total'];
    }

    $totalGlobal = 0;

    $totalPerArrays = OpartStatTools::createTotalPerArray($dateFrom,$dateTo,$filtersArray,$vars);
    $totalPerYear = $totalPerArrays['totalPerYear'];
    $totalPerMonth = $totalPerArrays['totalPerMonth'];
    $totalPerWeek = $totalPerArrays['totalPerWeek'];
    $totalPerDay = $totalPerArrays['totalPerDay'];

    foreach($repurchasesPerYear as $y => $ry) {
        foreach($ry as $m => $r) {
            if(!empty($repurchasesPerYear[$y][0]))
                $totalPerYear[$y][0]['value'] = ($repurchasesPerYear[$y][0] / $ordersPerYear[$y][0])*100;
        }
    }

    foreach($repurchasesPerMonth as $y => $ry) {
        foreach($ry as $m => $r) {
            if(!empty($repurchasesPerMonth[$y][$m]))
                $totalPerMonth[$y][$m]['value'] = ($repurchasesPerMonth[$y][$m] / $ordersPerMonth[$y][$m])*100;
        }
    }

    foreach($repurchasesPerWeek as $y => $rw) {
        foreach($rw as $w => $r) {
            if(!empty($repurchasesPerWeek[$y][$w]))
                $totalPerWeek[$y][$w]['value'] = ($repurchasesPerWeek[$y][$w] / $ordersPerWeek[$y][$w])*100;
        }
    }

    foreach($repurchasesPerDay as $y => $rd) {
        foreach($rd as $d => $r) {
            if(!empty($repurchasesPerDay[$y][$d]))
                $totalPerDay[$y][$d]['value'] = ($repurchasesPerDay[$y][$d] / $ordersPerDay[$y][$d])*100;
        }
    }

    if($totalOrders>0)
        $totalGlobal = ($totalRepurchases / $totalOrders)*100;

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