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

function getBestNumberShippingCarriers($vars)
{
    return OpartStatTools::getBestMetricResult('bestNumberShippingCarriers',$vars);
}

function getBestNumberShippingCarriersValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                COUNT(orders.id_carrier) as total, 
                orders.id_carrier,
                carrier.name
            FROM
                " . _DB_PREFIX_ . "orders orders                     
            LEFT JOIN 
                " . _DB_PREFIX_ . "carrier carrier 
            ON
                orders.id_carrier = carrier.id_carrier
            ".$sqlJoins." 
            WHERE
                " . $orderStateCondition . "
            ".$excludeFreeOrder."
            " .$sqlFilters."
            AND
                ".$shopConstraints."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            GROUP BY
                orders.id_carrier
            ORDER BY
                orders.date_add ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    //echo $sql."<br />";
    if (count($productList) == 0) 
        return $result;
    

    $mergedCarrierList = [];
    foreach($productList as $p) {
        $carrierName = ($p['name'] == null)?'Unknow':$p['name'];
        $carId = ($p['name'] == null)?0:$p['id_carrier'];
        if (isset($mergedCarrierList[$carId])) {
            $mergedCarrierList[$carId]['total'] += $p['total'];
        }
        else {            
            $mergedCarrierList[$carId] = [
                'total' => $p['total'],
                'name' => '('.$carId.') '.$carrierName,
                'id' => $carrierName
            ];
        }
    }

    $result['value'] = $mergedCarrierList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
