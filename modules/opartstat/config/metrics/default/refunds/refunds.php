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

function getRefunds($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('Refunds',$vars,'',false);
}

function getRefundsValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_REFUNDED_ORDER'); 
    $orderStateCondition2 = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');    
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    //$getTotalRefundedValue = (_PS_VERSION_ < "1.7.7.0")?'(order_detail.product_quantity_refunded*order_detail.unit_price_tax_excl)':'order_detail.total_refunded_tax_excl';
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    /* if(Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING') == 0) {
        $fields = 'orders.total_paid_tax_excl';        
    }
    else {
        //Remove shipping from total AND add cart_rules that remove shipping cost (otherwise shipping will be removed twice)
        $fields ='(orders.total_paid_tax_excl - orders.total_shipping_tax_excl) + IFNULL(order_cart_rule.value_tax_excl,0)';
    } */
    $totalRefundedFields = opartStatTools::getTotalRefundedFields(false);
    $fields = OpartStatTools::getTotalRevenueFields(true,false);

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);
    $sqlJoins2 = OpartStatTools::getJoins($filtersArray,['order_detail']);   
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    
                $sql =  "SELECT
                (
                    IFNULL(
                        (SELECT  
                            SUM(total1) as grand_total2
                        FROM
                        (
                            SELECT  
                                (".$fields.") as total1
                            FROM 
                                `"._DB_PREFIX_."orders` orders
                            LEFT JOIN 
                                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                            ON
                                orders.id_order = order_cart_rule.id_order
                            AND
                                order_cart_rule.free_shipping = 1
                            ".$sqlJoins."
                            WHERE 
                                ".$shopConstraints."
                            AND
                                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                            AND 
                                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                            AND 
                                ".$orderStateCondition. "                
                            ".$excludeFreeOrder."            
                            " .$sqlFilters."
                            GROUP BY 
                                orders.id_order
                        ) 
                        subquery1)
                    ,0) 
                )
                +
                (
                    IFNULL(
                        (SELECT  
                            SUM(total2) as grand_total2
                        FROM
                        (
                            SELECT  
                                ".$totalRefundedFields." as total2
                            FROM 
                                `"._DB_PREFIX_."orders` orders     
                            LEFT JOIN 
                                " . _DB_PREFIX_ . "order_detail order_detail 
                            ON
                                orders.id_order = order_detail.id_order
                                " .$sqlJoins2."
                            WHERE 
                                ".$shopConstraints."                        
                            AND
                                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                            AND 
                                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'  
                            AND 
                                ".$orderStateCondition2. "                
                            ".$excludeFreeOrder."          
                            " .$sqlFilters."
                            GROUP BY 
                                order_detail.id_order_detail
                        )  subQuery2)
                    ,0)  
                )"
                ;

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'price');
}