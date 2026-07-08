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

function getAverageRevenuesPerMonthTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('AverageRevenuesPerMonthTrend', $vars, 'price');
}

function getAverageRevenuesPerMonthTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);
    $fields = opartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);
    
    $sql = "SELECT
                    SUM(totalOrder) as total,
                    DATE_FORMAT(`".bqSQL($dateColumn)."`, '%Y-%m-%d') as date_add
            FROM (
                SELECT 
                    (".$fields.") as totalOrder, 
                    orders.`".bqSQL($dateColumn)."` as " .$dateColumn. "
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
                    orders." . pSQL($dateColumn) . " >= '" . pSQL($dateFrom) . "'
                AND 
                    orders." . pSQL($dateColumn) . " <= '" . pSQL($dateTo) . "'   
                AND 
                    " . $orderStateCondition . "
                ".$excludeFreeOrder."
                ".$sqlFilters."   
                ".$groupBy." 
                ORDER BY 
                    orders.`".bqSQL($dateColumn)."` asC
            ) t
            GROUP BY
                DATE_FORMAT(`".bqSQL($dateColumn)."`, '%Y-%m-%d')
            ORDER BY
                DATE_ADD ASC
            ";

    $totalGlobal = 0;

    $r = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom, $dateTo);

    foreach ($r['y'] as $y => $array1) {
        $r['y'][$y][0]['total'] = 0;
        $r['y'][$y][0]['nbMonth'] = 0;
    }

    $nbMonthGlobal = 0;

    foreach ($r['m'] as $y => $array1) {
        foreach ($array1 as $m => $array2) {
            $r['m'][$y][$m]['total'] = 0;
            $r['m'][$y][$m]['nbMonth'] = 1;

            if (!isset($r['y'][$y][0]['nbMonth']))
                $r['y'][$y][0]['nbMonth'] = 0;

            $r['y'][$y][0]['nbMonth'] += 1;

            $nbMonthGlobal += 1;
        }
    }

    $orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    foreach ($orders as $order) {
        $orderTotal = (float)$order['total'];
        $totalGlobal = (isset($totalGlobal)) ? $totalGlobal + $orderTotal : $orderTotal;

        $y = (int)date("y", strtotime($order['date_add']));
        $m = (int)date("m", strtotime($order['date_add']));

        (float)$r['y'][$y][0]['total'] = (isset($r['y'][$y][0]['total'])) ? $r['y'][$y][0]['total'] + $orderTotal : $orderTotal;
        (float)$r['m'][$y][$m]['total'] = (isset($r['m'][$y][$m]['total'])) ? $r['m'][$y][$m]['total'] + $orderTotal : $orderTotal;
    }

    foreach ($r['d'] as $y => $array1) {
        foreach ($array1 as $d => $array2) {
            $date = DateTime::createFromFormat('Y-z', '20' . $y . '-' . $d);
            $m = (int)$date->format('m');
            if (!isset($r['m'][$y][$m]))
                $r['m'][$y][$m]['total'] = 0;

            (float)$r['d'][$y][$d]['total'] = $r['m'][$y][$m]['total'];
            $r['d'][$y][$d]['nbMonth'] = 1;
        }
    }

    foreach ($r['w'] as $y => $array1) {
        foreach ($array1 as $w => $array2) {
            $date = new DateTime();
            $date = $date->setISODate($y, $w);
            $m = (int)$date->format('m');

            /* if(!isset($r['m'][$y][$m])) 
                $r['m'][$y][$m]['total'] = 0; */
            if (!isset($r['m'][$y][$m])) {
                $r['m'][$y][$m]['total'] = 0;
                $firstDayDate = '20' . $y . '-' . $m . '-01';//si chevauchement sur deux année sans commande.
                $r['m'][$y][$m]['date'] = $firstDayDate;
            }

            (float)$r['w'][$y][$w]['total'] = $r['m'][$y][$m]['total'];
            $r['w'][$y][$w]['nbMonth'] = 1;
        }
    }

    //calc average
    foreach ($r as $key => $array1) {
        foreach ($array1 as $y => $array2) {
            foreach ($array2 as $period => $vals) {
                if (!isset($vals['nbMonth']) || $vals['nbMonth'] == 0)
                    $average = 0;
                else
                    $average = $vals['total'] / $vals['nbMonth'];

                $r[$key][$y][$period]['value'] = $average;
            }
        }
    }

    ksort($r['y']);
    ksort($r['m']);
    ksort($r['w']);
    ksort($r['d']);

    if ($nbMonthGlobal == 0)
        $nbMonthGlobal = 1;

    $globalAverage = $totalGlobal / $nbMonthGlobal;

    $result['value'] = [
        'globalValue' => $globalAverage,
        'perYear' => $r['y'],
        'perMonth' => $r['m'],
        'perWeek' => $r['w'],
        'perDay' => $r['d']
    ];
    $result['conf'] = [
        'globalValueFormat' => 'price'
    ];

    return $result;
}
