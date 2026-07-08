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

function getbestConversionRateProducts($vars)
{
    return OpartStatTools::getBestMetricResult('bestConversionRateProducts',$vars);
}

function getbestConversionRateProductsValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'percent';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();    
    $shopConstraints2 = OpartStatTools::getShopConstraints('opartstat_sessions','shopId');

    $lastStatDate = opartSession::getLastStatDate();
    if ($lastStatDate == false) 
        return $result;    

    if ($dateFrom < $lastStatDate)
        $dateFrom = $lastStatDate;

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product_lang','order_detail']);

    //important !
    //here we do not use invoice_date because what we need is the moment where the order was created !
    //if the customer visits the website one day, create an order the same day and the order is validated 2 days later, this still is a conversion !
    $sql1 = "
        SELECT SQL_NO_CACHE               
            order_detail.product_id,
            product_lang.`name` AS product_name, 
            COUNT(DISTINCT orders.id_order) as totalOrders
        FROM 
            " . _DB_PREFIX_ . "order_detail order_detail
        INNER JOIN 
            " . _DB_PREFIX_ . "orders orders ON orders.id_order = order_detail.id_order
        LEFT JOIN 
            `" . _DB_PREFIX_ . "product_lang` product_lang 
        ON
            order_detail.`product_id` = product_lang.`id_product`
        AND 
            product_lang.id_shop = orders.id_shop
        AND
            product_lang.id_lang = ".(int)$idLang."
        ".$sqlJoins." 
        WHERE 
            orders.date_add >= '" . pSQL($dateFrom) . "'  
        AND 
            orders.date_add <= '" . pSQL($dateTo) . "' 
        AND
            ".$shopConstraints."
        AND 
            " . $orderStateCondition . "
        ".$excludeFreeOrder."
        " .$sqlFilters."        
        GROUP BY 
            order_detail.product_id
        LIMIT 
            " . (int)$start . ", " . (int)$limit . "
    ";

    $res = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql1);
    if (count($res) == 0) 
        return $result;    

    $products = [];
    $whereQuery = "";
    foreach($res AS $p) {
        $prodId = $p['product_id'];   
           
        $products[] = [
            'totalOrders' => $p['totalOrders'],
            'name' => $p['product_name'],
            'id' => $prodId
        ];


        $whereQuery .= ($whereQuery == "") ? "opartstat_sessions.elementId = ".(int)$prodId:" OR opartstat_sessions.elementId = ".(int)$prodId;
    }



    $sqlFilters2 = OpartStatTools::getFiltersForOpartSessionTable($filtersArray,['categories','brands']);

    $sql2 = "SELECT
                COUNT(DISTINCT CONCAT(DATE(first_visits.first_visit), first_visits.userIp)) as visits,
                opartstat_sessions.elementId
            FROM
            (
                SELECT
                    opartstat_sessions.userIp,
                    MIN(createdAt) AS first_visit
                FROM
                    `" . _DB_PREFIX_ . "opartstat_sessions` opartstat_sessions
                WHERE
                    `createdAt` BETWEEN '" . pSQL($dateFrom) . "' AND '" . pSQL($dateTo) . "'
                GROUP BY
                    userIp, DATE(createdAt)
            ) AS first_visits
            JOIN 
                `" . _DB_PREFIX_ . "opartstat_sessions` opartstat_sessions 
            ON
                first_visits.first_visit = opartstat_sessions.createdAt 
            AND 
                first_visits.userIp = opartstat_sessions.userIp
            WHERE
                ".$shopConstraints2."
                " . $sqlFilters2 . "
            AND
                (".$whereQuery.")
            AND
                opartstat_sessions.controllerName = 'ProductController'
            GROUP BY
                opartstat_sessions.elementId
            ";

    $res2 = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($sql2);
    $visitsArray = array_column($res2, 'visits', 'elementId');
    $mergedProductList = [];
    foreach($products AS $p) {
        $visits = (isset($visitsArray[$p['id']]))?$visitsArray[$p['id']]:0;  
        $conversionRate = ($visits>0)?($p['totalOrders']/$visits)*100:-1;
        $productName = ($p['name'] == null)?'Unknow':$p['name'];
        $mergedProductList[$p['name']] = [
            'id' => $p['id'],
            'name' => '('.$p['id'].') '.$productName,
            'total' => $conversionRate
        ];
    }

    $result['value'] = $mergedProductList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
