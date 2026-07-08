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

function getOpartdevisQuoteWonPercentTrend($vars)
{
    return OpartStatTools::getMetricResult('OpartdevisQuoteWonPercentTrend', $vars);
}

function getOpartdevisQuoteWonPercentTrendValues($dateFrom, $dateTo, $filtersArray, $vars)
{
    $shopConstraints = OpartStatTools::getShopConstraints('opartdevis');
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $result['conf']['total'] = '';
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT 
                (SUM(CASE WHEN " . $orderStateCondition . " THEN 1 ELSE 0 END) / COUNT(id_opartdevis)) * 100 as total,
                opartdevis.date_add as date_add,
                COUNT(id_opartdevis) as nbQuote
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
            GROUP BY 
                DATE_FORMAT(opartdevis.date_add,'%Y-%m-%d')
            ORDER BY 
                opartdevis.date_add ASC";
    $quotes = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArrayUsingAverage($dateFrom, $dateTo, $quotes, 'date_add', 'total');

    $totalWon = 0;
    $nbQuote = 0;
    foreach($quotes as $quote) {
        $totalWon = $totalWon+$quote['total'];
        $nbQuote =  $nbQuote+$quote['nbQuote'];
    }
    if($nbQuote != 0)
        $r['totalGlobal'] = $totalWon / $nbQuote;
    else
        $r['totalGlobal'] = 0;

    $result['value'] = [
        'globalValue' => $r['totalGlobal'],
        'perYear' => $r['totalPerYear'],
        'perMonth' => $r['totalPerMonth'],
        'perWeek' => $r['totalPerWeek'],
        'perDay' => $r['totalPerDay']
    ];

    $result['conf'] = [
        'globalValueFormat' => 'percent'
    ];
    return $result;
}
