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

function getbestSellsVariationsAndFeatures($vars)
{
    return OpartStatTools::getBestMetricResult('bestSellsVariationsAndFeatures',$vars);
}

function getbestSellsVariationsAndFeaturesValues($dateFrom, $dateTo, $filtersArray, $start, $limit,$vars) {
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
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['order_detail','product_lang']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                order_detail.product_id,  
                order_detail.product_attribute_id,
                CONCAT(order_detail.`product_name`, ' - ', GROUP_CONCAT(CONCAT(feature_lang.name, ': ', feature_value_lang.value) SEPARATOR ', ')) AS variation_name,
                order_detail.`product_quantity` - order_detail.`product_quantity_refunded` as qty,
                orders.id_order
            FROM 
                `" . _DB_PREFIX_ . "order_detail` order_detail         
            INNER JOIN 
                `" . _DB_PREFIX_ . "orders` orders 
            ON
                orders.id_order = order_detail.id_order
            INNER JOIN 
                " . _DB_PREFIX_ . "feature_product feature_product
            ON
                order_detail.product_id = feature_product.id_product
            INNER JOIN 
                " . _DB_PREFIX_ . "feature_lang feature_lang
            ON
                feature_product.id_feature = feature_lang.id_feature
            AND
                feature_lang.id_lang = ".(int)$idLang."
            INNER JOIN 
                " . _DB_PREFIX_ . "feature_value_lang feature_value_lang
            ON
                feature_product.id_feature_value = feature_value_lang.id_feature_value
            AND
                feature_value_lang.id_lang = ".(int)$idLang."
            AND
                ".$shopConstraints."
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'               
            LEFT JOIN 
                `" . _DB_PREFIX_ . "product_lang` product_lang 
            ON
                order_detail.`product_id` = product_lang.`id_product`
            AND 
                product_lang.id_shop = orders.id_shop
            AND
                product_lang.id_lang = ".(int)$idLang." 
            ".$sqlJoins."
            WHERE
                " . $orderStateCondition . "
            ".$excludeFreeOrder." 
            ".$sqlFilters." 
            AND
                order_detail.product_attribute_id != 0
            GROUP BY
                order_detail.id_order_detail
            LIMIT 
                " . (int)$start . ", " . (int)$limit . "
            ";

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);

    if (count($productList) == 0) 
        return $result;
    
    $token = Tools::getAdminTokenLite('AdminProducts');

    $mergedProductList = [];
    foreach ($productList as $p) {
        if (isset($mergedProductList[$p['product_attribute_id']])) {
            (int)$mergedProductList[$p['product_attribute_id']]['total'] += (int)$p['qty'];
        } else {
            $productName = ($p['variation_name'] == null)?'Unknow':$p['variation_name'];
            $productLink = 'index.php?controller=AdminProducts&id_product='.(int)$p['product_id'].'&updateproduct&token='.$token;
            $mergedProductList[$p['product_attribute_id']] = [
                'id' => $p['product_attribute_id'],
                'name' => '('.$p['product_attribute_id'].') '.$productName,
                'link' => $productLink,
                'total' => (int)$p['qty'],
            ];
        }
    }

    $result['value'] = $mergedProductList;
    $result['conf']['allDataLoaded'] = false;

    return $result;
}
