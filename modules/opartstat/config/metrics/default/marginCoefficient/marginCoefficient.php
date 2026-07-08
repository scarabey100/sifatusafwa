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

function getMarginCoefficient($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('MarginCoefficient',$vars,'');
}

function getMarginCoefficientValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $getTotalRefundedValue = (_PS_VERSION_ < "1.7.7.0")?'(order_detail.product_quantity_refunded*order_detail.unit_price_tax_excl)':'order_detail.total_refunded_tax_excl';
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);   
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
                AVG(marginCoefficient) 
            FROM 
                (
                    SELECT  
                        ( 
                            SUM(order_detail.total_price_tax_excl - ".$getTotalRefundedValue." - ((orders.total_discounts_tax_excl-IFNULL(order_cart_rule.value_tax_excl,0))*(order_detail.total_price_tax_excl/orders.total_products)))
                            /
                            SUM((order_detail.product_quantity-order_detail.product_quantity_refunded) * IFNULL(order_detail.purchase_supplier_price,order_detail.original_wholesale_price))
                        ) as 'marginCoefficient'
                    FROM 
                        " . _DB_PREFIX_ . "orders orders 
                    LEFT JOIN 
                        " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                    ON
                        orders.id_order = order_cart_rule.id_order
                    AND
                        order_cart_rule.free_shipping = 1                        
                    INNER JOIN 
                        " . _DB_PREFIX_ . "order_detail order_detail 
                    ON 
                        orders.id_order = order_detail.id_order    
                    ".$sqlJoins."                 
                    WHERE 
                        ".$shopConstraints." 
                    AND
                        orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
                    AND 
                        " . $orderStateCondition. "
                        ".$excludeFreeOrder."
                    AND
                        (order_detail.purchase_supplier_price OR order_detail.original_wholesale_price) != 0
                    ".$sqlFilters."
                    GROUP BY 
                        orders.id_order
                ) as t";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'float2');
}