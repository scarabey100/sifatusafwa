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

function getNumberOfRefundsTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('NumberOfRefundsTrend',$vars,'',false);
}

function getNumberOfRefundsTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_REFUNDED_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]); 
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
                COUNT(DISTINCT orders.id_order) AS total, 
                orders.`".bqSQL($dateColumn)."` as date_add
            FROM 
                `" . _DB_PREFIX_ . "orders` orders
            ".$sqlJoins."            
            WHERE 
                ".$shopConstraints."
            AND 
                " . $orderStateCondition . "  
                ".$excludeFreeOrder."  
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'            
            " .$sqlFilters."   
            GROUP BY 
                DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d')
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC";
    
    $orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArray($dateFrom,$dateTo,$orders,'date_add','total');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    /* $totalGlobal = 0;

    $totalPerArrays = OpartStatTools::createTotalPerArray($dateFrom,$dateTo,$filtersArray,$vars);
    $totalPerMonth = $totalPerArrays['totalPerMonth'];
    $totalPerWeek = $totalPerArrays['totalPerWeek'];
    $totalPerDay = $totalPerArrays['totalPerDay'];

    
    foreach ($orders as $order) {
        $orderTotal = (float)$order['total'];
        $totalGlobal = (isset($totalGlobal)) ? $totalGlobal + $orderTotal : $orderTotal;

        $y = (int)date("y", strtotime($order['date_add']));
        $yForWeek = OpartStatTools::getLastWeekOfTheYear(new DateTime($order['date_add']));
        $m = (int)date("n", strtotime($order['date_add']));
        $w = (int)date("W", strtotime($order['date_add']));
        $d = (int)date("z", strtotime($order['date_add']));

        (float)$totalPerMonth[$y][$m]['value'] = (isset($totalPerMonth[$y][$m]['value'])) ? $totalPerMonth[$y][$m]['value'] + $orderTotal : $orderTotal;
        (float)$totalPerWeek[$yForWeek][$w]['value'] = (isset($totalPerWeek[$yForWeek][$w]['value'])) ? $totalPerWeek[$yForWeek][$w]['value'] + $orderTotal : $orderTotal;
        (float)$totalPerDay[$y][$d]['value'] = (isset($totalPerDay[$y][$d]['value'])) ? $totalPerDay[$y][$d]['value'] + $orderTotal : $orderTotal;
    }

    ksort($totalPerMonth);
    ksort($totalPerWeek);
    ksort($totalPerDay);

    $result['value'] = [
        'globalValue' => $totalGlobal,
        'perMonth' => $totalPerMonth,
        'perWeek' => $totalPerWeek,
        'perDay' => $totalPerDay
    ]; */
    $result['conf'] = [
        'globalValueFormat' => ''
    ];
    return $result;
}