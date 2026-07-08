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

function getbestCouponsPerUse($vars)
{
    return OpartStatTools::getBestMetricResult('bestCouponsPerUse',$vars);
}

function getbestCouponsPerUseValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    //$result['conf']['total'] = 'price';
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    $fields = OpartStatTools::getRevenueFieldsForOrderDetailLine();

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','product_lang']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                order_cart_rule.`id_cart_rule`, 
                order_cart_rule.`name` AS cart_rule_name, 
                COUNT(DISTINCT orders.id_order) as total
            FROM
                " . _DB_PREFIX_ . "orders orders            
            INNER JOIN 
                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
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
                order_cart_rule.id_cart_rule
            ORDER BY
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $couponList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    //echo $sql."<br />";
    if (count($couponList) == 0)
        return $result;    

    $mergedCouponList = [];
    foreach ($couponList as $p) {
        $total = ($p['total'] == null)?0:$p['total'];
        if (isset($mergedCouponList[$p['id_cart_rule']])) {
            $mergedCouponList[$p['id_cart_rule']]['total'] += $total;
        } else {
            $couponName = ($p['cart_rule_name'] == null)?'Unknow':$p['cart_rule_name'];
            $mergedCouponList[$p['id_cart_rule']] = [
                'id' => $p['id_cart_rule'],
                'name' => '('.$p['id_cart_rule'].') '.$couponName,   
                'total' => $total      
            ];
        }
    }
    
    $result['value'] = $mergedCouponList;    
    $result['conf']['allDataLoaded'] = false;
    $result['sql'] = $sql;

    return $result;
}
