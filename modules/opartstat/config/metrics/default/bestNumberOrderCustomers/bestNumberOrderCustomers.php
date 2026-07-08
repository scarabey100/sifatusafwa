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

function getbestNumberOrderCustomers($vars)
{
    return OpartStatTools::getBestMetricResult('bestNumberOrderCustomers',$vars);
}

function getbestNumberOrderCustomersValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
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
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['customer']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);
    $sql = "SELECT SQL_NO_CACHE  
                customer.id_customer, 
                customer.firstname, 
                customer.lastname,
                customer.email
            FROM  
                `" . _DB_PREFIX_ . "orders` orders               
            LEFT JOIN 
                `" . _DB_PREFIX_ . "customer` customer 
            ON 
                orders.id_customer = customer.id_customer
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
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";   

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($orderList) == 0) 
        return $result;    
        
    $token = Tools::getAdminTokenLite('AdminCustomers');
    
    $mergedCustomerList = [];
    foreach ($orderList as $o) {
        //$total = $o['total_orders'];

        if (isset($mergedCustomerList[$o['id_customer']]) && $mergedCustomerList[$o['id_customer']]['total'] > 0) {
            $mergedCustomerList[$o['id_customer']]['total']++;
        }
        else {
            $customerLink = 'index.php?controller=AdminCustomers&id_customer='.(int)$o['id_customer'].'&viewcustomer&token='.$token;
            $mergedCustomerList[$o['id_customer']] = [
                'total' => 1,
                'name' => $o['firstname'].' '.$o['lastname'],
                'link' => $customerLink,
                'email' => $o['email'],
                'id' => $o['id_customer'],
            ];
        }
    }

    $result['value'] = $mergedCustomerList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
