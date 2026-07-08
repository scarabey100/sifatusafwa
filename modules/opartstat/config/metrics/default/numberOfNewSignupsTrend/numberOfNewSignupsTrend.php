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

function getNumberOfNewSignupsTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('NumberOfNewSignupsTrend',$vars);
}

function getNumberOfNewSignupsTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints('customer');
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);
    $sql = "
        SELECT SQL_NO_CACHE 
            COUNT(customer.id_customer) AS total,customer.date_add
        FROM 
            `"._DB_PREFIX_."customer` customer
        WHERE 
           customer.date_add BETWEEN '".pSQL($dateFrom)."' AND '".pSQL($dateTo)."'
        AND
            ".$shopConstraints."
        GROUP BY 
            DATE_FORMAT(customer.date_add,'%Y-%m-%d')
    ";

    $signups = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArray($dateFrom,$dateTo,$signups,'date_add','total');

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