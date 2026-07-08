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

function getBestRevenuesCarriers($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesCarriers',$vars);
}

function getBestRevenuesCarriersValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['conf']['totalRevenues'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    
    //$fields = OpartStatTools::getFields($filtersArray);
    $fields = OpartStatTools::getRevenueFieldsForOrderDetailLine();

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

       $sql = "
    SELECT 
        revenues.totalRevenues,
        shipping.total,
        shipping.id_carrier,
        carrier.name
    FROM
    (
        SELECT 
            orders.id_carrier,
            SUM(orders.total_shipping_tax_excl / COALESCE(NULLIF(orders.conversion_rate, 0), 1)) AS total
        FROM "._DB_PREFIX_."orders orders
        WHERE ".$orderStateCondition."
          AND ".$shopConstraints."
          AND orders.`".bqSQL($dateColumn)."` >= '".pSQL($dateFrom)."'
          AND orders.`".bqSQL($dateColumn)."` <= '".pSQL($dateTo)."'
          ".$excludeFreeOrder."
          ".$sqlFilters."
        GROUP BY orders.id_carrier
    ) AS shipping

    LEFT JOIN
    (
        SELECT 
            orders.id_carrier,
            SUM(".$fields.") AS totalRevenues
        FROM "._DB_PREFIX_."orders orders
        LEFT JOIN "._DB_PREFIX_."order_detail order_detail ON orders.id_order = order_detail.id_order
        LEFT JOIN "._DB_PREFIX_."order_cart_rule order_cart_rule ON orders.id_order = order_cart_rule.id_order
        ".$sqlJoins."
        WHERE ".$orderStateCondition."
          AND ".$shopConstraints."
          AND orders.`".bqSQL($dateColumn)."` >= '".pSQL($dateFrom)."'
          AND orders.`".bqSQL($dateColumn)."` <= '".pSQL($dateTo)."'
          ".$excludeFreeOrder."
          ".$sqlFilters."
        GROUP BY orders.id_carrier
    ) AS revenues ON shipping.id_carrier = revenues.id_carrier

    LEFT JOIN "._DB_PREFIX_."carrier carrier ON shipping.id_carrier = carrier.id_carrier

    LIMIT ".(int)$start.", ".(int)$limit;



    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($productList) == 0)
        return $result;    

    $mergedCarrierList = [];
    foreach($productList as $p) {
        $carrierName = ($p['name'] == null)?'Unknow':$p['name'];
        $carId = ($p['name'] == null)?0:$p['id_carrier'];
        if (isset($mergedCarrierList[$carId])) {
            $mergedCarrierList[$carId]['total'] += $p['total'];
            $mergedCarrierList[$carId]['totalRevenues'] += $p['totalRevenues'];
        }
        else {            
            $mergedCarrierList[$carId] = [
                'total' => $p['total'],
                'totalRevenues' => $p['totalRevenues'],
                'name' => '('.$carId.') '.$carrierName,
                'id' => $carrierName
            ];
        }
    }

    $result['value'] = $mergedCarrierList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
