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

function getAverageRevenuesPerOrderTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('AverageRevenuesPerOrderTrend', $vars, 'price');
}

function getAverageRevenuesPerOrderTrendValues($dateFrom, $dateTo, $filtersArray, $vars) {
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
    
    $sql = "SELECT SQL_NO_CACHE  
                (".$fields.") as total, 
                orders.`".bqSQL($dateColumn)."` as date_add,
                orders.id_order as id_order
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
                orders.`".bqSQL($dateColumn)."` ASC";

    $tmpOrders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    $orders = [];
    foreach($tmpOrders as $data) {
        if(array_key_exists($data['id_order'],$orders))
            $orders[$data['id_order']]['total'] += $data['total'];
        else
            $orders[$data['id_order']] = $data;
    }

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom, $dateTo, $orders, 'date_add', 'total');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => 'price'
    ];
    return $result;
}
