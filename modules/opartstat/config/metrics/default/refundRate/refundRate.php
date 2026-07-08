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

function getRefundRate($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('RefundRate',$vars,'',false);
}

function getRefundRateValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateConditionRefund = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_REFUNDED_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]); 
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                (
                    (
                        SELECT  
                            COUNT(DISTINCT orders.id_order)
                        FROM 
                            `"._DB_PREFIX_."orders` orders
                        ".$sqlJoins."                        
                        WHERE 
                            ".$shopConstraints."
                        AND
                            orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                        AND 
                            orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                        AND 
                            ".$orderStateConditionRefund."
                        ".$excludeFreeOrder."            
                        " .$sqlFilters."
                    ) 
                / 
                    (
                        SELECT  
                            COUNT(DISTINCT orders.id_order)
                        FROM 
                            `"._DB_PREFIX_."orders` orders   
                        ".$sqlJoins."                     
                        WHERE 
                            ".$shopConstraints."
                            ".$excludeFreeOrder."
                        AND
                            orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                        AND 
                            orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'               
                        " .$sqlFilters."
                    )
                )*100 AS total";
    
    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'percent');
}