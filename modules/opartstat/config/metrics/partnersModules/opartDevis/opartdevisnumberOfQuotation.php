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

function getopartdevisnumberOfQuotation($vars,$humanResult=true)
{   
    return OpartStatTools::getMetricResult('opartdevisnumberOfQuotation',$vars);
}

function getopartdevisnumberOfQuotationValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints('opartdevis');


    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);   
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
                COUNT(DISTINCT opartdevis.id_opartdevis)
            FROM 
                `" . _DB_PREFIX_ . "opartdevis` opartdevis  
            ".$sqlJoins."          
            WHERE 
                ".$shopConstraints."
            AND
                opartdevis.`date_add` >= '" . pSQL($dateFrom) . "'
            AND 
                opartdevis.`date_add` <= '" . pSQL($dateTo) . "'";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache);
}