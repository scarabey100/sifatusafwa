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

function getConversionRate($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('ConversionRate',$vars);
}

function getConversionRateValues($dateFrom, $dateTo, $filtersArray, $vars) {
    $result['value'] = 0;
    $result['conf'] = [
        'total' => 'percent',
    ];

    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray);    
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql1 =  "SELECT SQL_NO_CACHE 
                COUNT(DISTINCT orders.id_order)
            FROM 
                `" . _DB_PREFIX_ . "orders` orders  
            ".$sqlJoins."          
            WHERE 
                ".$shopConstraints."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
            AND 
                " . $orderStateCondition . 
                $excludeFreeOrder
                .$sqlFilters;

    $ordersNb = OpartStatTools::getValueFromCacheIfExists($sql1, $dateTo, $useCache, true);   

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
    } 
    else {  */   
        $sqlFilters2 = OpartStatTools::getFiltersForOpartSessionTable($filtersArray);
        $shopConstraints2 = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
        $lastStatDate = opartSession::getLastStatDate();
        $sessionsTable = OpartStatTools::getSessionsTableName(); 
        if ($lastStatDate == false) 
            return $result;
        
        if ($dateFrom < $lastStatDate)
            $dateFrom = $lastStatDate;
        
        $sql2 = "
            SELECT
                COUNT(DISTINCT CONCAT(DATE(first_visits.first_visit), first_visits.userIp))
            FROM
            (
                SELECT
                    opartstat_sessions.userIp,
                    MIN(createdAt) AS first_visit
                FROM
                    `" . pSQL($sessionsTable) . "` opartstat_sessions
                WHERE
                    `createdAt` BETWEEN '" . pSQL($dateFrom) . "' AND '" . pSQL($dateTo) . "'
                GROUP BY
                    userIp, DATE(createdAt)
            ) AS first_visits
            JOIN 
                `" . pSQL($sessionsTable) . "` opartstat_sessions 
            ON
                first_visits.first_visit = opartstat_sessions.createdAt 
            AND 
                first_visits.userIp = opartstat_sessions.userIp
            WHERE
                ".$shopConstraints2."
                " . $sqlFilters2;

        $visits = (int) OpartStatTools::getValueFromCacheIfExists($sql2, $dateTo, $useCache, true, true);

    /* } */

    $conversionRate = ($visits == 0 )?0:($ordersNb / $visits)*100;
        
    $result['value'] = $conversionRate;
    return $result;
}
