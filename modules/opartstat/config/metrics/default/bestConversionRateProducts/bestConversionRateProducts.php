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
    return OpartStatTools::getBestMetricResult('bestConversionRateProducts', $vars);
}

function getbestConversionRateProductsValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
{
    $result['conf']['total'] = 'percent';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;


    

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

        $response = OpartStatTools::getMetricsResultFromSaas("metrics/prestashop/productPageViews.php", $useCache, $datas);

        if ($response['datas'] == null)
            return $result;

        $sessions = $response['datas'];
    } else { */
        $shopConstraints1 = OpartStatTools::getShopConstraints('opartstat_sessions', 'shopId');
        $sqlFilters1 = OpartStatTools::getFiltersForOpartSessionTable($filtersArray, ['categories', 'brands']);
        $lastStatDate = opartSession::getLastStatDate();
        $sessionsTable = OpartStatTools::getSessionsTableName();
        if ($lastStatDate == false)
            return $result;

        if ($dateFrom < $lastStatDate)
            $dateFrom = $lastStatDate;

        $sql1 = "SELECT
            COUNT(*) as visits,
            opartstat_sessions.elementId as productId
        FROM
            `" . pSQL($sessionsTable) . "` opartstat_sessions
        WHERE
            ".$shopConstraints1."
            " . $sqlFilters1 . "
        AND
            opartstat_sessions.controllerName = 'ProductController'
        GROUP BY
            opartstat_sessions.elementId
        ";

        $sessions = OpartStatTools::executeSessionsSelect($sql1);
    /* } */
    if (count($sessions) == 0)
        return $result;

    $in = "";
    foreach ($sessions as $session) {
        $in .= ($in == "") ? "" : ",";
        $in .= (int)$session['productId'];
        $visitsProducts[$session['productId']] = $session['visits'];
    }
    $in = "order_detail.product_id  IN (" . $in . ")";
    $sqlFilters2 = OpartStatTools::getFilters($filtersArray);
    $sqlJoins2 = OpartStatTools::getJoins($filtersArray, ['product_lang', 'order_detail']);
    $shopConstraints2 = OpartStatTools::getShopConstraints();

    //important !
    //here we do not use invoice_date because what we need is the moment where the order was created !
    //if the customer visits the website one day, create an order the same day and the order is validated 2 days later, this still is a conversion !
    $sql2 = "
        SELECT SQL_NO_CACHE               
            order_detail.product_id,
            IFNULL(product_lang.`name`,order_detail.`product_name`) AS product_name, 
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
            product_lang.id_lang = " . (int)$idLang . "
        ".$sqlJoins2." 
        WHERE 
            " . pSQL($in) . "
        AND
            orders.date_add >= '" . pSQL($dateFrom) . "'  
        AND 
            orders.date_add <= '" . pSQL($dateTo) . "' 
        AND
            ".$shopConstraints2."
        AND 
            " . $orderStateCondition . "
        ".$excludeFreeOrder."
        " . $sqlFilters2 . "        
        GROUP BY 
            order_detail.product_id
        LIMIT 
            " . (int)$start . ", " . (int)$limit . "
    ";

    $products = OpartStatTools::getValueFromCacheIfExists($sql2, $dateTo, $useCache);

    if (count($products) == 0)
        return $result;
    
    $token = Tools::getAdminTokenLite('AdminProducts');

    $mergedProductList = [];
    foreach ($products as $p) {
        $visits = (isset($visitsProducts[$p['product_id']])) ? $visitsProducts[$p['product_id']] : 0;
        $conversionRate = ($visits > 0) ? ($p['totalOrders'] / $visits) * 100 : -1;
        $productName = ($p['product_name'] == null) ? 'Unknow' : $p['product_name'];
        $productLink = 'index.php?controller=AdminProducts&id_product='.(int)$p['product_id'].'&updateproduct&token='.$token;
        $mergedProductList[$p['product_name']] = [
            'id' => $p['product_id'],
            'name' => '(' . $p['product_id'] . ') ' . $productName,
            'link' => $productLink,
            'total' => $conversionRate
        ];
    }

    $result['value'] = $mergedProductList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
