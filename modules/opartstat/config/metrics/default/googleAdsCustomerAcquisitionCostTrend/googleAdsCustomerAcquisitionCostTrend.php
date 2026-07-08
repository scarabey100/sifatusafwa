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

function getGoogleAdsCustomerAcquisitionCostTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('googleAdsCustomerAcquisitionCostTrend', $vars, 'price', false);
}

function getGoogleAdsCustomerAcquisitionCostTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $result['value'] = 0;
    $result['conf'] = [
        'total' => 'price'
    ];
    if ($dateFrom == 0 || $dateTo == 0)
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $shopConstraints2 = OpartStatTools::getShopConstraints('orders1');
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $orderStateCondition2 = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER', 'orders1');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $excludeFreeOrder2 = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders1.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT
                    orders_min.id_order_min as idOrder,
                    orders.id_cart,
                    orders.`" . bqSQL($dateColumn) . "`as 'orderDate'
                FROM
                    `ps_customer` customer
                JOIN(
                    SELECT SQL_NO_CACHE MIN(orders.id_order) AS id_order_min,
                        orders.id_customer
                    FROM
                        `ps_orders` orders
                    WHERE 
                        " . $shopConstraints . "
                    AND 
                        " . $orderStateCondition . "
                        " . $excludeFreeOrder . "
                    AND
                        orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "' 
                    GROUP BY
                        orders.id_customer
                    HAVING
                        MIN(orders.date_add) =(
                            SELECT
                                MIN(orders1.date_add)
                            FROM
                                `ps_orders` orders1
                            WHERE
                                orders1.id_customer = orders.id_customer 
                            AND 
                                " . $shopConstraints2 . " 
                            AND 
                                " . $orderStateCondition2 . "
                                " . $excludeFreeOrder2 . "
                        )
                    ) AS orders_min
                ON
                    customer.id_customer = orders_min.id_customer
                JOIN 
                    `ps_orders` orders 
                ON
                    orders_min.id_order_min = orders.id_order
                WHERE
                        orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "' 
                ";

    $ordersPlaced = [];
    $results = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);

    if (!is_array($results) || count($results) == 0)
        return $result;

    foreach ($results as $res)
        $ordersPlaced[$res['id_cart']] = array('total' => 0, 'date' => $res['orderDate'], 'idOrder' => $res['idOrder']);

    $shops = opartStatTools::getShops();
    $attributionDelay = Configuration::get('OPARTSTAT_CONVERSION_ATTRIBUTION_DURATION');

    $datas = [
        'filtersArray' => $filtersArray,
        'shops' => $shops,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo,
        'ordersPlaced' => $ordersPlaced,
        'attributionDelay' => $attributionDelay,
        'originType' => "googleAdsCampaigns",
        'totalType' => "orderCount",
        'useCache' => $useCache
    ];

   /*  $response = OpartStatTools::getMetricsResultFromSaas("metrics/googleAds/customerAcquisitionCostTrend.php", $useCache, $datas);

    if ($response['datas'] == null)
        return $result;

    $totalGlobal = $response['datas']['totalGlobal'];

    unset($response['datas']['totalGlobal']); */

    //$r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom,$dateTo,$response['datas'],'createdAt','total');

    $resultDatas = OpartStatTools::getOriginConversion('firstClick',$datas);

    $conversions = [];
    
    foreach($resultDatas as $resultData) {
        foreach($resultData['orders'] as $order) {
            $conversions[] = array(
                'total' => 1,
                'createdAt' => date('Y-m-d', strtotime($order['orderDate']))
            );
        }    
    }
    
    foreach ($conversions as $conversion) {
        $day = date('Y-m-d', strtotime($conversion['createdAt']));
        if(isset($costPerConversionPerDay[$day]['conversions']))
            $costPerConversionPerDay[$day]['conversions']++;
        else
            $costPerConversionPerDay[$day]['conversions'] = 1;
        
    }
    
    $sql = "SELECT SQL_NO_CACHE             
                SUM(costMicros/1000000) as costs,
                createdAt 
            FROM 
                " . _DB_PREFIX_ . "opartstat_googleAdsDailyDatas googleAdsDailyDatas 
            WHERE 
                createdAt >= '".pSQL($dateFrom)."' 
            AND 
                createdAt <= '".pSQL($dateTo)."'
            GROUP BY
                createdAt
            ";
                
    $costs = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);
    
    if(!is_array($costs) || count($costs)==0) {
        echo json_encode(['success' => true, 'datas' => []]);
        die();
    }
    
    foreach ($costs as $cost) {
        $day = date('Y-m-d', strtotime($cost['createdAt']));
        if(isset($costPerConversionPerDay[$day]['costs']))
            $costPerConversionPerDay[$day]['costs'] += $cost['costs'];
        else
            $costPerConversionPerDay[$day]['costs'] = $cost['costs'];;    
    }
    
    $totalCosts = 0;
    $totalConversions = 0;
    
    foreach ($costPerConversionPerDay as $date => $data) {
        $total = 0;
        $conversions = 0;
    
        if(isset($data['conversions'])) {
            $conversions = $data['conversions'];
            $total = $data['costs']/$conversions;
    
            $totalConversions += $data['conversions'];
        }        
    
        $totalCosts += $data['costs'];
    
        $acquisitionCostsDatas[] = array(
            'createdAt' => $date,
            'total' => $total
        );
    }
    
    $totalGlobal = ($totalConversions == 0)?0:$totalCosts / $totalConversions;

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom, $dateTo, $acquisitionCostsDatas, 'createdAt', 'total');

    $result['value'] = [
        'globalValue' => $totalGlobal,
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
