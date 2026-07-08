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

function getAverageRevenuesPerDayTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('AverageRevenuesPerDayTrend', $vars, 'price');
}

function getAverageRevenuesPerDayTrendValues($dateFrom, $dateTo, $filtersArray, $vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $fields = opartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);
    
    $sql = "SELECT
                    SUM(totalOrder) as total,
                    DATE_FORMAT(`" .bqSQL($dateColumn)."`, '%Y-%m-%d') as date_add
            FROM (
                SELECT   
                    (".$fields.") as totalOrder, 
                    orders.`".bqSQL($dateColumn)."` as "  .($dateColumn). "
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
                    ".$shopConstraints."
                AND
                    orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                AND 
                    orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                AND 
                    " . $orderStateCondition . "
                ".$excludeFreeOrder."
                " .$sqlFilters."   
                ".$groupBy." 
                ORDER BY 
                    orders.`".bqSQL($dateColumn)."` ASC
            ) t
            GROUP BY
                DATE_FORMAT(`" .bqSQL($dateColumn)."`, '%Y-%m-%d')
            ORDER BY
                DATE_ADD ASC
            ";

    $totalGlobal = 0;

    $r = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom,$dateTo,$filtersArray,$vars);

    foreach($r['y'] as $y=>$array1) {
        $r['y'][$y][0]['total'] = 0;
        $r['y'][$y][0]['nbDay'] = 0;
    }

    foreach($r['m'] as $y=>$array1) {
        foreach($array1 as $m=>$array2) {
            $r['m'][$y][$m]['total'] = 0;
            $r['m'][$y][$m]['nbDay'] = 0;
        }
    }
                
    foreach($r['w'] as $y=>$array1) {
        foreach($array1 as $w=>$array2) {
            $r['w'][$y][$w]['total'] = 0;
            $r['w'][$y][$w]['nbDay'] = 0;
        }
    }
    $nbayGlobal = 0;
    foreach($r['d'] as $y=>$array1) {        
        foreach($array1 as $d=>$array2) {
            $r['d'][$y][$d]['total'] = 0; 
            $r['d'][$y][$d]['nbDay'] = 1;

            $dayDate = new DateTime($array2['date']);
            $r['y'][$y][0]['nbDay'] += 1;
            $r['m'][$y][(int)$dayDate->format('m')]['nbDay'] += 1;
            if(key_exists($dayDate->format('W'),$r['w'][$y]))
                $r['w'][$y][(int)$dayDate->format('W')]['nbDay'] += 1;

            $nbayGlobal += 1;
        }
    }

    $orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    foreach ($orders as $order) {
        //$orderTotal = (float)$order['total_paid_tax_excl'];
        $orderTotal = (float)$order['total'];

        $totalGlobal = (isset($totalGlobal)) ? $totalGlobal + $orderTotal : $orderTotal;

        $y = (int)date("y", strtotime($order['date_add']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($order['date_add']));
        $m = (int)date("m", strtotime($order['date_add']));
        $w = (int)date("W", strtotime($order['date_add']));
        $d = (int)date("z", strtotime($order['date_add']));
        
        (float)$r['y'][$y][0]['total'] = (isset($r['y'][$y][0]['total']))?$r['y'][$y][0]['total']+$orderTotal:$orderTotal;
        //(int)$r['y'][$y][0]['nbOrder'] = (isset($r['y'][$y][0]['nbDay']))?$r['y'][$y][0]['nbOrder']+1:1;

        (float)$r['m'][$y][$m]['total'] = (isset($r['m'][$y][$m]['total']))?$r['m'][$y][$m]['total']+$orderTotal:$orderTotal;
        //(int)$r['m'][$y][$m]['nbOrder'] = (isset($r['m'][$y][$m]['nbOrder']))?$r['m'][$y][$m]['nbOrder']+1:1;

        (float)$r['w'][$yForWeek][$w]['total'] = (isset($r['w'][$yForWeek][$w]['total']))?$r['w'][$yForWeek][$w]['total']+$orderTotal:$orderTotal;
        //(int)$r['w'][$yForWeek][$w]['nbOrder'] = (isset($r['w'][$yForWeek][$w]['nbOrder']))?$r['w'][$yForWeek][$w]['nbOrder']+1:1;

        (float)$r['d'][$y][$d]['total'] = (isset($r['d'][$y][$d]['total']))?$r['d'][$y][$d]['total']+$orderTotal:$orderTotal;
        //(int)$r['d'][$y][$d]['nbOrder'] = (isset($r['d'][$y][$d]['nbOrder']))?$r['d'][$y][$d]['nbOrder']+1:1;
    }

    //calc average
    foreach ($r as $key => $array1) {
        foreach ($array1 as $y => $array2) {
            foreach ($array2 as $period => $vals) {
                if($vals['nbDay']==0)
                    $average = 0;
                else
                    $average = $vals['total'] / $vals['nbDay'];

                $r[$key][$y][$period]['value'] = $average;
            }
        }
    }

    ksort($r['y']);
    ksort($r['m']);
    ksort($r['w']);
    ksort($r['d']);

    if($nbayGlobal==0)
        $nbayGlobal=1;
        
    $globalAverage = $totalGlobal / $nbayGlobal;

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
