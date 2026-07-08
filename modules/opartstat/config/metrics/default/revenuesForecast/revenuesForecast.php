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

function getRevenuesForecast($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('RevenuesForecast', $vars);
}

function getRevenuesForecastValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);
    $fields = opartStatTools::getFields($filtersArray);
    //$fields = OpartStatTools::getRevenueFieldsForOrderDetailLine($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);

    $today = date('Y-m-d');
    $startDateA1 = date('Y-m-d', strtotime('-12 months', strtotime($today)));
    $startDateA2 = date('Y-m-d', strtotime('-24 months', strtotime($today)));

    $averageRevenuePerDay1 = getAverageRevenuesPerDay($startDateA1, $today, $fields, $sqlJoins, $shopConstraints, $dateColumn, $orderStateCondition, $excludeFreeOrder, $sqlFilters, $groupBy);
    $averageRevenuePerDay2 = getAverageRevenuesPerDay($startDateA2, $startDateA1, $fields, $sqlJoins, $shopConstraints, $dateColumn, $orderStateCondition, $excludeFreeOrder, $sqlFilters, $groupBy);
    
    if($averageRevenuePerDay2 == 0)
        $variationPercent = 0;
    else
        $variationPercent = (($averageRevenuePerDay1 - $averageRevenuePerDay2) / $averageRevenuePerDay2) * 100;

    $lastDayOfTheYearLastYear = date('Y-m-d', strtotime('last day of December last year'));
    $todayLastYear = date('Y-m-d', strtotime('-1 year', strtotime($today)));

    $totalEndOfTheYearLastYear = getRevenues($todayLastYear, $lastDayOfTheYearLastYear, $fields, $sqlJoins, $shopConstraints, $dateColumn, $orderStateCondition, $excludeFreeOrder, $sqlFilters, $groupBy);

    $revenueForecastRemainsDaysOfTheYear = $totalEndOfTheYearLastYear * (1 + $variationPercent / 100);
    
    $firstDayOfTheYear = date('Y-m-d', strtotime('first day of January this year'));
    $totalRevenueThisYear = getRevenues($firstDayOfTheYear, $today, $fields, $sqlJoins, $shopConstraints, $dateColumn, $orderStateCondition, $excludeFreeOrder, $sqlFilters, $groupBy);

    $totalRevenueForecast = $totalRevenueThisYear + $revenueForecastRemainsDaysOfTheYear;

    $result['value'] = $totalRevenueForecast;
    $result['conf'] = [
        'total' => 'price'
    ];

    $startDateA1 = OpartStatTools::mysqlToHumanDate($startDateA1, OpartStatTools::getDateFormat());
    $startDateA2 = OpartStatTools::mysqlToHumanDate($startDateA2, OpartStatTools::getDateFormat());
    $averageRevenuePerDay1 = OpartStatTools::formatPrice($averageRevenuePerDay1);
    $averageRevenuePerDay2 = OpartStatTools::formatPrice($averageRevenuePerDay2);
    $revenueForecastRemainsDaysOfTheYear = OpartStatTools::formatPrice($revenueForecastRemainsDaysOfTheYear);
    $totalRevenueForecast = OpartStatTools::formatPrice($totalRevenueForecast);
    $variationPercent = number_format($variationPercent,2)."%";

    $result['helpValues'] = [
        'averageRevenuePerDay1' => $averageRevenuePerDay1,
        'averageRevenuePerDay2' => $averageRevenuePerDay2,
        'startDateA1' => $startDateA1,
        'startDateA2' => $startDateA2,
        'variationPercent' => $variationPercent,
        'revenueForecastRemainsDaysOfTheYear' => $revenueForecastRemainsDaysOfTheYear,
        'totalRevenueForecast' => $totalRevenueForecast,
    ];
    return $result;
}

function getAverageRevenuesPerDay($dateFrom, $dateTo, $fields, $sqlJoins, $shopConstraints, $dateColumn, $orderStateCondition, $excludeFreeOrder, $sqlFilters, $groupBy)
{
    $sql = "SELECT
    (SUM(revenues)/(DATEDIFF( '" . pSQL($dateTo) . "','" . pSQL($dateFrom) . "')+1)) AS 'totalRevenues'
FROM
    (
        SELECT
            orders.id_order,
            (".$fields.") AS 'revenues'
        FROM
        " . _DB_PREFIX_ . "orders orders                        
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
        ".$excludeFreeOrder." 
        WHERE 
            " . $shopConstraints . "
        AND
            orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
        AND 
            orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
        AND 
            " . $orderStateCondition . "
        ".$excludeFreeOrder."
        ".$sqlFilters."   
        ".$groupBy."                       
    ) AS t
";
    $res = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($sql);
    if($res === null)
        $res = 0;
    return $res;
}

function getRevenues($dateFrom, $dateTo, $fields, $sqlJoins, $shopConstraints, $dateColumn, $orderStateCondition, $excludeFreeOrder, $sqlFilters, $groupBy) {
    $sql =  "SELECT SQL_NO_CACHE 
        SUM(total) as 'totalCA'
    FROM 
        (
            SELECT  
                orders.id_order, 
                (".$fields.") as 'total'
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
                " . $orderStateCondition . "
                ".$excludeFreeOrder."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
            ".$sqlFilters."
            ".$groupBy."
        ) as t";

        return Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue($sql);
}
