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

require_once(dirname(__FILE__) . '/../../../../classes/opartStatTools.php');

function getGoogleAdsRevenues($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('GoogleAdsRevenues',$vars);
}

function getGoogleAdsRevenuesValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');    
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $fields = opartStatTools::getFields($filtersArray);
    //$fields = OpartStatTools::getRevenueFieldsForOrderDetailLine($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
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
        ";

    $ordersPlaced = [];
    $results = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache); 
    foreach($results as $res) 
        $ordersPlaced[$res['id_order']] = $res['total'];

    $shops = opartStatTools::getShops();        
    $datas = [
        'filtersArray' => $filtersArray,
        'shops' => $shops,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'ordersPlaced' => $ordersPlaced
    ];

    $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/revenues.php",$useCache,$datas);

    $total = $response['datas'];

    $valueType = 'price';
    $result['value'] = $total;
    $result['conf'] = [
        'total' => $valueType
    ];

    return $result;
}