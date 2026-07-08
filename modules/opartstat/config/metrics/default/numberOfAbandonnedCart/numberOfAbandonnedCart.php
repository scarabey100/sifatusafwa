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

function getnumberOfAbandonnedCart($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('numberOfAbandonnedCart',$vars);
}

function getnumberOfAbandonnedCartValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints('cart');
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);   
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
                COUNT(DISTINCT cart.id_cart)
            FROM 
                `" . _DB_PREFIX_ . "cart` cart           
            WHERE 
            NOT EXISTS (SELECT 1 FROM " . _DB_PREFIX_ . "orders orders WHERE orders.`id_cart` = cart.`id_cart`)
             AND   ".$shopConstraints."
            AND
                cart.`date_add` >= '" . pSQL($dateFrom) . "'
            AND 
                cart.`date_add` <= '" . pSQL($dateTo) . "'";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache);
}