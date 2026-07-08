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

function getOpartdevisAverageAmountTrend($vars)
{
    return OpartStatTools::getMetricResult('OpartdevisAverageAmountTrend', $vars);
}

function getOpartdevisAverageAmountTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';
    $result['conf']['total'] = '';
    $fields = opartStatTools::getTotalRevenueFields();
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT 
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
            INNER JOIN
                `" . _DB_PREFIX_ . "opartdevis` opartdevis   
            ON
                orders.id_order = opartdevis.id_order  
            WHERE 
                " . $shopConstraints . "
            AND
                orders." . pSQL($dateColumn) . " >= '" . pSQL($dateFrom) . "'
            AND 
                orders." . pSQL($dateColumn) . " <= '" . pSQL($dateTo) . "'   
            AND 
                " . $orderStateCondition . "
            GROUP BY 
                orders.id_order
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` asC";

    $tmpQuotes = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    $quotes = [];
    foreach ($tmpQuotes as $data) {
        if (array_key_exists($data['id_order'], $quotes))
            $quotes[$data['id_order']]['total'] += $data['total'];
        else
            $quotes[$data['id_order']] = $data;
    }

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom, $dateTo, $quotes, 'date_add', 'total');

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
