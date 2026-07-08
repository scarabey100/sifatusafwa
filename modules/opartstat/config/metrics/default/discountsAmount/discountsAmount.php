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

function getDiscountsAmount($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('DiscountsAmount', $vars);
}

function getDiscountsAmountValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, []);
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    //IMPORTANT we don't exclude free order. Because an order could be free because of a discount

    $sql = "SELECT
                SUM(
                    (orders.total_discounts_tax_excl + orderDetails.total_reduction_amount) / orders.conversion_rate
                ) AS 'total'
            FROM
                " . _DB_PREFIX_ . "orders orders
            INNER JOIN
                (
                    SELECT
                        order_detail.id_order,
                        SUM(order_detail.reduction_amount_tax_excl) AS 'total_reduction_amount'
                    FROM
                        " . _DB_PREFIX_ . "order_detail order_detail
                    GROUP BY
                        order_detail.id_order
                ) orderDetails
            ON
                orderDetails.id_order = orders.id_order
            ".$sqlJoins."
            WHERE 
                " . $shopConstraints . "
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            AND 
                " . $orderStateCondition . "
            ".$sqlFilters."       
            ";

    return OpartStatTools::getSingleNumberJsonResult($sql, $dateTo, $useCache, 'price');
}
