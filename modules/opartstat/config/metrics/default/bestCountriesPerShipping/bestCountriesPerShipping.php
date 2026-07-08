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

function getbestCountriesPerShipping($vars)
{
    return OpartStatTools::getBestMetricResult('bestCountriesPerShipping',$vars);
}

function getbestCountriesPerShippingValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars) {
    $result['conf']['total'] = 'price';
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
            orders.total_shipping_tax_excl as totalShipping,
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
        LIMIT 
            " . (int)$start . ", " . (int)$limit;




    $CountryList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);



    
    if (count($CountryList) == 0)
        return $result;    
    
    $mergedCountryList = [];

    foreach($CountryList as $p) {
        $name = ($p['name'] == null)?'Unknow':$p['name'];
        if (isset($mergedCountryList[$p['id_country']])) {
            $mergedCountryList[$p['id_country']]['total'] += $p['totalShipping'];
        }
        else {            
            $mergedCountryList[$p['id_country']] = [
                'total' => $p['totalShipping'],
                'name' => '('.$p['id_country'].') '.$name,   
                'id' => $p['id_country'],
            ];
        }
    }






    $result['value'] = $mergedCountryList;    
    $result['conf']['allDataLoaded'] = false;
    $result['sql'] = $sql;

    return $result;
}
