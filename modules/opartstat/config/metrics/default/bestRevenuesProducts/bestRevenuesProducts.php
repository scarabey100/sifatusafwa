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

function getbestRevenuesProducts($vars)
{
    return OpartStatTools::getBestMetricResult('bestRevenuesProducts',$vars);
}

function getbestRevenuesProductsValues($dateFrom, $dateTo, $filtersArray, $start, $limit, $vars) {
    $result['conf']['total'] = 'price';
    $result['value'] = [];
    $result['conf']['allDataLoaded'] = true;

    if ($dateFrom == 0 || $dateTo == 0) 
        return $result;

    $shopConstraints = OpartStatTools::getShopConstraints();
    $orderStateCondition = OpartStatTools::createOrderStateCondition('OPARTSTAT_STATUS_VALID_ORDER');
    $excludeFreeOrder = (Configuration::get('OPARTSTAT_EXCLUDE_FREE_ORDER') == 0)?"":"AND orders.total_paid_tax_excl > 0";
    $idLang = Context::getContext()->language->id;
    $dateColumn = (Configuration::get('OPARTSTAT_USE_ORDER_CREATED_DATE') == 0)?'invoice_date':'date_add';
    $fields = OpartStatTools::getRevenueFieldsForOrderDetailLine();

    $sqlFilters = OpartStatTools::getFilters($filtersArray);
    $sqlJoins = OpartStatTools::getJoins($filtersArray,['product','order_detail','product_lang']);
    $useCache = true;
    if(isset($vars['otherVars']['useCache']))
        $useCache = filter_var($vars['otherVars']['useCache'], FILTER_VALIDATE_BOOLEAN);

    $sql = "SELECT SQL_NO_CACHE 
                order_detail.`product_id`, 
                IFNULL(product_lang.`name`,order_detail.`product_name`) AS product_name,
                order_detail.total_price_tax_excl,
                orders.total_discounts_tax_excl,
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
            LEFT JOIN 
                " . _DB_PREFIX_ . "product product
            ON
                order_detail.product_id = product.id_product
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
            AND
                orders.`".bqSQL($dateColumn)."` >= '" . pSQL($dateFrom) . "'
            AND 
                orders.`".bqSQL($dateColumn)."` <= '" . pSQL($dateTo) . "'   
            AND
                ".$shopConstraints."
            ".$sqlFilters."            
            ORDER BY
                orders.`".bqSQL($dateColumn)."` ASC
            LIMIT 
                " . (int)$start . ", " . (int)$limit;

    $productList = OpartStatTools::getValueFromCacheIfExists($sql,$dateTo,$useCache);
    //echo $sql."<br />";
    if (count($productList) == 0)
        return $result;    
    
    $token = Tools::getAdminTokenLite('AdminProducts');

    $mergedProductList = [];
    foreach ($productList as $p) {
        $total = ($p['total'] == null)?0:$p['total'];
        if (isset($mergedProductList[$p['product_id']])) {
            $mergedProductList[$p['product_id']]['total'] += $total;
        } else {
            $productName = ($p['product_name'] == null)?'Unknow':$p['product_name'];
            $productLink = 'index.php?controller=AdminProducts&id_product='.(int)$p['product_id'].'&updateproduct&token='.$token;
            $mergedProductList[$p['product_id']] = [
                'id' => $p['product_id'],
                'name' => '('.$p['product_id'].') '.$productName,   
                'link' => $productLink,
                'total' => $total      
            ];
        }
    }
    
    $result['value'] = $mergedProductList;    
    $result['conf']['allDataLoaded'] = false;
    $result['sql'] = $sql;

    return $result;
}
