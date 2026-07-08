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

function getbestCountriesPerTaxesRevenues($vars)
{
    return OpartStatTools::getBestMetricResult('bestCountriesPerTaxesRevenues',$vars);
}

function getbestCountriesPerTaxesRevenuesValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars) {
    $result['conf']['total'] = 'price';
    $result['conf']['totalShippingTaxe'] = 'price';
    $result['conf']['totalProductTaxe'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";    
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $idLang = Context::getContext()->language->id;

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','order_detail','product_lang']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

        $sql = "SELECT SQL_NO_CACHE  
            country_lang.name as name,
            address.id_country as id_country,                
            SUM(order_detai_tax.total_amount) as totalProductTaxe,
            orders.id_order
        FROM
            " . _DB_PREFIX_ . "orders orders            
        INNER JOIN 
            " . _DB_PREFIX_ . "address address 
        ON
            orders.id_address_invoice = address.id_address
        INNER JOIN 
            " . _DB_PREFIX_ . "country_lang country_lang 
        ON
            address.id_country = country_lang.id_country
        AND
            country_lang.id_lang = " . (int)$idLang . "            
        INNER JOIN 
            " . _DB_PREFIX_ . "order_detail order_detail 
        ON
            orders.id_order = order_detail.id_order
         INNER JOIN  " . _DB_PREFIX_ . "order_detail_tax order_detai_tax 
        ON
            order_detail.id_order_detail = order_detai_tax.id_order_detail
            ".$sqlJoins."            
        WHERE
            " . $orderStateCondition . "
            ".$excludeFreeOrder."
        AND
            orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
        AND 
            orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
        AND
            ".$shopConstraints."
        ".$sqlFilters."
        GROUP BY
            address.id_country            
        LIMIT 
            " . (int)$start . ", " . (int)$limit;


    $taxesList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    
    if (count($taxesList) == 0)
        return $result;    
    
    $mergedTaxesList = [];

    $sqlIn = "";
    foreach ($taxesList as $p) {
        $totalProductTaxe = ($p['totalProductTaxe'] == null)?0:$p['totalProductTaxe'];
            $name = ($p['name'] == null)?'Unknow':$p['name'];
            $mergedTaxesList[$p['id_country']] = [
                'id' => $p['id_country'],
                'name' => '('.$p['id_country'].') '.$name,   
                'totalProductTaxe' => $totalProductTaxe  
            ];

        $sqlIn .= ($sqlIn == "")?(int)$p['id_order']:",".(int)$p['id_order'];
    }

    $sqlIn = "(".$sqlIn.")";

    $sql = "SELECT SQL_NO_CACHE  
            address.id_country as id_country,                
            SUM(orders.total_shipping_tax_incl - orders.total_shipping_tax_excl) as totalShippingTaxe,
            orders.id_order
        FROM
            " . _DB_PREFIX_ . "orders orders            
        INNER JOIN 
            " . _DB_PREFIX_ . "address address 
        ON
            orders.id_address_invoice = address.id_address                 
        WHERE 
            ".$shopConstraints."
        AND
            orders.id_order IN ".$sqlIn."
        GROUP BY
            address.id_country            
        LIMIT 
            " . (int)$start . ", " . (int)$limit;
    
    $shippingTaxesList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    foreach ($shippingTaxesList as $s) {
        $mergedTaxesList[$s['id_country']]['totalShippingTaxe'] = $s['totalShippingTaxe'];   
        $mergedTaxesList[$s['id_country']]['total'] = $mergedTaxesList[$s['id_country']]['totalProductTaxe']+$s['totalShippingTaxe'];   
    }

    $result['value'] = $mergedTaxesList;    
    $result['conf']['allDataLoaded'] = false;
    $result['sql'] = $sql;

    return $result;
}
