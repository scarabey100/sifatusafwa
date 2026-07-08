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

function getNumberOfNewCustomersTrend($vars, $humanResult = true)
{
    return OpartStatTools::getMetricResult('NumberOfNewCustomersTrend',$vars);
}

function getNumberOfNewCustomersTrendValues($dateFrom,$dateTo,$filtersArray,$vars) {
    $shopConstraints = OpartStatTools::getShopConstraints();
    $shopConstraints2 = OpartStatTools::getShopConstraints('orders1');
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add'; 
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['customer']);

    $sql = "
        SELECT SQL_NO_CACHE 
            COUNT(DISTINCT(customer1.id_customer)) as total, orders1.`".bqSQL($dateColumn)."` as date_add
        FROM 
            `"._DB_PREFIX_."orders` orders1
        JOIN 
            `"._DB_PREFIX_."customer` customer1 
        ON 
            customer1.id_customer = orders1.id_customer
        WHERE 
            ".pSQL($shopConstraints2)."
        AND
            orders1.`".bqSQL($dateColumn)."` BETWEEN '".pSQL($dateFrom)."' AND '".pSQL($dateTo)."'
        AND 
            orders1.id_order IN (
                SELECT  
                    MIN(orders.id_order)
                FROM 
                   `"._DB_PREFIX_."orders` orders
                JOIN 
                    `"._DB_PREFIX_."customer` customer 
                ON 
                    orders.id_customer = customer.id_customer                
                 ".$sqlJoins."  
                WHERE
                    ".$shopConstraints."
                AND 
                    ".$orderStateCondition."
                    ".$excludeFreeOrder."
                    " . $sqlFilters . "                     
                GROUP BY 
                    orders.id_customer
            )
        GROUP BY DATE_FORMAT(orders1.`".bqSQL($dateColumn)."`,'%Y-%m-%d')
    ";

    $customers = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    $r = OpartStatTools::populatePeriodArray($dateFrom,$dateTo,$customers,'date_add','total');

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