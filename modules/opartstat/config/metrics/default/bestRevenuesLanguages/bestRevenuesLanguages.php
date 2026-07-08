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

function getBestRevenuesLanguages($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesLanguages',$vars);
}

function getBestRevenuesLanguagesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $result['conf']['thLabels'] = [
        'thLabel1' => 'languages', 
        'thLabel2' => 'revenues' 
    ];

    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $fields = OpartStatTools::getFields($filtersArray);
    $groupBy = opartStatTools::getGroupBy($filtersArray);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE  
                sub1.id_order, 
                sub1.total as total,
                sub1.id_lang,
                l.`name` as language_name
            FROM 
                (
                    SELECT  
                        orders.id_order,
                        orders.id_lang,
                        ".$fields." as total
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
                    AND
                        orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
                    AND 
                        orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'                     
                    ".$excludeFreeOrder."
                    ".$sqlFilters."     
                    ".$groupBy."  
                    ORDER BY 
                        orders.`".bqSQL($dateColumn)."` ASC
                    LIMIT 
                        " . (int)$start . ", " . (int)$limit . "
                ) sub1
            LEFT JOIN 
                `" . _DB_PREFIX_ . "lang` l 
                ON 
                    sub1.id_lang = l.id_lang";

    $orderList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($orderList) == 0)
        return $result;    

    $mergedLanguageList = [];
    foreach ($orderList as $o) {
        $total = ($o['total'] == null)?0:$o['total'];
        if (isset($mergedLanguageList[$o['id_lang']]) && $mergedLanguageList[$o['id_lang']]['total'] > 0) {
            $mergedLanguageList[$o['id_lang']]['total'] = $total+$mergedLanguageList[$o['id_lang']]['total'];
        }
        else {
            $mergedLanguageList[$o['id_lang']] = [
                'total' => $total,
                'name' => $o['language_name'],
                'id' => $o['id_lang'],
            ];
        }
    }

    $result['value'] = $mergedLanguageList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
