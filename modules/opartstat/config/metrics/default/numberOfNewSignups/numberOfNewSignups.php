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

function getNumberOfNewSignups($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('NumberOfNewSignups',$vars);
}

function getNumberOfNewSignupsValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints('customer');
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);
    $sql = "
        SELECT SQL_NO_CACHE 
            COUNT(customer.id_customer)
        FROM 
            `"._DB_PREFIX_."customer` customer
        WHERE 
            customer.date_add BETWEEN '".pSQL($dateFrom)."' AND '".pSQL($dateTo)."'
        AND
            ".$shopConstraints."
    ";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache);
}