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

function getGoogleAdsBestGroupsPerCostPerConversion($vars, $humanResult = true)
{
    return OpartStatTools::getBestMetricResult('googleAdsBestGroupsPerCostPerConversion', $vars);
}

function getGoogleAdsBestGroupsPerCostPerConversionValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars)
{
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);
    $fields = opartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
                            orders.id_cart, 
                            (" . $fields . ") as 'total',
                            orders.`" . bqSQL($dateColumn) . "`as 'orderDate',
                            orders.id_order as idOrder
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
                        " . $sqlJoins . "
                        WHERE 
                            " . $shopConstraints . "
                        AND 
                            " . $orderStateCondition . "
                            " . $excludeFreeOrder . "
                        AND
                            orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
                        AND 
                            orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
                        " . $sqlFilters . "
                        " . $groupBy . "
                    ";

    $ordersPlaced = [];
    $results = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);

    if (!is_array($results) || count($results) == 0)
        return $result;

    foreach ($results as $res)
        $ordersPlaced[$res['id_cart']] = array('total' => $res['total'], 'date' => $res['orderDate'], 'idOrder' => $res['idOrder']);

    $shops = opartStatTools::getShops();
    $attributionDelay = Configuration::get('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION');

    $datas = [
        'filtersArray' => $filtersArray,
        'shops' => $shops,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'ordersPlaced' => $ordersPlaced,
        'attributionDelay' => $attributionDelay,
        'originType' => "googleAdsGroups",
        'totalType' => "orderCount",
        'useCache' => $useCache
    ];

    /* $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/bestGroupsPerCostPerConversion.php", $useCache, $datas);

    $groups = $response['datas']; */

    $googleAdsElementsWithOrders = OpartStatTools::getOriginConversion('firstClick',$datas);

    if (!is_array($googleAdsElementsWithOrders) || count($googleAdsElementsWithOrders) == 0)
        return $result;
        
    $googleAdsElementsCostsByAdId = OpartStatTools::getGoogleAdsElementsCostsByElementId($dateFrom,$dateTo,$googleAdsElementsWithOrders,$datas['originType']);
    
    foreach($googleAdsElementsWithOrders as $id => $googleAdsElementWithOrders) {
        $resultDatas[$id] = $googleAdsElementWithOrders;
        if($googleAdsElementsCostsByAdId[$id] == 0)
            $resultDatas[$id]['total'] = 0;
        else
            $resultDatas[$id]['total'] = $googleAdsElementsCostsByAdId[$id] / $googleAdsElementWithOrders['total'];
    }

    $groups = $resultDatas;

    if ($groups == null || count($groups) == 0)
        return $result;

    $result['value'] = $groups;
    $result['conf']['allDataLoaded'] = true;

    return $result;
}
