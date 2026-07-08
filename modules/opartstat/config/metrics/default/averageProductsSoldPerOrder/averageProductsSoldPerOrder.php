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

function getAverageProductsSoldPerOrder($vars)
{
    return OpartStatTools::getMetricResult('AverageProductsSoldPerOrder',$vars);
}

function getAverageProductsSoldPerOrderValues($dateFrom, $dateTo, $filtersArray, $vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE  
                AVG(numberOfProducts) 
            FROM 
                (
                    SELECT   
                        COUNT(DISTINCT order_detail.`product_id`) as numberOfProducts
                    FROM 
                        `" . _DB_PREFIX_ . "order_detail` order_detail         
                    INNER JOIN 
                        `" . _DB_PREFIX_ . "orders` orders 
                    ON
                        orders.id_order = order_detail.id_order
                    AND
                        ".$shopConstraints."
                    AND 
                        " . $orderStateCondition . "
                    ".$excludeFreeOrder."
                    ".$sqlJoins."   
                    WHERE  
                        orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
                    " .$sqlFilters." 
                    GROUP BY 
                        orders.id_order
                ) as t";
                
    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'float2');
}
