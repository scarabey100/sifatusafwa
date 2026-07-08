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

function getNumberOfInactivCustomers($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('NumberOfInactivCustomers',$vars,'',false);
}

function getNumberOfInactivCustomersValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $interval = Configuration::get('OPARTSTAT_INACTIV_CUSTOMER_DAYS');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $today = date("Y-m-d H:i:s");
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';     
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['customer']);

    $sql =  "
    SELECT SQL_NO_CACHE 
        COUNT(DISTINCT customer.id_customer) 
    FROM 
        `"._DB_PREFIX_."customer` customer
    WHERE EXISTS (
        SELECT  
            1 
        FROM 
            `"._DB_PREFIX_."orders` orders 
        ".$sqlJoins."         
        WHERE 
            orders.id_customer = customer.id_customer
        AND
            ".$shopConstraints."
        AND
            orders.`".bqSQL($dateColumn)."` < DATE_SUB('".pSQL($today)."', INTERVAL ".(int)$interval." DAY)
        AND 
            ".$orderStateCondition."            
            ".$excludeFreeOrder."
            " . $sqlFilters . "
    )
    AND NOT EXISTS (
        SELECT  
            1 
        FROM 
            `"._DB_PREFIX_."orders` orders
        ".$sqlJoins."          
        WHERE 
            orders.id_customer = customer.id_customer
        AND
            ".$shopConstraints."
        AND
            orders.`".bqSQL($dateColumn)."` > DATE_SUB('".pSQL($today)."', INTERVAL ".(int)$interval." DAY)
        AND 
            ".$orderStateCondition."            
            ".$excludeFreeOrder."
            " . $sqlFilters . "
    )";

    return OpartStatTools::getSingleNumberJsonResult($sql,0,$useCache);
}