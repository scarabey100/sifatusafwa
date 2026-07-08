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

function getRefundRateTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('RefundRateTrend',$vars,'percent',false);
}

function getRefundRateTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateConditionRefund = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_REFUNDED_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]); 
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql ="SELECT SQL_NO_CACHE 
                nbOrder.date_add,
                nbOrder.count AS nbOrder,
                nbRefund.count AS nbRefund
            FROM 
            (
                SELECT  
                    COUNT(DISTINCT orders.id_order) as count, 
                    DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d') as date_add
                FROM
                    "._DB_PREFIX_."orders orders  
                ".$sqlJoins."              
                WHERE
                    ".$shopConstraints."
                ".$excludeFreeOrder."            
                " .$sqlFilters."
                AND
                    orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                AND 
                    orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                GROUP BY 
                    DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d')
            ) as nbOrder        
            LEFT JOIN 
            (
                SELECT  
                    COUNT(DISTINCT orders.id_order) as count, 
                    DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d') as date_add
                FROM
                    "._DB_PREFIX_."orders orders
                ".$sqlJoins."
                WHERE
                    ".$shopConstraints." 
                AND 
                    ".$orderStateConditionRefund."
                ".$excludeFreeOrder."            
                " .$sqlFilters."
                AND
                    orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                AND 
                    orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                GROUP BY 
                    DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d')
            ) as nbRefund
            ON 
                nbRefund.date_add = nbOrder.date_add
        ";

    $r = OpartStatTools::getAllDatesBeetweenTwoDate($dateFrom,$dateTo,$filtersArray,$vars);

    foreach($r['y'] as $y=>$array1) {
        $r['y'][$y][0]['nbRefund'] = 0;
        $r['y'][$y][0]['nbOrder'] = 0;        
    }

    foreach($r['m'] as $y=>$array1) {
        foreach($array1 as $m=>$array2) {
            $r['m'][$y][$m]['nbRefund'] = 0;
            $r['m'][$y][$m]['nbOrder'] = 0;
        }
    }
                
    foreach($r['w'] as $y=>$array1) {
        foreach($array1 as $w=>$array2) {
            $r['w'][$y][$w]['nbRefund'] = 0;
            $r['w'][$y][$w]['nbOrder'] = 0;
        }
    }

    foreach($r['d'] as $y=>$array1) {
        foreach($array1 as $d=>$array2) {
            $r['d'][$y][$d]['nbRefund'] = 0; 
            $r['d'][$y][$d]['nbOrder'] = 0;
        }
    }
    $nbOrderGlobal=0;
    $nbRefundGlobal = 0;
    $orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    foreach ($orders as $order) {
        /* $orderTotal = (float)$order['total_paid_tax_excl']; */
        $nbRefund = (float)$order['nbRefund'];
        $nbOrder = (float)$order['nbOrder'];

        $nbRefundGlobal = (isset($nbRefundGlobal)) ? $nbRefundGlobal + $nbRefund : $nbRefund;
        $nbOrderGlobal = (isset($nbOrderGlobal)) ? $nbOrderGlobal + $nbOrder : $nbOrder;

        $y = (int)date("y", strtotime($order['date_add']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($order['date_add']));
        $m = (int)date("m", strtotime($order['date_add']));
        $w = (int)date("W", strtotime($order['date_add']));
        $d = (int)date("z", strtotime($order['date_add']));
        
        (float)$r['y'][$y][0]['nbRefund'] = (isset($r['y'][$y][0]['nbRefund']))?$r['y'][$y][0]['nbRefund']+$nbRefund:$nbRefund;
        (int)$r['y'][$y][0]['nbOrder'] = (isset($r['y'][$y][0]['nbOrder']))?$r['y'][$y][0]['nbOrder']+$nbOrder:$nbOrder;

        (float)$r['m'][$y][$m]['nbRefund'] = (isset($r['m'][$y][$m]['nbRefund']))?$r['m'][$y][$m]['nbRefund']+$nbRefund:$nbRefund;
        (int)$r['m'][$y][$m]['nbOrder'] = (isset($r['m'][$y][$m]['nbOrder']))?$r['m'][$y][$m]['nbOrder']+$nbOrder:$nbOrder;

        (float)$r['w'][$yForWeek][$w]['nbRefund'] = (isset($r['w'][$yForWeek][$w]['nbRefund']))?$r['w'][$yForWeek][$w]['nbRefund']+$nbRefund:$nbRefund;
        (int)$r['w'][$yForWeek][$w]['nbOrder'] = (isset($r['w'][$yForWeek][$w]['nbOrder']))?$r['w'][$yForWeek][$w]['nbOrder']+$nbOrder:$nbOrder;

        (float)$r['d'][$y][$d]['nbRefund'] = (isset($r['d'][$y][$d]['nbRefund']))?$r['d'][$y][$d]['nbRefund']+$nbRefund:$nbRefund;
        (int)$r['d'][$y][$d]['nbOrder'] = (isset($r['d'][$y][$d]['nbOrder']))?$r['d'][$y][$d]['nbOrder']+$nbOrder:$nbOrder;
    }

    //calc average
    foreach ($r as $key => $array1) {
        foreach ($array1 as $y => $array2) {
            foreach ($array2 as $period => $vals) {
                if($vals['nbOrder']==0)
                    $average = 0;
                else
                    $average = $vals['nbRefund'] / $vals['nbOrder'];

                $r[$key][$y][$period]['value'] = $average*100;
            }
        }
    }

    ksort($r['y']);
    ksort($r['m']);
    ksort($r['w']);
    ksort($r['d']);

    if($nbOrderGlobal==0)
        $nbOrderGlobal=1;
        
    $globalAverage = $nbRefundGlobal / $nbOrderGlobal;

    $result['value'] = [
        'globalValue' => $globalAverage*100,
        'perYear' => $r['y'],
        'perMonth' => $r['m'],
        'perWeek' => $r['w'],
        'perDay' => $r['d']
    ];
    $result['conf'] = [
        'globalValueFormat' => 'percent'
    ];

    return $result;
}