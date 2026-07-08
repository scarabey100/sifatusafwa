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

function getBestRevenuesCompanies($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesCompanies',$vars);
}

function getBestRevenuesCompaniesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $result['conf']['thLabels'] = [
        'thLabel1' => 'companies', 
        'thLabel2' => 'revenues' 
    ];

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    $fields = OpartStatTools::getTotalRevenueFields(); 
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['address','order_detail']);

    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                ".$fields." as total,
                orders.id_order,
                address.company,
                id_address
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
            `" . _DB_PREFIX_ . "address` address 
            ON 
                orders.id_address_invoice = address.id_address
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
            GROUP BY
                orders.id_order
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";       

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($orderList) == 0)
        return $result;    

    $mergedList = [];
    foreach($orderList as $o) {
        $companyName = ($o['id_address']==null)?"Unknow":$o['company'];
        $companyName = ($companyName=='')?'Unknow':$companyName;
        $total = ($o['total'] == null)?0:$o['total'];
        if (isset($mergedList[$companyName])) {
            $mergedList[$companyName]['total'] += $total;
        }
        else {
            $mergedList[$companyName] = [
                'total' => $total,
                'name' => $companyName,
                'id' => $companyName
            ];
        }
    }

    $result['value'] = $mergedList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
