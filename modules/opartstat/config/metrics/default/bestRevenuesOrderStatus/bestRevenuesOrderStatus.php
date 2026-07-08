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

function getBestRevenuesOrderStatus($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesOrderStatus',$vars);
}

function getBestRevenuesOrderStatusValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;
    //we don't use statu for this metric so we do not need the order_history date add, we just need the order date_add

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $fields = opartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                orders.current_state, 
                ".$fields." as total,
                os.name
            FROM  
                `" . _DB_PREFIX_ . "orders` orders
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
            AND
                order_cart_rule.free_shipping = 1
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_detail order_detail 
            ON
                orders.id_order = order_detail.id_order
            LEFT JOIN
                `" . _DB_PREFIX_ . "order_state_lang` os
            ON
                orders.current_state = os.id_order_state
            AND
                os.id_lang = ".(int)$idLang."
            ".$sqlJoins."
            WHERE 
                orders.date_add >= '" . pSQL($dateFrom) . "'
            AND 
                orders.date_add <= '" . pSQL($dateTo) . "'
            AND
                ".$shopConstraints."
                ".$excludeFreeOrder."
                ".$sqlFilters."  
                ".$groupBy."
            ORDER BY 
                orders.date_add ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($orderList) == 0)
        return $result;    

    $mergedStatusList = [];
    foreach ($orderList as $o) {
        $total = ($o['total'] == null)?0:$o['total'];
        if (isset($mergedStatusList[$o['current_state']]) && $mergedStatusList[$o['current_state']]['total'] > 0) {
            $mergedStatusList[$o['current_state']]['total'] = $total+$mergedStatusList[$o['current_state']]['total'];
        }
        else {
            $mergedStatusList[$o['current_state']] = [
                'id' => $o['current_state'],
                'name' => $o['name'],
                'total' => $total
            ];
        }
    }

    $result['value'] = $mergedStatusList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
