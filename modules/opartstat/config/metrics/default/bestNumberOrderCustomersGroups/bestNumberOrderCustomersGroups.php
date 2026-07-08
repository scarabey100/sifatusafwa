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

function getBestNumberOrderCustomersGroups($vars)
{
    return OpartStatTools::getBestMetricResult('bestNumberOrderCustomersGroups',$vars);
}

function getBestNumberOrderCustomersGroupsValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
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
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['customer']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                COUNT(DISTINCT orders.id_order) AS total,        
                customer.id_default_group as id_group, 
                gl.`name`,         
                orders.id_order
            FROM  
                `" . _DB_PREFIX_ . "orders` orders                    
            LEFT JOIN 
                `" . _DB_PREFIX_ . "customer` customer 
            ON 
                orders.id_customer = customer.id_customer
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
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            AND 
                " . $orderStateCondition . "
            ".$excludeFreeOrder."
            " .$sqlFilters."  
            GROUP BY
                customer.id_default_group
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    //echo $sql."<br />";
    if (count($orderList) == 0)
        return $result;    

    $mergedList = [];
    foreach ($orderList as $o) {
        $total = ($o['total'] == null) ? 0 : $o['total'];
        if (isset($mergedList[$o['id_group']])) {
            $mergedList[$o['id_group']]['total'] += $total;
        } else {
            $mergedList[$o['id_group']] = [
                'total' => $total,
                'name' => $o['name'],
                'id' => $o['id_group']
            ];
        }
    }

    $result['value'] = $mergedList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
