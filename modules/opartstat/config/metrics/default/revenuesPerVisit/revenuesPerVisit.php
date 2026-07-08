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

function getRevenuesPerVisit($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('RevenuesPerVisit', $vars);
}

function getRevenuesPerVisitValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => 'price',
    ];

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);
    $fields = OpartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
     $sessionsTable = OpartStatTools::getSessionsTableName();

    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    /* if (Configuration::get('OPARTSTAT_USE_SAAS')) {
        $shops = opartStatTools::getShops();        
        $datas = [
            'filtersArray' => $filtersArray,
            'shops' => $shops,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo
        ];

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/visits.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $visits = $response['datas'];
    } else { */
        $sqlFilters1 = OpartStatTools::getFiltersForOpartSessionTable($filtersArray);
        $shopConstraints1 = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
        $lastStatDate = opartSession::getLastStatDate();
        if ($lastStatDate == false)
            return $result;

        if ($dateFrom < $lastStatDate)
            $dateFrom = $lastStatDate;

        $sql1 = "SELECT SQL_NO_CACHE 
            COUNT(*) AS total
            FROM (
                    SELECT
                        MIN(createdAt) AS first_visit
                    FROM
                        `" . pSQL($sessionsTable) . "` opartstat_sessions
                    WHERE
                        `createdAt` BETWEEN '" . pSQL($dateFrom) . "' AND '" . pSQL($dateTo) . "'
                    AND
                        ".$shopConstraints1."
                        " . $sqlFilters1 . "
                    GROUP BY
                        userIp,
                        DATE(createdAt)
                ) AS subquery";


        $visits = OpartStatTools::executeSessionsSelect($sql1);
    /* } */

    $sqlFilters2 = OpartStatTools::getFilters($filtersArray);
    $shopConstraints2 = OpartStatTools::getShopConstraints();

    $sql2 = "SELECT SQL_NO_CACHE 
                SUM(revenues)
            FROM (
                SELECT  
                    (".$fields.") as revenues
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
                    ".$shopConstraints2."
                AND 
                    " . $orderStateCondition . "
                AND
                    orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                AND 
                    orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'                       
                " . $sqlFilters2 . "                    
                ".$groupBy."
            ) as sub";

    $revenues = OpartStatTools::getValueFromCacheIfExists($sql2, $dateTo, $useCache, true);

    if ($visits == 0)
        $result['value'] = 0;
    else
        $result['value'] = $revenues / $visits;

    return $result;
}
