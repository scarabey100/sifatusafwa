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

require_once(dirname(__FILE__) . '/../../../../classes/opartStatTools.php');

function getGoogleAdsOrder($vars,$humanResult=true)
{   
    return OpartStatTools::getBestMetricResult('googleAdsOrder', $vars);
}

function getGoogleAdsOrderValues($dateFrom,$dateTo,$filtersArray,$start,$limit,$vars) {

      $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;


    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0) ? "" : "AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0) ? 'invoice_date' : 'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray, ['order_detail']);
    $fields = opartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if (isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql =  "SELECT SQL_NO_CACHE 
                            orders.id_cart, 
                            orders.reference, 
                            (" . $fields . ") as 'total',
                            orders.`" . bqSQL($dateColumn) . "`as 'orderDate',
                            orders.id_order as idOrder,
                            opartstat_sessions.gclid
                        FROM 
                            " . _DB_PREFIX_ . "orders orders                     
                        INNER JOIN 
                            " . _DB_PREFIX_ . "order_detail order_detail 
                        ON 
                            orders.id_order = order_detail.id_order                     
                        LEFT JOIN 
                            " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                        ON
                            orders.id_order = order_cart_rule.id_order
                        AND
                            order_cart_rule.free_shipping = 1
                        " . $sqlJoins . "
                        INNER JOIN " . _DB_PREFIX_ . "opartstat_sessions opartstat_sessions
                        ON orders.id_cart = opartstat_sessions.idCart AND opartstat_sessions.gclid != ''
                        WHERE 
                            " . $shopConstraints . "
                        AND 
                            " . $orderStateCondition . "
                            " . $excludeFreeOrder . "
                        AND
                            orders.`" . bqSQL($dateColumn) . "` >= '" . pSQL($dateFrom) . "'
                        AND 
                            orders.`" . bqSQL($dateColumn) . "` <= '" . pSQL($dateTo) . "'
                        " . $sqlFilters . "
                        " . $groupBy . "
                         LIMIT 
            ".(int)$start.",".(int)$limit."
                    ";

    $results = OpartStatTools::getValueFromCacheIfExists($sql, $dateTo, $useCache);


    if (!is_array($results) || count($results) == 0)
        return $result;



    $mergedCampaigns = [];
    
    foreach ($results as $p) {
                $mergedCampaigns[$p['idOrder']] = [
                    'idOrder' => $p['idOrder'],
                    'reference' => $p['reference'],
                    'gclid' => $p['gclid'],
                ];
    }



    $result['value'] = $mergedCampaigns;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}