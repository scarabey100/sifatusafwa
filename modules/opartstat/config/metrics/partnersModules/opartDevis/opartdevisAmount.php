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

function getOpartdevisAmount($vars)
{
    return OpartStatTools::getMetricResult('OpartdevisAmount',$vars);
}

function getOpartdevisAmountValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 
    $result['conf']['total'] = '';
    $fields = opartStatTools::getTotalRevenueFields();
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT 
                SUM(orderRevenu) 'totalCA'
            FROM 
                (
                    SELECT 
                        orders.id_order as commande, 
                        (".$fields.") as 'orderRevenu'
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
                    INNER JOIN
                        `" . _DB_PREFIX_ . "opartdevis` opartdevis   
                    ON
                        orders.id_order = opartdevis.id_order        
                    WHERE 
                        ".$shopConstraints." 
                    AND
                        orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                    AND 
                        " . $orderStateCondition. "
                    GROUP BY 
                        orders.id_order
                ) as t";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'price');
}
