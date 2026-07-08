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

function getDiscountsAmountTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('DiscountsAmountTrend',$vars,'price');
}

function getDiscountsAmountTrendValues($dateFrom,$dateTo, $filtersArray) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);   
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
                ((total_discounts_tax_excl + orderDetails.total_reduction_amount)/ orders.conversion_rate) AS 'total',
                orders.`".bqSQL($dateColumn)."` as date_add
            FROM 
                `" . _DB_PREFIX_ . "orders` orders
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
                ".$shopConstraints."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            AND 
                " . $orderStateCondition . "
            ".$excludeFreeOrder."
            ".$sqlFilters."
            GROUP BY
                orders.id_order
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC";
            //IMPORTANT we don't exclude free order. Be cause and order could be free because of a discount

    $orders = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArray($dateFrom,$dateTo,$orders,'date_add','total');

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