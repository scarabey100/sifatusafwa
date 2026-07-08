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

function getCustomerLifetime($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('CustomerLifetime',$vars);
}

function getCustomerLifetimeValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $interval = Configuration::get('OPARTSTAT_INACTIV_CUSTOMER_DAYS');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $today = date("Y-m-d H:i:s");
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['customer']);

    $sql =  "SELECT
                AVG(t.lifetime)
            FROM
                (
                    SELECT
                        DATEDIFF(
                            MAX(orders.date_add),
                            MIN(orders.date_add)
                        ) AS lifetime
                    FROM
                        "._DB_PREFIX_."customer customer
                    JOIN 
                        "._DB_PREFIX_."orders orders 
                    ON
                        customer.id_customer = orders.id_customer
                    AND
                        customer.id_shop = orders.id_shop
                    ".$sqlJoins." 
                    WHERE
                        ". $orderStateCondition ."
                        ".pSQL($excludeFreeOrder). "
                        " . $sqlFilters . "
                    AND
                        orders.`".bqSQL($dateColumn)."` < DATE_SUB('".pSQL($today)."', INTERVAL ".(int)$interval." DAY)                    
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
                    )
                    GROUP BY
                        customer.id_customer
                ) t";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,'float0');
}