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

function getNumberOfQuantitiesSoldTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('NumberOfQuantitiesSoldTrend', $vars);
}

function getNumberOfQuantitiesSoldTrendValues($dateFrom, $dateTo,$filtersArray) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']); 
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* $sql = "SELECT SQL_NO_CACHE 
                SUM(order_detail.`product_quantity` - order_detail.`product_quantity_refunded`) AS total,
                orders.`".bqSQL($dateColumn)."` as date_add
            FROM 
                `" . _DB_PREFIX_ . "order_detail` order_detail         
            INNER JOIN 
                `" . _DB_PREFIX_ . "orders` orders 
            ON
                orders.id_order = order_detail.id_order
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
            ".$sqlFilters."               
            GROUP BY 
                DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d')
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC"; */

    $sql = "SELECT SQL_NO_CACHE 
                SUM(sub.`product_quantity` - sub.`product_quantity_refunded`) AS total,
                sub.`".bqSQL($dateColumn)."` as date_add
            FROM 
                (
                    SELECT  
                        order_detail.id_order_detail,
                        order_detail.`product_quantity`,
                        order_detail.`product_quantity_refunded`,
                        orders.`".bqSQL($dateColumn)."`
                    FROM
                        `" . _DB_PREFIX_ . "order_detail` order_detail         
                        INNER JOIN 
                            `" . _DB_PREFIX_ . "orders` orders 
                        ON
                            orders.id_order = order_detail.id_order
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
                        ".$sqlFilters."               
                        GROUP BY
                            order_detail.id_order_detail
                ) sub 
                GROUP BY
                    DATE_FORMAT(sub.`".bqSQL($dateColumn)."`,'%Y-%m-%d')
                ORDER BY 
                    sub.`".bqSQL($dateColumn)."` ASC";

    $quantitiesSold = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArray($dateFrom, $dateTo, $quantitiesSold, 'date_add', 'total');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => ''
    ];
    return $result;
}
