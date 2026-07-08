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

function getBestRevenuesCustomersGroups($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesCustomersGroups',$vars);
}

function getBestRevenuesCustomersGroupsValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;    
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['customer','order_detail']);
    $fields = OpartStatTools::getFields($filtersArray);  
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                customer.id_default_group as id_group, 
                gl.`name`,
                (".$fields.") as total,
                orders.id_order 
            FROM  
                `" . _DB_PREFIX_ . "orders` orders             
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
            AND
                order_cart_rule.free_shipping = 1           
            LEFT JOIN 
                `" . _DB_PREFIX_ . "customer` customer 
            ON 
                orders.id_customer = customer.id_customer
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_detail order_detail 
            ON
                orders.id_order = order_detail.id_order
            LEFT JOIN  
                `" . _DB_PREFIX_ . "group_lang` gl 
            ON 
                gl.id_group = customer.id_default_group
            AND
                gl.id_lang = ".(int)$idLang."
            ".$sqlJoins."
            WHERE 
                ".$shopConstraints."
            AND 
                " . $orderStateCondition . "
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
            ".$excludeFreeOrder."
            ".$sqlFilters."
            ".$groupBy."      
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($orderList) == 0)
        return $result;    

    $mergedCustomerList = [];
    foreach ($orderList as $o) {
        $total = ($o['total'] == null)?0:$o['total'];
        if (isset($mergedCustomerList[$o['id_group']]) && $mergedCustomerList[$o['id_group']]['total'] > 0) {
            $mergedCustomerList[$o['id_group']]['total'] = $total+$mergedCustomerList[$o['id_group']]['total'];
        }
        else {
            $mergedCustomerList[$o['id_group']] = [
                'id' => $o['id_group'],
                'name' => $o['name'],
                'total' => $total
            ];
        }
    }

    $result['value'] = $mergedCustomerList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
