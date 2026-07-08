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

function getCustomerLifetimeValue($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('CustomerLifetimeValue', $vars);
}

function getCustomerLifetimeValueValues($dateFrom, $dateTo, $filtersArray, $vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $interval = Configuration::get('OPARTSTAT_INACTIV_CUSTOMER_DAYS');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $today = date("Y-m-d H:i:s");
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins1 = OpartStatTools::getJoins($filtersArray,['customer']);
    $sqlJoins2 = OpartStatTools::getJoins($filtersArray);
    $sqlJoins3 = OpartStatTools::getJoins($filtersArray,['order_detail']);

    $fields = OpartStatTools::getTotalRevenueFields();
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE (
                SELECT
                    AVG(t.nbOrder)
                FROM
                    (
                        SELECT
                            COUNT(orders.id_order) AS nbOrder
                        FROM
                            " . _DB_PREFIX_ . "orders orders
                        ".$sqlJoins1."  
                        JOIN 
                            " . _DB_PREFIX_ . "customer customer
                        ON
                            customer.id_customer = orders.id_customer
                        AND
                            customer.id_shop = orders.id_shop
                        WHERE
                            orders.`".bqSQL($dateColumn)."` < DATE_SUB('" . pSQL($today) . "', INTERVAL " . (int)$interval . " DAY)
                        AND
                            " . $orderStateCondition . "
                            ".$excludeFreeOrder."
                            " . $sqlFilters . "
                        AND NOT EXISTS (
                            SELECT  
                                1 
                            FROM 
                                `" . _DB_PREFIX_ . "orders` orders
                            ".$sqlJoins2." 
                            WHERE 
                                orders.id_customer = customer.id_customer
                            AND
                                ".$shopConstraints."
                            AND
                                orders.`".bqSQL($dateColumn)."` > DATE_SUB('" . pSQL($today) . "', INTERVAL " . (int)$interval . " DAY)
                            AND 
                                " . $orderStateCondition . "            
                                ".$excludeFreeOrder."
                                " . $sqlFilters . "
                        )
                        GROUP BY
                            customer.id_customer
                    ) t
                ) * (
                    SELECT  
                AVG(orderRevenu) 
            FROM 
                (
                    SELECT  
                        orders.id_order, 
                        (".$fields.") as 'orderRevenu'
                    FROM 
                        " . _DB_PREFIX_ . "orders orders 
                    LEFT JOIN 
                        " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                    ON
                        orders.id_order = order_cart_rule.id_order
                    AND
                        order_cart_rule.free_shipping = 1                        
                    INNER JOIN 
                        " . _DB_PREFIX_ . "order_detail order_detail 
                    ON 
                        orders.id_order = order_detail.id_order    
                    ".$sqlJoins3."                  
                    WHERE 
                        ".$shopConstraints." 
                    AND 
                        orders.`".bqSQL($dateColumn)."` < DATE_SUB('" . pSQL($today) . "', INTERVAL " . (int)$interval . " DAY)   
                    AND 
                        " . $orderStateCondition . "
                        ".$excludeFreeOrder."
                        " . $sqlFilters . "
                    GROUP BY 
                        orders.id_order
                ) as a
            )";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'price');
}
