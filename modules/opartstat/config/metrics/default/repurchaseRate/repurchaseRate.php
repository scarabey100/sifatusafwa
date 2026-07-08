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

function getRepurchaseRate($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('RepurchaseRate',$vars);
}

function getRepurchaseRateValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition1 = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER','orders');
    $excludeFreeOrder1 = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);   
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT
                (
                    (
                        SELECT
                            SUM(repurchase) 
                        FROM 
                            (
                                SELECT  
                                    orders.id_customer, 
                                    COUNT(orders.id_customer)-1 as repurchase 
                                FROM 
                                    "._DB_PREFIX_."orders orders 
                                ".$sqlJoins."                                
                                WHERE
                                    ".$shopConstraints."
                                AND 
                                    " .$orderStateCondition1. "
                                    ".pSQL($excludeFreeOrder1)."
                                AND
                                    orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                                AND 
                                    orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                                ".$sqlFilters."
                                GROUP BY 
                                    orders.id_customer 
                                HAVING 
                                    COUNT(orders.id_customer) > 1
                            ) r
                    ) 
                    /
                    (
                        SELECT
                            COUNT(orders.id_order)
                        FROM
                            `"._DB_PREFIX_."orders` orders   
                        ".$sqlJoins."                     
                        WHERE
                            ".$shopConstraints."
                        AND 
                            " .$orderStateCondition1. "
                            ".pSQL($excludeFreeOrder1)."  
                        AND
                            orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                        AND 
                            orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'  
                        ".$sqlFilters." 
                    )
                )*100";

return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'percent');
}