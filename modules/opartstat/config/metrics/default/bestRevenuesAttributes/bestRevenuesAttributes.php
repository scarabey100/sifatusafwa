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

function getbestRevenuesAttributes($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesAttributes',$vars);
}

function getbestRevenuesAttributesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $field = (Configuration::get('OPARTSTAT_EXCLUDE_SHIPPING') == 0) ? 'orders.total_paid_tax_excl' : '(orders.total_paid_tax_excl - orders.total_shipping_tax_excl)';
    $idLang = Context::getContext()->language->id;
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail']);
    $fields = OpartStatTools::getRevenueFieldsForOrderDetailLine();
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                product_attribute_combination.`id_attribute`, 
                attribute_lang.`name` AS attribute_name, 
                attribute_group_lang.`name` AS group_name, 
                " . bqSQL($field) . " as field,
                (".$fields.") as total
            FROM
                " . _DB_PREFIX_ . "orders orders            
            LEFT JOIN 
                " . _DB_PREFIX_ . "order_cart_rule order_cart_rule 
            ON
                orders.id_order = order_cart_rule.id_order
            AND
                order_cart_rule.free_shipping = 1
            INNER JOIN 
                " . _DB_PREFIX_ . "order_detail order_detail 
            ON
                orders.id_order = order_detail.id_order
            INNER JOIN 
                " . _DB_PREFIX_ . "product_attribute_combination product_attribute_combination
            ON
                order_detail.product_attribute_id = product_attribute_combination.id_product_attribute
            LEFT JOIN 
                `" . _DB_PREFIX_ . "attribute_lang` attribute_lang 
            ON
                product_attribute_combination.`id_attribute` = attribute_lang.`id_attribute`
            AND
                attribute_lang.id_lang = ".(int)$idLang."
            ".$sqlJoins."
            LEFT JOIN 
                `" . _DB_PREFIX_ . "attribute` attribute
            ON
                product_attribute_combination.`id_attribute` = attribute.`id_attribute`
            LEFT JOIN 
                `" . _DB_PREFIX_ . "attribute_group_lang` attribute_group_lang
            ON
                attribute.`id_attribute_group` = attribute_group_lang.`id_attribute_group`
            AND
                attribute_group_lang.id_lang = ".(int)$idLang."
            WHERE
                " . $orderStateCondition . "
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            AND
                ".$shopConstraints."
            AND
                order_detail.product_attribute_id != 0
            ".$excludeFreeOrder."
            ".$sqlFilters." 
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($productList) == 0)
        return $result;    

    $mergedProductList = [];
    foreach ($productList as $p) {
        $total = ($p['total'] == null)?0:$p['total'];
        if (isset($mergedProductList[$p['id_attribute']])) {
            $mergedProductList[$p['id_attribute']]['total'] += $total;
        } else {
            $productName = ($p['attribute_name'] == null)?'Unknow':$p['attribute_name'];
            $mergedProductList[$p['id_attribute']] = [
                'id' => $p['id_attribute'],
                'name' => '('.$p['group_name'].') '.$productName,
                'total' => $total
            ];
        }
    }

    $result['value'] = $mergedProductList;    
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
