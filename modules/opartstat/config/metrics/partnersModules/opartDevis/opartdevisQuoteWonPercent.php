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

function getOpartdevisQuoteWonPercent($vars)
{
    return OpartStatTools::getMetricResult('OpartdevisQuoteWonPercent',$vars);
}

function getOpartdevisQuoteWonPercentValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints('opartdevis');
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $result['conf']['total'] = '';
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT 
                (SUM(CASE WHEN " . $orderStateCondition . " THEN 1 ELSE 0 END) / COUNT(id_opartdevis)) * 100 as total
            FROM 
                `" . _DB_PREFIX_ . "opartdevis` opartdevis
            LEFT JOIN 
                `" . _DB_PREFIX_ . "orders` orders     
            ON
                orders.id_order = opartdevis.id_order
            WHERE
                ".$shopConstraints."
            AND
                opartdevis.date_add >= '" . pSQL($dateFrom) . "'
            AND 
                opartdevis.date_add <= '" . pSQL($dateTo) . "'
            ";

    return OpartStatTools::getSingleNumberJsonResult($sql,$dateTo,$useCache,'percent');
}
