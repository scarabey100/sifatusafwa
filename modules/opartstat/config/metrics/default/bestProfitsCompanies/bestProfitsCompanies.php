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

function getBestProfitsCompanies($vars)
{
    return OpartStatTools::getBestMetricResult('bestProfitsCompanies',$vars);
}

function getBestProfitsCompaniesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    $fields = OpartStatTools::getProfitFieldsForOrderDetailLine();

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['address','order_detail'],[],true);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
        orders.id_order,
        address.company,
        address.id_address,
        (".$fields.") as total
    FROM  
        `" . _DB_PREFIX_ . "orders` orders 
    LEFT JOIN 
        `" . _DB_PREFIX_ . "order_cart_rule` order_cart_rule 
    ON
        orders.id_order = order_cart_rule.id_order 
    AND 
        order_cart_rule.free_shipping = 1
    INNER JOIN
        `" . _DB_PREFIX_ . "order_detail` order_detail   
    ON
        orders.id_order = order_detail.id_order
    LEFT JOIN 
    `" . _DB_PREFIX_ . "address` address 
    ON 
        orders.id_address_invoice = address.id_address
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
    ".$sqlFilters."
    ORDER BY 
        orders.`".bqSQL($dateColumn)."` ASC
    LIMIT 
        " . (int)$start . ", " . (int)$limit . "
    ";

    $orderDetailList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($orderDetailList) == 0)
        return $result;    

    $mergedList = [];
    foreach($orderDetailList as $d) {
        $companyName = ($d['id_address']==null)?"Unknow":$d['company'];
        $companyName = ($companyName=='')?'Unknow':$companyName;
        $total = ($d['total'] == null)?0:$d['total'];
        if (isset($mergedList[$companyName])) {
            $mergedList[$companyName]['total'] += $total;
        }
        else {
            $mergedList[$companyName] = [
                'id' => $companyName,
                'name' => $companyName,
                'total' => $total,
            ];
        }
    }

    $result['value'] = $mergedList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
