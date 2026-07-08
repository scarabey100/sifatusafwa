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

function getMarginRate($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('MarginRate', $vars, '');
}

function getMarginRateValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');

    $sqlFilters = OpartStatTools::getFilters($filtersArray);

    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail'], [], true);

    //$profitsFields = opartStatTools::getFields($filtersArray, 'profits',1);
    $profitsFields = opartStatTools::getProfitFieldsForOrderDetailLine(1);

    //$costsFields = OpartStatTools::getCostsFields(OpartStatTools::needDetailLine($filtersArray));
    $costsFields = OpartStatTools::getCostsFields(true);

    //$groupBy = opartStatTools::getGroupBy($filtersArray,true);
    $groupBy = opartStatTools::getGroupBy($filtersArray, true, true);

    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =   "SELECT SQL_NO_CACHE (
                    SELECT  
                        SUM(orderMargin) as 'totalMargin'
                    FROM 
                        (
                            SELECT  
                                orders.id_order, 
                                (".$profitsFields.") as 'orderMargin'
                            FROM 
                                " . _DB_PREFIX_ . "orders orders                     
                            INNER JOIN 
                                " . _DB_PREFIX_ . "order_detail order_detail 
                            ON 
                                orders.id_order = order_detail.id_order                     
                            LEFT JOIN 
                                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                            ON
                                orders.id_order = order_cart_rule.id_order
                            AND
                                order_cart_rule.free_shipping = 1
                            ".$sqlJoins."
                            WHERE 
                                ".$shopConstraints."
                            AND
                                (
                                        order_detail.purchase_supplier_price IS NOT NULL 
                                    OR 
                                        order_detail.original_wholesale_price IS NOT NULL
                                )
                            AND 
                                ".$orderStateCondition."
                                ".$excludeFreeOrder."
                            AND
                                orders.`" . pSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
                            AND 
                                orders.`" . pSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
                            ".$sqlFilters."
                            ".$groupBy."
                        ) as m
                    ) / (
                        SELECT  
                            SUM(costs) as 'TotalCosts'
                        FROM 
                            (
                                SELECT  
                                    ".$costsFields." as costs
                                FROM 
                                    " . _DB_PREFIX_ . "orders orders                     
                                INNER JOIN 
                                    " . _DB_PREFIX_ . "order_detail order_detail 
                                ON 
                                    orders.id_order = order_detail.id_order                     
                                LEFT JOIN 
                                    " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                                ON
                                    orders.id_order = order_cart_rule.id_order
                                AND
                                    order_cart_rule.free_shipping = 1
                                ".$sqlJoins."
                                WHERE 
                                    ".$shopConstraints."
                                AND
                                    (
                                            order_detail.purchase_supplier_price IS NOT NULL 
                                        OR 
                                            order_detail.original_wholesale_price IS NOT NULL
                                    )
                                AND 
                                    ".$orderStateCondition."
                                    ".$excludeFreeOrder."
                                AND
                                    orders.`" . pSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
                                AND 
                                    orders.`" . pSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
                                ".$sqlFilters."
                                ".$groupBy."
                            ) as c
                    )*100 as marginRate";

    return OpartStatTools::getSingleNumberJsonResult($sql, $dateTo, $useCache, 'percent');
}
