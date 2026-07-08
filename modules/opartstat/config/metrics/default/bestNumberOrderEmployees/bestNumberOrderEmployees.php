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

function getBestNumberOrderEmployees($vars)
{
    return OpartStatTools::getBestMetricResult('bestNumberOrderEmployees',$vars);
}

function getbestNumberOrderEmployeesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    //$shopConstraints = OpartStatTools::getShopConstraints();
    
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,[]);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    //we don't use statu for this metric so we do not need the order_history date add, we just need the order date_add
        $sql = "SELECT SQL_NO_CACHE  
                1 as total,
                orders.id_order,
                oh.`id_employee`,
                orders.id_order,
                e.`lastname`,
                e.`firstname`,
                orders.current_state
            FROM 
                " . _DB_PREFIX_ . "orders orders
            LEFT JOIN
                " . _DB_PREFIX_ . "order_history oh
            ON
                orders.id_order = oh.id_order
            AND 
                oh.id_order_history = (
                    SELECT   
                        min(oh.id_order_history)
                    FROM 
                        " . _DB_PREFIX_ . "order_history oh
                    WHERE 
                        orders.id_order = oh.id_order
                )
            LEFT JOIN                      
                `" . _DB_PREFIX_ . "employee` e 
            ON
                e.id_employee = oh.id_employee            
            ".$sqlJoins."       
            WHERE
                orders.date_add >= '" . pSQL($dateFrom) . "'
            AND 
                orders.date_add <= '" . pSQL($dateTo) . "' 
            ".$excludeFreeOrder."
            " .$sqlFilters."
            GROUP BY 
                orders.id_order
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    //echo $sql."<br />";
    if (count($orderList) == 0)
        return $result;    

    $mergedList = [];

    foreach($orderList as $o) {
        $employeeName = ($o['id_employee']==0)?"Alone":$o['firstname'].' '.$o['lastname'];;

        if (isset($mergedList[$o['id_employee']])) {
            $mergedList[$o['id_employee']]['total'] += $o['total'];
        }
        else {
            $mergedList[$o['id_employee']] = [
                'id' => $o['id_employee'],
                'name' => '('.$o['id_employee'].') '.$employeeName,
                'total' => $o['total'],
            ];
        }
    }

    $result['value'] = $mergedList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
