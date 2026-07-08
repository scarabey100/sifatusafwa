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

function getBestNumberOrderCompanies($vars)
{
    return OpartStatTools::getBestMetricResult('bestNumberOrderCompanies', $vars);
}

function getBestNumberOrderCompaniesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['address']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                COUNT(DISTINCT orders.id_order) AS total,                 
                address.company,
                id_address,
                orders.id_order
            FROM  
                `" . _DB_PREFIX_ . "orders` orders                    
            LEFT JOIN 
            `" . _DB_PREFIX_ . "address` address 
            ON 
                orders.id_address_invoice = address.id_address
            ".$sqlJoins." 
            WHERE 
                ".$shopConstraints."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            AND 
                " . $orderStateCondition . "
            ".$excludeFreeOrder."
            " .$sqlFilters."   
            GROUP BY
                address.company
            ORDER BY 
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    //echo $sql."<br />";
    if (count($orderList) == 0)
        return $result;    

    $mergedList = [];
    foreach ($orderList as $o) {
        $companyName = ($o['id_address'] == null) ? "Unknow" : $o['company'];
        $companyName = ($companyName == '') ? 'Unknow' : $companyName;
        $total = ($o['total'] == null) ? 0 : $o['total'];
        if (isset($mergedList[$companyName])) {
            $mergedList[$companyName]['total'] += $total;
        } else {
            $mergedList[$companyName] = [
                'total' => $total,
                'name' => $companyName,
                'id' => $companyName
            ];
        }
    }

    $result['value'] = $mergedList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
    /*  */
}
