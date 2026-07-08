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

function getbestRevenuesTaxes($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesTaxes',$vars);
}

function getbestRevenuesTaxesValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars) {
    $result['conf']['total'] = 'price';
    $result['conf']['totalHt'] = 'price';
    $result['conf']['totalTtc'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";    
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                order_detail_tax.id_tax, 
                SUM((order_detail.total_price_tax_incl - order_detail.total_price_tax_excl)) as total,                
                SUM(order_detail.total_price_tax_excl) as totalHt,
                SUM(order_detail.total_price_tax_incl) as totalTtc,
                order_detail.tax_name as name
            FROM
                " . _DB_PREFIX_ . "orders orders            
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
            AND
                order_cart_rule.free_shipping = 1
            INNER JOIN 
                " . _DB_PREFIX_ . "order_detail order_detail 
            ON
                orders.id_order = order_detail.id_order
            INNER JOIN 
                " . _DB_PREFIX_ . "order_detail_tax order_detail_tax 
            ON
                order_detail.id_order_detail = order_detail_tax.id_order_detail
                ".$sqlJoins."            
            WHERE
                " . $orderStateCondition . "
                ".$excludeFreeOrder."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            AND
                ".$shopConstraints."
            ".$sqlFilters."
            GROUP BY
                order_detail_tax.id_tax            
            ORDER BY
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $taxesList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    //echo $sql."<br />";
    if (count($taxesList) == 0)
        return $result;    

    $mergedTaxesList = [];
    foreach ($taxesList as $p) {
        $total = ($p['total'] == null)?0:$p['total'];
        $totalHt = ($p['totalHt'] == null)?0:$p['totalHt'];
        $totalTtc = ($p['totalTtc'] == null)?0:$p['totalTtc'];

        if (isset($mergedTaxesList[$p['id_tax']])) {
            $mergedTaxesList[$p['id_tax']]['total'] += $total;
            $mergedTaxesList[$p['id_tax']]['totalHt'] += $totalHt;
            $mergedTaxesList[$p['id_tax']]['totalTtc'] += $totalTtc;
        } else {
            $name = ($p['name'] == null)?'Unknow':$p['name'];
            $mergedProductList[$p['id_tax']] = [
                'id' => $p['id_tax'],
                'name' => '('.$p['id_tax'].') '.$name,   
                //'total' => $total,   
                'total' => $total,   
                'totalHt' => $totalHt,   
                'totalTtc' => $totalTtc      
            ];
        }
    }
    
    $result['value'] = $mergedProductList;    
    $result['conf']['allDataLoaded'] = false;
    $result['sql'] = $sql;

    return $result;
}
