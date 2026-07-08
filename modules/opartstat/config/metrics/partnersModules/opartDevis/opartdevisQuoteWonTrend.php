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

function getOpartdevisQuoteWonTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('OpartdevisQuoteWonTrend',$vars);
}

function getOpartdevisQuoteWonTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints('opartdevis');
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 

    $sql =  "SELECT 
                COUNT(opartdevis.id_opartdevis) AS total, 
                orders.`".bqSQL($dateColumn)."` as date_add
            FROM 
                `" . _DB_PREFIX_ . "opartdevis` opartdevis   
            INNER JOIN 
                `" . _DB_PREFIX_ . "orders` orders     
            ON
                orders.id_order = opartdevis.id_order     
            WHERE 
                ".$shopConstraints."    
            AND 
                " . $orderStateCondition . "
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'
            GROUP BY 
                DATE_FORMAT(orders.`".bqSQL($dateColumn)."`,'%Y-%m-%d')
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC";
    
    $quotes = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArray($dateFrom,$dateTo,$quotes,'date_add','total');

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => ''
    ];
    return $result;
}