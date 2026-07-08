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

function getBestNumberOrderCountries($vars)
{
    return OpartStatTools::getBestMetricResult('bestNumberOrderCountries',$vars);
}

function getBestNumberOrderCountriesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = '';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    
    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql ="SELECT SQL_NO_CACHE  
                sub1.id_order,                 
                sub1.id_address_invoice,
                address.id_country,
                country_lang.`name` as country_name
            FROM 
                (
                    SELECT                         
                        id_address_invoice,
                        orders.id_order                 
                    FROM  
                        `" . _DB_PREFIX_ . "orders` orders                    
                    LEFT JOIN 
                        " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
                    ON
                        orders.id_order = order_cart_rule.id_order
                    AND
                        order_cart_rule.free_shipping = 1
                    LEFT JOIN 
                        " . _DB_PREFIX_ . "order_detail order_detail 
                    ON
                        orders.id_order = order_detail.id_order
                    ".$sqlJoins." 
                    WHERE 
                        ".$shopConstraints."
                    AND 
                        " . $orderStateCondition . "
                    ".$excludeFreeOrder."
                    " .$sqlFilters."   
                    AND
                        orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
                    GROUP BY
                        orders.id_order
                    ORDER BY 
                        orders.`".bqSQL($dateColumn)."` ASC
                    LIMIT 
                        " . (int)$start . ", " . (int)$limit . "
                ) sub1
            LEFT JOIN 
                `" . _DB_PREFIX_ . "address` address 
            ON 
                sub1.id_address_invoice = address.id_address
            LEFT JOIN 
                `" . _DB_PREFIX_ . "country_lang` country_lang 
            ON 
                address.id_country = country_lang.id_country 
            AND 
                country_lang.id_lang=".(int)$idLang;

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($orderList) == 0)
        return $result;    

    $mergedCountryList = [];
    foreach ($orderList as $o) {
        if (isset($mergedCountryList[$o['id_country']]) && $mergedCountryList[$o['id_country']]['total'] > 0) {
            $mergedCountryList[$o['id_country']]['total'] = $mergedCountryList[$o['id_country']]['total']+1;
        }
        else {
            $mergedCountryList[$o['id_country']] = [
                'id' => $o['id_country'],
                'name' => $o['country_name'],
                'total' => 1
            ];
        }
    }

    $result['value'] = $mergedCountryList;
    $result['conf']['allDataLoaded'] = false;
    return $result;
}
